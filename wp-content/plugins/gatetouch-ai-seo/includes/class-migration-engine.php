<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- migration writes bounded batches to custom tables.

/**
 * Migration orchestrator.
 *
 * Reads SEO data from another plugin through a GateTouch_Migration_Source adapter
 * and writes it into GateTouch's own storage.
 *
 * Three guarantees the previous implementation did not make:
 *
 *  1. Non-destructive. By default a field is only written when the GateTouch
 *     side is empty, so re-running a migration — or migrating from two plugins
 *     in sequence — never silently destroys work.
 *  2. Bounded. Everything runs in batches over paginated queries, so a site with
 *     200,000 posts does not exhaust memory or hit the PHP time limit.
 *  3. Verifiable. analyze() reports what *would* happen before anything is
 *     written, and verify() re-reads both sides afterwards and reports mismatches.
 */
class GateTouch_Migration_Engine {

    const STATE_OPTION    = 'gatetouch_migration_state';
    const REPORT_OPTION   = 'gatetouch_migration_report';
    const IMPORTED_OPTION = 'gatetouch_migration_imported';
    const BACKUP_OPTION = 'gatetouch_migration_backup';

    /** Records processed per batch. */
    const BATCH_SIZE = 50;

    /**
     * Every available adapter, keyed by slug.
     *
     * @return array<string, GateTouch_Migration_Source>
     */
    public static function sources() {
        static $sources = null;
        if ( null !== $sources ) {
            return $sources;
        }

        $classes = [
            'GateTouch_Migration_Yoast',
            'GateTouch_Migration_Rankmath',
            'GateTouch_Migration_Aioseo',
            'GateTouch_Migration_Seopress',
            'GateTouch_Migration_Tsf',
            'GateTouch_Migration_Slimseo',
        ];

        $sources = [];
        foreach ( $classes as $class ) {
            if ( ! class_exists( $class ) ) {
                continue;
            }
            $instance                      = new $class();
            $sources[ $instance->slug() ] = $instance;
        }

        /**
         * Filter the registered migration adapters.
         *
         * @param array<string, GateTouch_Migration_Source> $sources
         */
        $sources = apply_filters( 'gatetouch_migration_sources', $sources );

        return $sources;
    }

    public static function get_source( $slug ) {
        $sources = self::sources();
        return $sources[ sanitize_key( $slug ) ] ?? null;
    }

    /**
     * Slugs of every source that has data on this site.
     *
     * Kept returning a plain list for backwards compatibility with the existing
     * Tools screen, which iterates it directly.
     *
     * @return string[]
     */
    public static function detect_sources() {
        $found = [];

        foreach ( self::sources() as $slug => $source ) {
            if ( $source->is_detected() ) {
                $found[] = $slug;
            }
        }

        return $found;
    }

    /**
     * Detected sources with their record counts and labels, for the UI.
     */
    public static function detect_detailed() {
        $out = [];

        foreach ( self::sources() as $slug => $source ) {
            if ( ! $source->is_detected() ) {
                continue;
            }

            $counts = $source->counts();

            $done = self::imported_sources();

            $out[ $slug ] = [
                'slug'     => $slug,
                'label'    => $source->label(),
                'counts'   => $counts,
                'total'    => array_sum( $counts ),
                'settings' => ! empty( $source->get_settings() ),
                'active'   => self::is_plugin_active_for( $slug ),
                'imported' => ! empty( $done[ $slug ]['finished'] ),
                'imported_at' => (int) ( $done[ $slug ]['finished'] ?? 0 ),
            ];
        }

        return $out;
    }

    private static function is_plugin_active_for( $slug ) {
        $constants = [
            'yoast'    => 'WPSEO_VERSION',
            'rankmath' => 'RANK_MATH_VERSION',
            'aioseo'   => 'AIOSEO_VERSION',
            'seopress' => 'SEOPRESS_VERSION',
            'tsf'      => 'THE_SEO_FRAMEWORK_VERSION',
            'slimseo'  => 'SLIM_SEO_VER',
        ];

        return isset( $constants[ $slug ] ) && defined( $constants[ $slug ] );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Dry run
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Report what a migration would do, without writing anything.
     *
     * @param string $slug   Source slug.
     * @param int    $sample How many records to inspect for the conflict estimate.
     */
    public static function analyze( $slug, $sample = 200 ) {
        $source = self::get_source( $slug );
        if ( ! $source ) {
            return [ 'success' => false, 'error' => __( 'Unknown migration source.', 'gatetouch-ai-seo' ) ];
        }

        $counts   = $source->counts();
        $settings = $source->get_settings();

        $conflicts = [ 'posts' => 0, 'terms' => 0, 'users' => 0 ];
        $examples  = [];

        // Inspect a bounded sample to estimate how much would be overwritten.
        foreach ( [ 'posts', 'terms', 'users' ] as $type ) {
            $records = self::read( $source, $type, 0, min( (int) $sample, self::BATCH_SIZE * 4 ) );

            foreach ( $records as $object_id => $incoming ) {
                $existing = self::read_destination( $type, $object_id );

                foreach ( $incoming as $field => $value ) {
                    if ( ! empty( $existing[ $field ] ) && (string) $existing[ $field ] !== (string) $value ) {
                        $conflicts[ $type ]++;
                        break;
                    }
                }

                if ( count( $examples ) < 5 && ! empty( $incoming['meta_title'] ) ) {
                    $examples[] = [
                        'type'  => $type,
                        'id'    => $object_id,
                        'title' => $incoming['meta_title'],
                        'desc'  => $incoming['meta_description'] ?? '',
                    ];
                }
            }
        }

        return [
            'success'   => true,
            'slug'      => $slug,
            'label'     => $source->label(),
            'counts'    => $counts,
            'redirects' => $counts['redirects'],
            'settings'  => array_keys( $settings ),
            'conflicts' => $conflicts,
            'examples'  => $examples,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Running
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Process one batch and return progress.
     *
     * The caller (AJAX loop or WP-Cron) keeps calling this with the returned
     * cursor until `done` is true.
     *
     * @param string $slug    Source slug.
     * @param array  $options overwrite (bool), types (string[]), cursor (array).
     */
    public static function run_batch( $slug, array $options = [] ) {
        $source = self::get_source( $slug );
        if ( ! $source ) {
            return [ 'success' => false, 'error' => __( 'Unknown migration source.', 'gatetouch-ai-seo' ) ];
        }

        $overwrite = ! empty( $options['overwrite'] );
        $types     = ! empty( $options['types'] ) ? (array) $options['types'] : [ 'settings', 'posts', 'terms', 'users', 'redirects' ];
        $cursor    = $options['cursor'] ?? [ 'stage' => 0, 'offset' => 0 ];

        $stages = array_values( array_intersect( [ 'settings', 'posts', 'terms', 'users', 'redirects' ], $types ) );

        $stage_index = (int) ( $cursor['stage'] ?? 0 );
        $offset      = (int) ( $cursor['offset'] ?? 0 );

        $state = get_option( self::STATE_OPTION, [] );
        $state = is_array( $state ) ? $state : [];
        if ( 0 === $stage_index && 0 === $offset ) {
            // Fresh run.
            $state = [
                'slug'      => $slug,
                'started'   => time(),
                'imported'  => [ 'settings' => 0, 'posts' => 0, 'terms' => 0, 'users' => 0, 'redirects' => 0 ],
                'skipped'   => [ 'settings' => 0, 'posts' => 0, 'terms' => 0, 'users' => 0, 'redirects' => 0 ],
                'overwrite' => $overwrite,
            ];
            self::snapshot_settings();
        }

        // Finished every stage.
        if ( $stage_index >= count( $stages ) ) {
            $state['finished'] = time();
            update_option( self::STATE_OPTION, $state, false );
            update_option( self::REPORT_OPTION, $state, false );
            self::mark_imported( $slug, $state );

            return [
                'success' => true,
                'done'    => true,
                'state'   => $state,
            ];
        }

        $stage = $stages[ $stage_index ];

        switch ( $stage ) {
            case 'settings':
                $result = self::import_settings( $source, $overwrite );
                $state['imported']['settings'] += $result['imported'];
                $cursor = [ 'stage' => $stage_index + 1, 'offset' => 0 ];
                break;

            case 'redirects':
                $result = self::import_redirects( $source );
                $state['imported']['redirects'] += $result['imported'];
                $state['skipped']['redirects']  += $result['skipped'];
                $cursor = [ 'stage' => $stage_index + 1, 'offset' => 0 ];
                break;

            default:
                $records = self::read( $source, $stage, $offset, self::BATCH_SIZE );

                if ( empty( $records ) ) {
                    $cursor = [ 'stage' => $stage_index + 1, 'offset' => 0 ];
                    break;
                }

                foreach ( $records as $object_id => $incoming ) {
                    $written = self::write( $stage, $object_id, $incoming, $overwrite );
                    if ( $written ) {
                        $state['imported'][ $stage ]++;
                    } else {
                        $state['skipped'][ $stage ]++;
                    }
                }

                $cursor = [ 'stage' => $stage_index, 'offset' => $offset + self::BATCH_SIZE ];
                break;
        }

        update_option( self::STATE_OPTION, $state, false );

        return [
            'success' => true,
            'done'    => false,
            'stage'   => $stage,
            'cursor'  => $cursor,
            'state'   => $state,
        ];
    }

    /**
     * Run an entire migration synchronously.
     *
     * Suitable for WP-CLI and small sites. The admin UI drives run_batch()
     * instead so the browser can show progress and never times out.
     */
    public static function migrate( $slug, array $options = [] ) {
        $source = self::get_source( $slug );
        if ( ! $source ) {
            return [ 'success' => false, 'error' => __( 'Unsupported source.', 'gatetouch-ai-seo' ) ];
        }

        $cursor = [ 'stage' => 0, 'offset' => 0 ];
        $guard  = 0;

        do {
            $options['cursor'] = $cursor;
            $result            = self::run_batch( $slug, $options );

            if ( empty( $result['success'] ) ) {
                return $result;
            }

            $cursor = $result['cursor'] ?? $cursor;
            $guard++;
        } while ( empty( $result['done'] ) && $guard < 20000 );

        $state = $result['state'] ?? [];

        return [
            'success'  => true,
            'source'   => $source->label(),
            'migrated' => array_sum( $state['imported'] ?? [] ),
            'imported' => $state['imported'] ?? [],
            'skipped'  => $state['skipped'] ?? [],
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Reading / writing
    // ─────────────────────────────────────────────────────────────────────────

    private static function read( GateTouch_Migration_Source $source, $type, $offset, $limit ) {
        switch ( $type ) {
            case 'posts':
                return $source->get_posts( $offset, $limit );
            case 'terms':
                return $source->get_terms( $offset, $limit );
            case 'users':
                return $source->get_users( $offset, $limit );
        }

        return [];
    }

    /**
     * Current GateTouch meta for an object.
     */
    private static function read_destination( $type, $object_id ) {
        switch ( $type ) {
            case 'posts':
                $meta = get_post_meta( (int) $object_id, GATETOUCH_META_KEY, true );
                return is_array( $meta ) ? $meta : [];
            case 'terms':
                return GateTouch_Term_Meta::get( (int) $object_id );
            case 'users':
                return GateTouch_User_Meta::get( (int) $object_id );
        }

        return [];
    }

    /**
     * Merge incoming values into GateTouch storage.
     *
     * @return bool True if anything was actually written.
     */
    private static function write( $type, $object_id, array $incoming, $overwrite ) {
        $object_id = (int) $object_id;
        if ( ! $object_id ) {
            return false;
        }

        // Only write to objects that still exist — orphaned SEO rows are common
        // on sites that have deleted content without cleaning up meta.
        if ( ! self::object_exists( $type, $object_id ) ) {
            return false;
        }

        $existing = self::read_destination( $type, $object_id );
        $merged   = $existing;
        $changed  = false;

        foreach ( $incoming as $field => $value ) {
            if ( '' === $value || null === $value ) {
                continue;
            }

            $has_existing = isset( $existing[ $field ] ) && '' !== $existing[ $field ];

            if ( $has_existing && ! $overwrite ) {
                continue;
            }
            if ( $has_existing && (string) $existing[ $field ] === (string) $value ) {
                continue;
            }

            $merged[ $field ] = $value;
            $changed          = true;
        }

        if ( ! $changed ) {
            return false;
        }

        $merged = self::sanitize_meta( $merged );

        switch ( $type ) {
            case 'posts':
                update_post_meta( $object_id, GATETOUCH_META_KEY, $merged );
                return true;
            case 'terms':
                GateTouch_Term_Meta::update( $object_id, $merged );
                return true;
            case 'users':
                $existing_user = GateTouch_User_Meta::get( $object_id );
                update_user_meta( $object_id, GateTouch_User_Meta::META_KEY, array_merge( $existing_user, $merged ) );
                return true;
        }

        return false;
    }

    private static function object_exists( $type, $object_id ) {
        switch ( $type ) {
            case 'posts':
                return (bool) get_post_status( $object_id );
            case 'terms':
                return get_term( $object_id ) instanceof \WP_Term;
            case 'users':
                return (bool) get_userdata( $object_id );
        }

        return false;
    }

    /**
     * Sanitize migrated values. Source plugins store user input, and a site being
     * migrated may carry anything from years of editing.
     */
    private static function sanitize_meta( array $meta ) {
        $clean = [];

        foreach ( $meta as $key => $value ) {
            if ( is_array( $value ) ) {
                $clean[ $key ] = $value;
                continue;
            }

            switch ( $key ) {
                case 'canonical':
                case 'og_image':
                case 'twitter_image':
                    $clean[ $key ] = esc_url_raw( $value );
                    break;

                case 'meta_description':
                case 'og_description':
                case 'twitter_description':
                case 'custom_schema':
                    $clean[ $key ] = sanitize_textarea_field( $value );
                    break;

                default:
                    $clean[ $key ] = sanitize_text_field( $value );
                    break;
            }
        }

        return $clean;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Settings + redirects
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Keep a copy of the pre-migration settings so a bad import can be undone.
     */
    private static function snapshot_settings() {
        update_option( self::BACKUP_OPTION, [
            'time'              => time(),
            'search_appearance' => get_option( GateTouch_Search_Appearance::OPTION, [] ),
            'schema_settings'   => get_option( 'gatetouch_schema_settings', [] ),
            'social_settings'   => get_option( 'gatetouch_social_settings', [] ),
        ], false );
    }

    /**
     * Restore the settings captured before the last migration.
     */
    public static function rollback_settings() {
        $backup = get_option( self::BACKUP_OPTION );
        if ( ! is_array( $backup ) || empty( $backup['time'] ) ) {
            return [ 'success' => false, 'error' => __( 'No migration backup found.', 'gatetouch-ai-seo' ) ];
        }

        update_option( GateTouch_Search_Appearance::OPTION, $backup['search_appearance'] ?? [] );
        update_option( 'gatetouch_schema_settings', $backup['schema_settings'] ?? [] );
        update_option( 'gatetouch_social_settings', $backup['social_settings'] ?? [] );

        GateTouch_Search_Appearance::flush();

        return [ 'success' => true, 'restored' => gmdate( 'c', (int) $backup['time'] ) ];
    }

    /**
     * Import site-wide templates into Search Appearance and the schema settings.
     */
    private static function import_settings( GateTouch_Migration_Source $source, $overwrite ) {
        $incoming = $source->get_settings();
        if ( empty( $incoming ) ) {
            return [ 'imported' => 0 ];
        }

        $imported = 0;

        // Publisher entity and social profiles live in their own options.
        if ( ! empty( $incoming['_schema'] ) ) {
            $schema_in = $incoming['_schema'];
            unset( $incoming['_schema'] );

            $profiles = $schema_in['_profiles'] ?? [];
            unset( $schema_in['_profiles'] );

            $schema = get_option( 'gatetouch_schema_settings', [] );
            $schema = is_array( $schema ) ? $schema : [];

            foreach ( $schema_in as $key => $value ) {
                if ( '' === $value ) {
                    continue;
                }
                if ( ! empty( $schema[ $key ] ) && ! $overwrite ) {
                    continue;
                }
                $schema[ $key ] = $value;
                $imported++;
            }

            update_option( 'gatetouch_schema_settings', $schema );

            if ( $profiles ) {
                $social = get_option( 'gatetouch_social_settings', [] );
                $social = is_array( $social ) ? $social : [];

                $existing_profiles = isset( $social['profiles'] ) && is_array( $social['profiles'] ) ? $social['profiles'] : [];
                $social['profiles'] = array_values( array_unique( array_merge( $existing_profiles, $profiles ) ) );

                update_option( 'gatetouch_social_settings', $social );
                $imported++;
            }
        }

        // Everything else maps onto the Search Appearance tree.
        $current = get_option( GateTouch_Search_Appearance::OPTION, [] );
        $current = is_array( $current ) ? $current : [];

        foreach ( $incoming as $group => $rows ) {
            if ( ! is_array( $rows ) ) {
                continue;
            }

            foreach ( $rows as $key => $value ) {
                if ( is_array( $value ) ) {
                    foreach ( $value as $field => $field_value ) {
                        if ( '' === $field_value ) {
                            continue;
                        }
                        if ( ! empty( $current[ $group ][ $key ][ $field ] ) && ! $overwrite ) {
                            continue;
                        }
                        $current[ $group ][ $key ][ $field ] = $field_value;
                        $imported++;
                    }
                    continue;
                }

                if ( '' === $value ) {
                    continue;
                }
                if ( ! empty( $current[ $group ][ $key ] ) && ! $overwrite ) {
                    continue;
                }

                $current[ $group ][ $key ] = $value;
                $imported++;
            }
        }

        $current = GateTouch_Helpers::sanitize_search_appearance( $current ) + $current;

        update_option( GateTouch_Search_Appearance::OPTION, $current );
        GateTouch_Search_Appearance::flush();

        return [ 'imported' => $imported ];
    }

    /**
     * Import redirects, skipping any source URL we already redirect.
     */
    private static function import_redirects( GateTouch_Migration_Source $source ) {
        global $wpdb;

        $redirects = $source->get_redirects();
        if ( empty( $redirects ) ) {
            return [ 'imported' => 0, 'skipped' => 0 ];
        }

        $table = $wpdb->prefix . 'gatetouch_redirects';

        // The table only exists once the plugin has been activated properly.
        $exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
        if ( $exists !== $table ) {
            GateTouch_Redirects::create_table();
        }

        $imported = 0;
        $skipped  = 0;

        foreach ( $redirects as $redirect ) {
            $source_url = trim( (string) ( $redirect['source'] ?? '' ) );
            $target_url = trim( (string) ( $redirect['target'] ?? '' ) );

            if ( '' === $source_url || '' === $target_url ) {
                $skipped++;
                continue;
            }

            // Normalise to a site-relative path where possible.
            $source_url = self::normalise_path( $source_url );

            $already = $wpdb->get_var(
                $wpdb->prepare( "SELECT id FROM {$table} WHERE source_url = %s LIMIT 1", $source_url ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from $wpdb->prefix.
            );

            if ( $already ) {
                $skipped++;
                continue;
            }

            $type = (int) ( $redirect['type'] ?? 301 );
            if ( ! in_array( $type, [ 301, 302, 307, 410, 451 ], true ) ) {
                $type = 301;
            }

            $wpdb->insert(
                $table,
                [
                    'source_url' => $source_url,
                    'target_url' => $target_url,
                    'type'       => $type,
                    'format'     => ( 'regex' === ( $redirect['format'] ?? 'exact' ) ) ? 'regex' : 'exact',
                    'status'     => 'active',
                ],
                [ '%s', '%s', '%d', '%s', '%s' ]
            );

            $imported++;
        }

        return [ 'imported' => $imported, 'skipped' => $skipped ];
    }

    private static function normalise_path( $url ) {
        if ( 0 === strpos( $url, 'http' ) ) {
            $path = wp_parse_url( $url, PHP_URL_PATH );
            if ( $path ) {
                return $path;
            }
        }

        return '/' === substr( $url, 0, 1 ) ? $url : '/' . ltrim( $url, '/' );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Verification
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Re-read both sides and confirm the migration actually landed.
     *
     * Reports three categories:
     *   matched  — destination holds the migrated value
     *   differs  — destination holds a DIFFERENT non-empty value (expected when
     *              the run was non-destructive and the field was already set)
     *   missing  — destination has nothing, which indicates a genuine failure
     *
     * @param string $slug  Source slug.
     * @param int    $limit Maximum records to check per type.
     */
    public static function verify( $slug, $limit = 200 ) {
        $source = self::get_source( $slug );
        if ( ! $source ) {
            return [ 'success' => false, 'error' => __( 'Unknown migration source.', 'gatetouch-ai-seo' ) ];
        }

        $report = [
            'success' => true,
            'slug'    => $slug,
            'label'   => $source->label(),
            'types'   => [],
            'issues'  => [],
        ];

        foreach ( [ 'posts', 'terms', 'users' ] as $type ) {
            $checked = 0;
            $matched = 0;
            $differs = 0;
            $missing = 0;
            $offset  = 0;

            while ( $checked < $limit ) {
                $records = self::read( $source, $type, $offset, self::BATCH_SIZE );
                if ( empty( $records ) ) {
                    break;
                }

                foreach ( $records as $object_id => $incoming ) {
                    if ( ! self::object_exists( $type, $object_id ) ) {
                        continue;
                    }

                    $existing = self::read_destination( $type, $object_id );
                    $checked++;

                    foreach ( $incoming as $field => $value ) {
                        if ( '' === $value ) {
                            continue;
                        }

                        $current = $existing[ $field ] ?? '';

                        if ( '' === $current ) {
                            $missing++;
                            if ( count( $report['issues'] ) < 25 ) {
                                $report['issues'][] = [
                                    'type'  => $type,
                                    'id'    => $object_id,
                                    'field' => $field,
                                    'state' => 'missing',
                                ];
                            }
                        } elseif ( (string) $current === (string) $value ) {
                            $matched++;
                        } else {
                            $differs++;
                        }
                    }
                }

                $offset += self::BATCH_SIZE;
            }

            $report['types'][ $type ] = [
                'checked' => $checked,
                'matched' => $matched,
                'differs' => $differs,
                'missing' => $missing,
            ];
        }

        // Settings check.
        $incoming_settings = $source->get_settings();
        unset( $incoming_settings['_schema'] );

        $current_settings = GateTouch_Search_Appearance::settings();
        $settings_matched = 0;
        $settings_missing = 0;

        foreach ( $incoming_settings as $group => $rows ) {
            if ( ! is_array( $rows ) ) {
                continue;
            }
            foreach ( $rows as $key => $value ) {
                $fields = is_array( $value ) ? $value : [ $key => $value ];
                $base   = is_array( $value ) ? ( $current_settings[ $group ][ $key ] ?? [] ) : ( $current_settings[ $group ] ?? [] );

                foreach ( $fields as $field => $field_value ) {
                    if ( '' === $field_value ) {
                        continue;
                    }
                    if ( isset( $base[ $field ] ) && (string) $base[ $field ] === (string) $field_value ) {
                        $settings_matched++;
                    } else {
                        $settings_missing++;
                    }
                }
            }
        }

        $report['settings'] = [
            'matched' => $settings_matched,
            'missing' => $settings_missing,
        ];

        $totals = [ 'matched' => 0, 'missing' => 0, 'differs' => 0 ];
        foreach ( $report['types'] as $stats ) {
            $totals['matched'] += $stats['matched'];
            $totals['missing'] += $stats['missing'];
            $totals['differs'] += $stats['differs'];
        }
        $report['totals'] = $totals;

        // A migration is healthy when nothing that should have landed is absent.
        $report['healthy'] = ( 0 === $totals['missing'] );

        update_option( self::REPORT_OPTION . '_verify', $report, false );

        return $report;
    }

    /**
     * Progress/report of the most recent run.
     */
    public static function last_report() {
        $report = get_option( self::REPORT_OPTION, [] );
        return is_array( $report ) ? $report : [];
    }

    /**
     * Per-source record of completed imports.
     *
     * Detection alone cannot tell you whether a source has been imported: the
     * importer only reads, so the other plugin's data is still sitting there
     * afterwards and keeps being detected. Without this record the UI nags you
     * to import something you already imported. REPORT_OPTION only holds the
     * most recent run, so it cannot answer the question per source either.
     *
     * @return array<string, array{finished:int, imported:array}>
     */
    public static function imported_sources() {
        $done = get_option( self::IMPORTED_OPTION, [] );
        return is_array( $done ) ? $done : [];
    }

    public static function is_imported( $slug ) {
        $done = self::imported_sources();
        return ! empty( $done[ $slug ]['finished'] );
    }

    public static function mark_imported( $slug, array $state = [] ) {
        $done = self::imported_sources();
        $done[ $slug ] = [
            'finished' => (int) ( $state['finished'] ?? time() ),
            'imported' => (array) ( $state['imported'] ?? [] ),
        ];
        update_option( self::IMPORTED_OPTION, $done, false );
    }

    /** Forget a source's imported record, so it can be offered again. */
    public static function clear_imported( $slug ) {
        $done = self::imported_sources();
        unset( $done[ $slug ] );
        update_option( self::IMPORTED_OPTION, $done, false );
    }

    /** Detected sources that have not been imported yet. */
    public static function pending_sources() {
        $pending = [];
        foreach ( self::detect_sources() as $slug ) {
            if ( ! self::is_imported( $slug ) ) {
                $pending[] = $slug;
            }
        }
        return $pending;
    }
}
