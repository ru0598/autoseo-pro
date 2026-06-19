<?php
namespace AutoSEO\Services;

class Schema {

    public function __construct() {
        add_action( 'wp_head', [ $this, 'output' ], 5 );
    }

    public function output() {
        if ( is_singular() ) {
            global $post;
            $data = $this->build( $post );
        } elseif ( is_home() || is_front_page() ) {
            $data = $this->build_website();
        } else {
            return;
        }

        echo "\n<script type=\"application/ld+json\">\n"
            . wp_json_encode( $data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT )
            . "\n</script>\n";
    }

    private function build( \WP_Post $post ): array {
        $settings = get_option( 'autoseo_settings', [] );
        $type     = $settings['schema_type'] ?? 'Article';
        $img      = get_the_post_thumbnail_url( $post->ID, 'large' );

        $data = [
            '@context'  => 'https://schema.org',
            '@type'     => $type,
            'headline'  => get_the_title( $post->ID ),
            'url'       => get_permalink( $post->ID ),
            'datePublished' => get_the_date( 'c', $post->ID ),
            'dateModified'  => get_the_modified_date( 'c', $post->ID ),
            'author'    => [
                '@type' => 'Person',
                'name'  => get_the_author_meta( 'display_name', $post->post_author ),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name'  => get_bloginfo( 'name' ),
                'logo'  => [
                    '@type' => 'ImageObject',
                    'url'   => get_site_icon_url( 512 ) ?: get_site_url() . '/favicon.ico',
                ],
            ],
        ];

        if ( $img ) $data['image'] = $img;

        $meta = get_post_meta( $post->ID, '_autoseo_meta_desc', true );
        if ( $meta ) $data['description'] = $meta;

        // BreadcrumbList
        $data['breadcrumb'] = $this->build_breadcrumb_schema( $post );

        return $data;
    }

    private function build_website(): array {
        return [
            '@context' => 'https://schema.org',
            '@type'    => 'WebSite',
            'name'     => get_bloginfo( 'name' ),
            'url'      => get_site_url(),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => get_site_url() . '/?s={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    private function build_breadcrumb_schema( \WP_Post $post ): array {
        $items = [
            [ '@type' => 'ListItem', 'position' => 1, 'name' => '首页', 'item' => get_site_url() ],
        ];
        $cats = get_the_category( $post->ID );
        if ( $cats ) {
            $items[] = [ '@type' => 'ListItem', 'position' => 2, 'name' => $cats[0]->name, 'item' => get_category_link( $cats[0]->term_id ) ];
            $items[] = [ '@type' => 'ListItem', 'position' => 3, 'name' => get_the_title( $post->ID ), 'item' => get_permalink( $post->ID ) ];
        } else {
            $items[] = [ '@type' => 'ListItem', 'position' => 2, 'name' => get_the_title( $post->ID ), 'item' => get_permalink( $post->ID ) ];
        }
        return [ '@type' => 'BreadcrumbList', 'itemListElement' => $items ];
    }
}
