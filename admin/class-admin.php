<?php
namespace AutoSEO\Admin;

class Admin {

    public function __construct() {
        new MetaBox();
        new Settings();

        add_action( 'admin_menu',            [ $this, 'register_menu' ] );
        add_filter( 'admin_footer_text',      [ $this, 'footer_text' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );

        foreach ( [ 'autoseo_gen_titles','autoseo_gen_meta','autoseo_gen_kw','autoseo_repair_all',
                    'autoseo_rank_check_now','autoseo_add_keyword','autoseo_delete_keyword' ] as $action ) {
            add_action( 'wp_ajax_' . $action, [ $this, 'handle_ajax' ] );
        }
    }

    public function register_menu() {
        add_menu_page( 'AutoSEO Pro', 'AutoSEO Pro', 'manage_options',
            'autoseo-pro', [ $this, 'page_dashboard' ], 'dashicons-search', 25 );

        add_submenu_page( 'autoseo-pro', '仪表盘',   '仪表盘',   'manage_options', 'autoseo-pro',       [ $this, 'page_dashboard' ] );
        add_submenu_page( 'autoseo-pro', '排名监控', '排名监控', 'manage_options', 'autoseo-rank',      [ $this, 'page_rank' ] );
        add_submenu_page( 'autoseo-pro', '全站分析', '全站分析', 'manage_options', 'autoseo-bulk',      [ $this, 'page_bulk' ] );
        add_submenu_page( 'autoseo-pro', '设置',     '设置',     'manage_options', 'autoseo-settings',  [ $this, 'page_settings' ] );
    }

    public function enqueue( string $hook ) {
        $is_autoseo = strpos( $hook, 'autoseo' ) !== false;
        $is_editor  = in_array( $hook, [ 'post.php', 'post-new.php' ] );
        if ( ! $is_autoseo && ! $is_editor ) return;

        wp_enqueue_style( 'autoseo-admin', AUTOSEO_URL . 'assets/css/admin.css', [], AUTOSEO_VERSION );
        wp_enqueue_script( 'autoseo-admin', AUTOSEO_URL . 'assets/js/admin.js', [ 'jquery' ], AUTOSEO_VERSION, true );
        wp_localize_script( 'autoseo-admin', 'autoseo', [ 'ajaxurl' => admin_url( 'admin-ajax.php' ) ] );
    }

    public function footer_text( string $text ): string {
        $screen = get_current_screen();
        if ( $screen && strpos( $screen->id, 'autoseo' ) !== false ) {
            return '感谢使用 <a  target="_blank" href="https://sunnywp.com" target="_blank">AutoSEO Pro</a> 进行创作';
        }
        return $text;
    }

    public function page_dashboard() { ( new Dashboard() )->render(); }
    public function page_rank()      {
        require_once AUTOSEO_PATH . 'admin/class-rank.php';
        ( new Rank() )->render();
    }
    public function page_bulk()      { ( new Dashboard() )->render_bulk(); }
    public function page_settings()  { ( new Settings() )->render(); }

    public function handle_ajax() {
        $current = current_action();
        $action  = preg_replace( '/^wp_ajax_(no_priv_)?autoseo_/', '', $current );

        $nonce_ok = check_ajax_referer( 'autoseo_ajax', 'nonce', false );
        if ( ! $nonce_ok ) { wp_send_json_error( 'Nonce 验证失败，请刷新页面后重试' ); return; }
        if ( ! current_user_can( 'edit_posts' ) ) { wp_send_json_error( '权限不足' ); return; }

        $post_id = (int) ( $_POST['post_id'] ?? 0 );

        switch ( $action ) {
            case 'gen_titles':
                $r = ( new \AutoSEO\Services\TitleGenerator() )->generate( $post_id );
                is_wp_error( $r ) ? wp_send_json_error( $r->get_error_message() ) : wp_send_json_success( $r );
                break;

            case 'gen_kw':
                $kw = ( new \AutoSEO\Services\KeywordExtractor() )->extract( $post_id );
                is_wp_error( $kw ) ? wp_send_json_error( $kw->get_error_message() ) : wp_send_json_success( $kw );
                break;

            case 'gen_meta':
                $gen = new \AutoSEO\Services\MetaGenerator();
                $r   = $gen->generate( $post_id );
                if ( is_wp_error( $r ) ) { wp_send_json_error( $r->get_error_message() ); return; }
                $gen->save( $post_id, $r );
                wp_send_json_success( $r );
                break;

            case 'repair_all':
                wp_send_json_success( ( new \AutoSEO\Services\RepairEngine() )->repair_all( $post_id ) );
                break;

            case 'rank_check_now':
                ( new \AutoSEO\Services\RankMonitor() )->run_check();
                wp_send_json_success( '检查完成' );
                break;

            case 'add_keyword':
                $kw   = sanitize_text_field( $_POST['keyword'] ?? '' );
                $eng  = sanitize_text_field( $_POST['engine']  ?? 'google' );
                if ( ! $kw ) { wp_send_json_error( '关键词不能为空' ); return; }
                $list = get_option( 'autoseo_monitor_keywords', [] );
                $list[] = [ 'keyword' => $kw, 'engine' => $eng, 'rank' => -1, 'checked_at' => 0 ];
                update_option( 'autoseo_monitor_keywords', $list );
                wp_send_json_success( $list );
                break;

            case 'delete_keyword':
                $idx  = (int) ( $_POST['index'] ?? -1 );
                $list = get_option( 'autoseo_monitor_keywords', [] );
                if ( isset( $list[ $idx ] ) ) array_splice( $list, $idx, 1 );
                update_option( 'autoseo_monitor_keywords', $list );
                wp_send_json_success( $list );
                break;
        }
    }
}
