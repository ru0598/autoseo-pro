<?php
namespace AutoSEO\Services;

class Breadcrumb {

    public function __construct() {
        add_shortcode( 'autoseo_breadcrumb', [ $this, 'render' ] );
    }

    public function render(): string {
        $settings = get_option( 'autoseo_settings', [] );
        $sep      = $settings['breadcrumb_sep'] ?? ' › ';
        $items    = $this->get_items();
        if ( empty( $items ) ) return '';

        $parts = [];
        $last  = count( $items ) - 1;
        foreach ( $items as $i => $item ) {
            if ( $i === $last ) {
                $parts[] = '<span class="autoseo-bc-current">' . esc_html( $item['title'] ) . '</span>';
            } else {
                $parts[] = '<a class="autoseo-bc-link" href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['title'] ) . '</a>';
            }
        }
        return '<nav class="autoseo-breadcrumb" aria-label="Breadcrumb">'
            . implode( '<span class="autoseo-bc-sep">' . esc_html( $sep ) . '</span>', $parts )
            . '</nav>';
    }

    private function get_items(): array {
        $items = [ [ 'title' => '首页', 'url' => get_site_url() ] ];

        if ( is_single() ) {
            $cats = get_the_category();
            if ( $cats ) {
                $items[] = [ 'title' => $cats[0]->name, 'url' => get_category_link( $cats[0]->term_id ) ];
            }
            $items[] = [ 'title' => get_the_title(), 'url' => get_permalink() ];
        } elseif ( is_page() ) {
            global $post;
            if ( $post->post_parent ) {
                $items[] = [ 'title' => get_the_title( $post->post_parent ), 'url' => get_permalink( $post->post_parent ) ];
            }
            $items[] = [ 'title' => get_the_title(), 'url' => get_permalink() ];
        } elseif ( is_category() ) {
            $items[] = [ 'title' => single_cat_title( '', false ), 'url' => '' ];
        } elseif ( is_tag() ) {
            $items[] = [ 'title' => single_tag_title( '', false ), 'url' => '' ];
        } elseif ( is_archive() ) {
            $items[] = [ 'title' => get_the_archive_title(), 'url' => '' ];
        } elseif ( is_search() ) {
            $items[] = [ 'title' => '搜索：' . get_search_query(), 'url' => '' ];
        } elseif ( is_404() ) {
            $items[] = [ 'title' => '页面未找到', 'url' => '' ];
        }
        return $items;
    }
}
