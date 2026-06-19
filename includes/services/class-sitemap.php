<?php
namespace AutoSEO\Services;

class Sitemap {

    public function __construct() {
        add_action( 'init',          [ $this, 'add_rewrite' ] );
        add_filter( 'query_vars',    [ $this, 'add_query_var' ] );
        add_action( 'template_redirect', [ $this, 'serve' ] );
        add_action( 'save_post',     [ $this, 'flush' ] );
    }

    public function add_rewrite() {
        add_rewrite_rule( '^sitemap\.xml$', 'index.php?autoseo_sitemap=1', 'top' );
        add_rewrite_rule( '^sitemap-([a-z]+)-(\d+)\.xml$', 'index.php?autoseo_sitemap=$matches[1]&autoseo_sitemap_page=$matches[2]', 'top' );
    }

    public function add_query_var( $vars ) {
        $vars[] = 'autoseo_sitemap';
        $vars[] = 'autoseo_sitemap_page';
        return $vars;
    }

    public function serve() {
        $type = get_query_var( 'autoseo_sitemap' );
        if ( ! $type ) return;

        header( 'Content-Type: application/xml; charset=utf-8' );
        header( 'X-Robots-Tag: noindex, follow' );

        if ( $type === '1' || $type === 'index' ) {
            echo $this->build_index();
        } elseif ( $type === 'posts' ) {
            echo $this->build_posts_sitemap( 'post' );
        } elseif ( $type === 'pages' ) {
            echo $this->build_posts_sitemap( 'page' );
        }
        exit;
    }

    public function flush() {
        flush_rewrite_rules();
    }

    private function build_index(): string {
        $sitemaps = [
            get_site_url() . '/sitemap-posts-1.xml',
            get_site_url() . '/sitemap-pages-1.xml',
        ];
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ( $sitemaps as $s ) {
            $xml .= "  <sitemap>\n    <loc>" . esc_url( $s ) . "</loc>\n    <lastmod>" . date( 'Y-m-d' ) . "</lastmod>\n  </sitemap>\n";
        }
        $xml .= '</sitemapindex>';
        return $xml;
    }

    private function build_posts_sitemap( string $post_type ): string {
        $posts = get_posts( [
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => 1000,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ] );

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
         xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach ( $posts as $p ) {
            $img  = get_the_post_thumbnail_url( $p->ID, 'large' );
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . esc_url( get_permalink( $p->ID ) ) . "</loc>\n";
            $xml .= "    <lastmod>" . get_the_modified_date( 'Y-m-d', $p->ID ) . "</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>" . ( $post_type === 'page' ? '0.8' : '0.6' ) . "</priority>\n";
            if ( $img ) {
                $xml .= "    <image:image><image:loc>" . esc_url( $img ) . "</image:loc></image:image>\n";
            }
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';
        return $xml;
    }
}
