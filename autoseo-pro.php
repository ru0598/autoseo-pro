<?php
/**
 * Plugin Name: AutoSEO Pro
 * Plugin URI:  https://sunnywp.com/
 * Description: 全自动 AI SEO 优化插件。AI 标题生成、自动摘要、一键修复、竞品排名监控、Schema 注入、Sitemap 生成。
 * Version:     2.0.0
 * Author:      SunnyWP
 * Text Domain: autoseo-pro
 * Domain Path: /languages
 * Requires at least: 6.2
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'AUTOSEO_PATH',    plugin_dir_path( __FILE__ ) );
define( 'AUTOSEO_URL',     plugin_dir_url( __FILE__ ) );
define( 'AUTOSEO_VERSION', '2.0.0' );
define( 'AUTOSEO_FILE',    __FILE__ );

require_once AUTOSEO_PATH . 'includes/class-loader.php';

register_activation_hook(   __FILE__, [ 'AutoSEO\\Activator',   'activate'   ] );
register_deactivation_hook( __FILE__, [ 'AutoSEO\\Deactivator', 'deactivate' ] );

function autoseo_pro_run() {
    $loader = new AutoSEO\Loader();
    $loader->run();
}
autoseo_pro_run();
