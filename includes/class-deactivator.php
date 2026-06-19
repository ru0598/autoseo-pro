<?php
namespace AutoSEO;

class Deactivator {
    public static function deactivate() {
        wp_clear_scheduled_hook( 'autoseo_rank_check' );
        flush_rewrite_rules();
    }
}
