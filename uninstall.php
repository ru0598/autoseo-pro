<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

foreach ( [ 'autoseo_settings', 'autoseo_activated_at', 'autoseo_monitor_keywords' ] as $opt ) {
    delete_option( $opt );
}

global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_autoseo_%'" );

wp_clear_scheduled_hook( 'autoseo_rank_check' );
