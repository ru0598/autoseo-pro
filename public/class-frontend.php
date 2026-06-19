<?php
namespace AutoSEO;

require_once __DIR__ . '/class-share-bar.php';

class Frontend {

    private $settings;

    public function __construct() {
        $this->settings = get_option( 'autoseo_settings', [] );
        new ShareBar();
        add_action( 'wp_head', [ $this, 'inject_meta' ], 1 );
        add_filter( 'document_title_parts', [ $this, 'filter_title' ] );
        add_filter( 'wp_robots', [ $this, 'filter_robots' ] );
    }

    public function inject_meta() {
        if ( ! is_singular() ) return;
        global $post;

        // Meta Description
        $desc = get_post_meta( $post->ID, '_autoseo_meta_desc', true );
        if ( $desc ) {
            echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
        }

        // Meta Keywords (focus kw)
        $kw = get_post_meta( $post->ID, '_autoseo_focus_kw', true );
        if ( $kw ) {
            echo '<meta name="keywords" content="' . esc_attr( $kw ) . '" />' . "\n";
        }

        // Open Graph
        $og_title    = get_post_meta( $post->ID, '_autoseo_og_title',    true ) ?: get_the_title( $post->ID );
        $social_copy = get_post_meta( $post->ID, '_autoseo_social_copy', true );
        $og_desc     = $social_copy ?: $desc; // 分享文案优先，降级用元描述
        $img         = get_the_post_thumbnail_url( $post->ID, 'large' );
        // 封面图兆底：使用设置里配置的默认 OG 图片
        if ( ! $img ) {
            $default_og_img = $this->settings['default_og_image'] ?? '';
            if ( $default_og_img ) $img = $default_og_img;
        }
        echo '<meta property="og:type"        content="article" />' . "\n";
        echo '<meta property="og:site_name"   content="' . esc_attr( get_bloginfo('name') ) . '" />' . "\n";
        echo '<meta property="og:title"       content="' . esc_attr( $og_title ) . '" />' . "\n";
        echo '<meta property="og:url"         content="' . esc_url( get_permalink( $post->ID ) ) . '" />' . "\n";
        if ( $og_desc ) echo '<meta property="og:description" content="' . esc_attr( $og_desc ) . '" />' . "\n";
        if ( $img )     echo '<meta property="og:image"       content="' . esc_url( $img ) . '" />' . "\n";

        // Twitter Card
        echo '<meta name="twitter:card"        content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:title"       content="' . esc_attr( $og_title ) . '" />' . "\n";
        if ( $og_desc ) echo '<meta name="twitter:description" content="' . esc_attr( $og_desc ) . '" />' . "\n";
        if ( $img )     echo '<meta name="twitter:image"       content="' . esc_url( $img ) . '" />' . "\n";

        // Canonical
        $custom_canonical = get_post_meta( $post->ID, '_autoseo_canonical', true );
        $canonical = $custom_canonical ?: get_permalink( $post->ID );
        echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
    }

    public function filter_title( array $parts ): array {
        if ( is_singular() ) {
            global $post;
            $custom = get_post_meta( $post->ID, '_autoseo_seo_title', true );
            if ( $custom ) $parts['title'] = $custom;
        }
        $sep = $this->settings['title_sep'] ?? ' - ';
        if ( isset( $parts['site'] ) ) $parts['tagline'] = ''; // remove tagline
        return $parts;
    }

    public function filter_robots( array $robots ): array {
        if ( is_singular() ) {
            global $post;
            $noindex = get_post_meta( $post->ID, '_autoseo_noindex', true );
            if ( $noindex ) {
                $robots['noindex']  = true;
                $robots['nofollow'] = true;
            }
        }
        $settings = get_option( 'autoseo_settings', [] );
        if ( ! empty( $settings['noindex_archives'] ) && ( is_archive() || is_tag() ) ) {
            $robots['noindex'] = true;
        }
        return $robots;
    }
}
