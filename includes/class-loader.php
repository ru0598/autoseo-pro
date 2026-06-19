<?php
namespace AutoSEO;

class Loader {

    public function run() {
        // Core services
        foreach ( [
            'includes/class-activator.php',
            'includes/class-deactivator.php',
            'includes/services/class-ai-client.php',
            'includes/services/class-title-generator.php',
            'includes/services/class-meta-generator.php',
            'includes/services/class-keyword-extractor.php',
            'includes/services/class-repair-engine.php',
            'includes/services/class-seo-analyzer.php',
            'includes/services/class-rank-monitor.php',
            'includes/services/class-schema.php',
            'includes/services/class-sitemap.php',
            'includes/services/class-breadcrumb.php',
            'public/class-frontend.php',
        ] as $file ) {
            require_once AUTOSEO_PATH . $file;
        }

        // Admin only
        if ( is_admin() ) {
            foreach ( [
                'admin/class-settings.php',
                'admin/class-meta-box.php',
                'admin/class-dashboard.php',
                'admin/class-admin.php',
            ] as $file ) {
                require_once AUTOSEO_PATH . $file;
            }
            new \AutoSEO\Admin\Admin();
        }

        // Frontend
        new \AutoSEO\Frontend();

        // Sitemap rewrite
        new \AutoSEO\Services\Sitemap();

        // Schema
        new \AutoSEO\Services\Schema();

        // Breadcrumb shortcode
        new \AutoSEO\Services\Breadcrumb();

        // Rank monitor cron
        new \AutoSEO\Services\RankMonitor();
    }
}
