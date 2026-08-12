<?php
defined( 'ABSPATH' ) || exit;

/**
 * Base class for SEO plugin migration adapters.
 *
 * Each adapter reads one source plugin's data and returns it in GateTouch's own
 * normalised shape, so the engine never needs to know how any particular plugin
 * stores things. Adapters are strictly read-only: nothing here writes to the
 * source plugin's tables or options.
 *
 * Normalised meta keys (all optional — only return what the source actually has):
 *   meta_title, meta_description, focus_keyword, additional_keywords, canonical,
 *   noindex, nofollow, noarchive, noimageindex, nosnippet,
 *   og_title, og_description, og_image,
 *   twitter_title, twitter_description, twitter_card,
 *   schema_type, custom_schema, primary_term, breadcrumb_title
 */
abstract class GateTouch_Migration_Source {

    /** Unique slug, e.g. "yoast". */
    abstract public function slug();

    /** Human-readable name, e.g. "Yoast SEO". */
    abstract public function label();

    /**
     * Whether this source has data on the site (regardless of whether the plugin
     * is still active — people often deactivate before migrating).
     */
    abstract public function is_detected();

    /**
     * How many records exist per object type.
     *
     * @return array{posts:int, terms:int, users:int, redirects:int}
     */
    public function counts() {
        return [
            'posts'     => 0,
            'terms'     => 0,
            'users'     => 0,
            'redirects' => 0,
        ];
    }

    /**
     * One page of post metadata.
     *
     * @return array<int, array> Keyed by post ID, values are normalised meta.
     */
    public function get_posts( $offset, $limit ) {
        return [];
    }

    /**
     * One page of term metadata.
     *
     * @return array<int, array> Keyed by term ID.
     */
    public function get_terms( $offset, $limit ) {
        return [];
    }

    /**
     * One page of user metadata.
     *
     * @return array<int, array> Keyed by user ID.
     */
    public function get_users( $offset, $limit ) {
        return [];
    }

    /**
     * Site-wide templates and settings, already mapped onto the GateTouch
     * Search Appearance structure.
     */
    public function get_settings() {
        return [];
    }

    /**
     * Redirects, normalised to the gatetouch_redirects table shape.
     *
     * @return array<int, array{source:string, target:string, type:int}>
     */
    public function get_redirects() {
        return [];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Variable translation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Source variable syntax => GateTouch syntax.
     *
     * Templates are worthless after a migration if their variables do not carry
     * across — a title of "%%title%% %%sep%% %%sitename%%" would render literally.
     *
     * @return array<string, string>
     */
    protected function variable_map() {
        return [];
    }

    /**
     * Convert a source template string into GateTouch variable syntax.
     */
    public function translate( $text ) {
        $text = (string) $text;
        if ( '' === $text ) {
            return '';
        }

        $map = $this->variable_map();
        if ( $map ) {
            // Longest tokens first so %%pt_plural%% is not partly eaten by %%pt%%.
            $keys = array_keys( $map );
            usort( $keys, static function ( $a, $b ) {
                return strlen( $b ) <=> strlen( $a );
            } );

            $ordered = [];
            foreach ( $keys as $key ) {
                $ordered[ $key ] = $map[ $key ];
            }

            $text = strtr( $text, $ordered );
        }

        $text = $this->translate_dynamic( $text );

        // Anything left in the source's syntax has no GateTouch equivalent.
        // Drop it rather than shipping a literal "%%unknown%%" to search engines.
        $text = $this->strip_unknown( $text );

        return trim( preg_replace( '/\s+/u', ' ', $text ) );
    }

    /**
     * Hook for parameterised variables such as %%cf_<name>%% or %customfield(x)%.
     */
    protected function translate_dynamic( $text ) {
        return $text;
    }

    /**
     * Remove leftover source-syntax tokens.
     */
    protected function strip_unknown( $text ) {
        return preg_replace( '/%%[^%\s]+%%/', '', $text );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Shared helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Normalise any truthy representation of a robots flag to '1' or ''.
     */
    protected function flag( $value ) {
        if ( is_array( $value ) ) {
            return ! empty( $value ) ? '1' : '';
        }

        $value = (string) $value;

        return in_array( $value, [ '1', 'yes', 'on', 'true' ], true ) ? '1' : '';
    }

    /**
     * Drop empty values so a migration never blanks an existing GateTouch field.
     */
    protected function compact_meta( array $meta ) {
        return array_filter(
            $meta,
            static function ( $value ) {
                if ( is_array( $value ) ) {
                    return ! empty( $value );
                }
                return '' !== $value && null !== $value;
            }
        );
    }

    /**
     * Resolve an attachment ID to a URL, passing through URLs unchanged.
     */
    protected function image_url( $value ) {
        if ( ! $value ) {
            return '';
        }
        if ( is_numeric( $value ) ) {
            return (string) wp_get_attachment_image_url( (int) $value, 'full' );
        }
        return esc_url_raw( (string) $value );
    }

    /**
     * Whether a database table exists. Adapters that read custom tables must
     * check this — an uninstalled plugin leaves options behind but drops tables.
     */
    protected function table_exists( $table ) {
        global $wpdb;

        static $cache = [];
        if ( isset( $cache[ $table ] ) ) {
            return $cache[ $table ];
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- schema probe, cached in-process.
        $found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );

        $cache[ $table ] = ( $found === $table );
        return $cache[ $table ];
    }

    /**
     * Count rows in postmeta for a set of meta keys.
     */
    protected function count_postmeta( array $keys ) {
        global $wpdb;

        if ( empty( $keys ) ) {
            return 0;
        }

        $placeholders = implode( ',', array_fill( 0, count( $keys ), '%s' ) );

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- one-off migration count over a fixed key list.
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key IN ({$placeholders}) AND meta_value != ''",
                $keys
            )
        );
        // phpcs:enable
    }

    /**
     * Fetch a page of postmeta rows for a set of keys, grouped by post.
     *
     * Paginating over distinct post IDs (rather than meta rows) keeps batches
     * stable and guarantees every field for a post arrives in the same batch.
     *
     * @return array<int, array<string, string>> post_id => [meta_key => value]
     */
    protected function fetch_postmeta_page( array $keys, $offset, $limit ) {
        global $wpdb;

        if ( empty( $keys ) ) {
            return [];
        }

        $placeholders = implode( ',', array_fill( 0, count( $keys ), '%s' ) );

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- bounded, paginated migration read.
        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT post_id FROM {$wpdb->postmeta}
                 WHERE meta_key IN ({$placeholders}) AND meta_value != ''
                 ORDER BY post_id ASC LIMIT %d OFFSET %d",
                array_merge( $keys, [ (int) $limit, (int) $offset ] )
            )
        );

        if ( empty( $ids ) ) {
            return [];
        }

        $id_placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta}
                 WHERE post_id IN ({$id_placeholders}) AND meta_key IN ({$placeholders})",
                array_merge( $ids, $keys )
            )
        );
        // phpcs:enable

        $out = [];
        foreach ( $rows as $row ) {
            $out[ (int) $row->post_id ][ $row->meta_key ] = $row->meta_value;
        }

        return $out;
    }

    /**
     * Same as fetch_postmeta_page(), for term meta.
     */
    protected function fetch_termmeta_page( array $keys, $offset, $limit ) {
        global $wpdb;

        if ( empty( $keys ) ) {
            return [];
        }

        $placeholders = implode( ',', array_fill( 0, count( $keys ), '%s' ) );

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- bounded, paginated migration read.
        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT term_id FROM {$wpdb->termmeta}
                 WHERE meta_key IN ({$placeholders}) AND meta_value != ''
                 ORDER BY term_id ASC LIMIT %d OFFSET %d",
                array_merge( $keys, [ (int) $limit, (int) $offset ] )
            )
        );

        if ( empty( $ids ) ) {
            return [];
        }

        $id_placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT term_id, meta_key, meta_value FROM {$wpdb->termmeta}
                 WHERE term_id IN ({$id_placeholders}) AND meta_key IN ({$placeholders})",
                array_merge( $ids, $keys )
            )
        );
        // phpcs:enable

        $out = [];
        foreach ( $rows as $row ) {
            $out[ (int) $row->term_id ][ $row->meta_key ] = $row->meta_value;
        }

        return $out;
    }

    /**
     * Count distinct terms carrying any of the given meta keys.
     */
    protected function count_termmeta( array $keys ) {
        global $wpdb;

        if ( empty( $keys ) ) {
            return 0;
        }

        $placeholders = implode( ',', array_fill( 0, count( $keys ), '%s' ) );

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- one-off migration count.
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT term_id) FROM {$wpdb->termmeta} WHERE meta_key IN ({$placeholders}) AND meta_value != ''",
                $keys
            )
        );
        // phpcs:enable
    }

    /**
     * Users carrying any of the given meta keys.
     */
    protected function fetch_usermeta_page( array $keys, $offset, $limit ) {
        global $wpdb;

        if ( empty( $keys ) ) {
            return [];
        }

        $placeholders = implode( ',', array_fill( 0, count( $keys ), '%s' ) );

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- bounded, paginated migration read.
        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT user_id FROM {$wpdb->usermeta}
                 WHERE meta_key IN ({$placeholders}) AND meta_value != ''
                 ORDER BY user_id ASC LIMIT %d OFFSET %d",
                array_merge( $keys, [ (int) $limit, (int) $offset ] )
            )
        );

        if ( empty( $ids ) ) {
            return [];
        }

        $id_placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT user_id, meta_key, meta_value FROM {$wpdb->usermeta}
                 WHERE user_id IN ({$id_placeholders}) AND meta_key IN ({$placeholders})",
                array_merge( $ids, $keys )
            )
        );
        // phpcs:enable

        $out = [];
        foreach ( $rows as $row ) {
            $out[ (int) $row->user_id ][ $row->meta_key ] = $row->meta_value;
        }

        return $out;
    }

    protected function count_usermeta( array $keys ) {
        global $wpdb;

        if ( empty( $keys ) ) {
            return 0;
        }

        $placeholders = implode( ',', array_fill( 0, count( $keys ), '%s' ) );

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- one-off migration count.
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT user_id) FROM {$wpdb->usermeta} WHERE meta_key IN ({$placeholders}) AND meta_value != ''",
                $keys
            )
        );
        // phpcs:enable
    }
}
