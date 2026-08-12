<?php
/**
 * GateTouch List Table Implementation
 * 
 * Handles all logic for edit.php (Pages, Posts, Products list screens).
 * Provides columns, filters, row actions, and bulk tools.
 */

defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- WordPress list-table filters use sanitized read-only GET parameters.

/**
 * Adds GateTouch list-table controls for posts, pages, and products.
 */
class GateTouch_List_Table {

    private $post_types = ['post', 'page', 'product'];

    public function __construct() {
        foreach ( $this->post_types as $type ) {
            // Columns
            add_filter( "manage_{$type}_posts_columns", [ $this, 'add_columns' ] );
            add_action( "manage_{$type}_posts_custom_column", [ $this, 'render_columns' ], 10, 2 );
            add_filter( "manage_edit-{$type}_sortable_columns", [ $this, 'sortable_columns' ] );
            
            // Filters
            add_action( 'restrict_manage_posts', [ $this, 'add_filters' ], 10, 2 );
            add_filter( 'parse_query', [ $this, 'process_filters' ] );

            // Row Actions removed per user request

            // Bulk Actions
            add_filter( "bulk_actions-edit-{$type}", [ $this, 'register_bulk_actions' ] );
            add_filter( "handle_bulk_actions-edit-{$type}", [ $this, 'handle_bulk_actions' ], 10, 3 );
        }

        // Assets for the list screen
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    /**
     * Enqueue CSS/JS specifically for the list table
     */
    public function enqueue_assets( $hook ) {
        if ( 'edit.php' !== $hook ) return;

        wp_enqueue_style( 'gatetouch-list-table', GATETOUCH_URL . 'assets/css/list-table.css', [], GATETOUCH_VERSION );
        wp_enqueue_script( 'gatetouch-list-table', GATETOUCH_URL . 'assets/js/list-table.js', ['jquery'], GATETOUCH_VERSION, true );
        
        wp_localize_script( 'gatetouch-list-table', 'gatetouchList', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'gatetouch_list_nonce' ),
        ] );
    }

    /**
     * Register New Enterprise Columns
     */
    public function add_columns( $columns ) {
        $new_columns = [];
        
        foreach ( $columns as $key => $title ) {
            $new_columns[$key] = $title;
            
            // Insert after Title or before Author
            if ( 'title' === $key ) {
                $new_columns['gatetouch_seo'] = '<span class="gatetouch-col-icon" title="SEO Score">' . GateTouch_Helpers::icon( 'chart-bar', 14 ) . '</span> SEO'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static trusted SVG
                $new_columns['gatetouch_ai']  = '<span class="gatetouch-col-icon" title="AI Readiness">' . GateTouch_Helpers::icon( 'brain', 14 ) . '</span> AI'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static trusted SVG
            }
        }

        // Add to the end if not already inserted
        if ( !isset($new_columns['gatetouch_seo']) ) {
            $new_columns['gatetouch_seo'] = 'SEO';
            $new_columns['gatetouch_ai']  = 'AI';
        }

        return $new_columns;
    }

    /**
     * Render Column Content
     */
    public function render_columns( $column, $post_id ) {
        $meta = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];
        
        switch ( $column ) {
            case 'gatetouch_seo':
                $score = isset($meta['score']) ? (int)$meta['score'] : 0;
                $this->render_score_badge( $score );
                break;

            case 'gatetouch_ai':
                $score = isset($meta['ai_score']) ? (int)$meta['ai_score'] : 0;
                $this->render_ai_badge( $score );
                break;

            case 'gatetouch_meta':
                $title = !empty($meta['meta_title']);
                $desc  = !empty($meta['meta_description']);
                $kw    = !empty($meta['focus_keyword']);
                
                echo '<div class="gatetouch-meta-status">';
                echo '<span class="gatetouch-dot ' . esc_attr( $title ? 'is-good' : 'is-bad' ) . '" title="' . esc_attr( 'Title: ' . ( $title ? 'Set' : 'Missing' ) ) . '">T</span>';
                echo '<span class="gatetouch-dot ' . esc_attr( $desc ? 'is-good' : 'is-bad' ) . '" title="' . esc_attr( 'Description: ' . ( $desc ? 'Set' : 'Missing' ) ) . '">D</span>';
                echo '<span class="gatetouch-dot ' . esc_attr( $kw ? 'is-good' : 'is-bad' ) . '" title="' . esc_attr( 'Keyword: ' . ( $kw ? 'Set' : 'Missing' ) ) . '">K</span>';
                echo '</div>';
                if ($kw) echo '<div class="gatetouch-kw-small">' . esc_html($meta['focus_keyword']) . '</div>';
                break;

            case 'gatetouch_schema':
                $type = !empty($meta['schema_type']) ? $meta['schema_type'] : 'None';
                $class = $type !== 'None' ? 'is-active' : '';
                echo '<span class="gatetouch-badge-schema ' . esc_attr( $class ) . '">' . esc_html($type) . '</span>';
                break;

            case 'gatetouch_index':
                $status = isset($meta['index_status']) ? $meta['index_status'] : 'unknown';
                $label = ucfirst($status);
                $color = 'gray';
                if ($status === 'indexed') $color = 'green';
                if ($status === 'noindex') $color = 'red';
                echo '<span class="gatetouch-status-dot dot-' . esc_attr( $color ) . '"></span> ' . esc_html( $label );
                break;

            case 'gatetouch_details':
                $words = isset($meta['word_count']) ? $meta['word_count'] : 0;
                $links = isset($meta['internal_links_count']) ? $meta['internal_links_count'] : 0;
                echo '<div style="font-size:11px; color:#64748b;">';
                echo '<strong>' . esc_html( number_format_i18n( $words ) ) . '</strong> words<br>';
                echo '<strong>' . esc_html( number_format_i18n( $links ) ) . '</strong> int. links';
                echo '</div>';
                break;
        }
    }

    private function render_score_badge( $score ) {
        $color = '#ef4444';
        if ($score >= 80) $color = '#10b981';
        elseif ($score >= 50) $color = '#f59e0b';
        
        echo '<div class="gatetouch-list-score" style="--score-color: ' . esc_attr( $color ) . ';">' . esc_html( $score ) . '</div>';
    }

    private function render_ai_badge( $score ) {
        $color = '#6366f1';
        if ($score === 0) $color = '#94a3b8';
        echo '<div class="gatetouch-list-ai-score" style="--ai-color: ' . esc_attr( $color ) . ';">' . esc_html( $score ) . '%</div>';
    }

    /**
     * Sortable Columns
     */
    public function sortable_columns( $columns ) {
        $columns['gatetouch_seo'] = 'gatetouch_seo_score';
        $columns['gatetouch_ai']  = 'gatetouch_ai_score';
        return $columns;
    }

    /**
     * Row Actions (Removed per user request)
     */
    public function add_row_actions( $actions, \WP_Post $post ) {
        return $actions;
    }

    /**
     * Add Filters to List Table
     */
    public function add_filters( $post_type, $which ) {
        if ( !in_array( $post_type, $this->post_types ) || 'top' !== $which ) return;

        $current = isset( $_GET['gatetouch_filter'] ) ? sanitize_key( wp_unslash( $_GET['gatetouch_filter'] ) ) : '';
        
        $filters = [
            'low_score'    => __( 'Low SEO Score (< 50)', 'gatetouch-ai-seo' ),
            'missing_meta' => __( 'Missing Meta Data', 'gatetouch-ai-seo' ),
            'no_schema'    => __( 'No Schema Markup', 'gatetouch-ai-seo' ),
            'noindex'      => __( 'No-Index Posts', 'gatetouch-ai-seo' ),
            'low_ai'       => __( 'Low AI Readiness', 'gatetouch-ai-seo' ),
        ];

        echo '<select name="gatetouch_filter">';
        echo '<option value="">' . esc_html__( 'All SEO Status', 'gatetouch-ai-seo' ) . '</option>';
        foreach ( $filters as $val => $label ) {
            printf( '<option value="%s" %s>%s</option>', esc_attr( $val ), selected( $current, $val, false ), esc_html( $label ) );
        }
        echo '</select>';
    }

    /**
     * Process Filter Logic
     */
    public function process_filters( $query ) {
        if ( !is_admin() || !$query->is_main_query() || empty($_GET['gatetouch_filter']) ) return;

        $filter = sanitize_key( $_GET['gatetouch_filter'] );
        $meta_query = $query->get('meta_query') ?: [];

        switch ( $filter ) {
            case 'low_score':
                $meta_query[] = [
                    'key'     => GATETOUCH_META_KEY,
                    'value'   => '"score";i:50',
                    'compare' => '<',
                    'type'    => 'NUMERIC'
                ];
                break;
            case 'missing_meta':
                $meta_query[] = [
                    'key'     => GATETOUCH_META_KEY,
                    'value'   => '"meta_title";s:0:""',
                    'compare' => 'LIKE'
                ];
                break;
            // Additional complex filter logic would go here
        }

        $query->set('meta_query', $meta_query);
    }

    /**
     * Register Bulk Actions
     */
    public function register_bulk_actions( $actions ) {
        $actions['gatetouch_bulk_optimize'] = 'GT SEO/GEO/AEO: Optimize All (AI)';
        $actions['gatetouch_bulk_meta']     = 'GT SEO/GEO/AEO: Generate Meta';
        $actions['gatetouch_bulk_schema']   = 'GT SEO/GEO/AEO: Generate Schema';
        $actions['gatetouch_bulk_index']    = 'GT SEO/GEO/AEO: Request Indexing';
        return $actions;
    }

    /**
     * Handle Bulk Actions
     */
    public function handle_bulk_actions( $redirect_to, $doaction, $post_ids ) {
        if ( strpos( $doaction, 'gatetouch_bulk' ) === false ) return $redirect_to;

        $count = count( $post_ids );
        
        // In a real implementation, we'd trigger a background process or loop
        // For now, we'll just mock the success redirect
        return add_query_arg( [
            'gatetouch_bulk_success' => $count,
            'gatetouch_action'      => $doaction
        ], $redirect_to );
    }
}

new GateTouch_List_Table();
