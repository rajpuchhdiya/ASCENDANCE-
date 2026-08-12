<?php
defined( 'ABSPATH' ) || exit;

/**
 * Slim SEO migration adapter.
 *
 * Slim SEO stores everything for a post in one serialised `slim_seo` meta value,
 * and term data in a `slim_seo` term meta value with the same shape. It uses
 * `{{ handlebars }}` template variables.
 */
class GateTouch_Migration_Slimseo extends GateTouch_Migration_Source {

    const META_KEY = 'slim_seo';

    public function slug() {
        return 'slimseo';
    }

    public function label() {
        return 'Slim SEO';
    }

    public function is_detected() {
        if ( defined( 'SLIM_SEO_VER' ) ) {
            return true;
        }

        return $this->count_postmeta( [ self::META_KEY ] ) > 0;
    }

    protected function variable_map() {
        return [
            '{{ post.title }}'        => '#title#',
            '{{ post.excerpt }}'      => '#excerpt#',
            '{{ post.date }}'         => '#date#',
            '{{ post.modified_date }}' => '#modified#',
            '{{ post.author.display_name }}' => '#author_name#',
            '{{ site.title }}'        => '#site_title#',
            '{{ site.description }}'  => '#tagline#',
            '{{ term.name }}'         => '#term#',
            '{{ term.description }}'  => '#term_description#',
            '{{ archive.title }}'     => '#archive_title#',
            '{{ author.display_name }}' => '#author_name#',
            '{{ search.query }}'      => '#search_query#',
            '{{ sep }}'               => '#sep#',
        ];
    }

    protected function strip_unknown( $text ) {
        return preg_replace( '/\{\{[^}]*\}\}/', '', $text );
    }

    public function counts() {
        return [
            'posts'     => $this->count_postmeta( [ self::META_KEY ] ),
            'terms'     => $this->count_termmeta( [ self::META_KEY ] ),
            'users'     => 0,
            'redirects' => 0,
        ];
    }

    public function get_posts( $offset, $limit ) {
        return $this->normalise_rows( $this->fetch_postmeta_page( [ self::META_KEY ], $offset, $limit ) );
    }

    public function get_terms( $offset, $limit ) {
        return $this->normalise_rows( $this->fetch_termmeta_page( [ self::META_KEY ], $offset, $limit ) );
    }

    private function normalise_rows( array $rows ) {
        $out = [];

        foreach ( $rows as $id => $raw ) {
            $data = maybe_unserialize( $raw[ self::META_KEY ] ?? '' );
            if ( ! is_array( $data ) ) {
                continue;
            }

            $meta = $this->compact_meta( [
                'meta_title'       => $this->translate( $data['title'] ?? '' ),
                'meta_description' => $this->translate( $data['description'] ?? '' ),
                'og_image'         => $this->image_url( $data['facebook_image'] ?? '' ),
                'canonical'        => (string) ( $data['canonical'] ?? '' ),
                'noindex'          => $this->flag( $data['noindex'] ?? '' ),
            ] );

            if ( $meta ) {
                $out[ $id ] = $meta;
            }
        }

        return $out;
    }
}
