<?php
namespace AutoSEO\Services;

class RankMonitor {

    public function __construct() {
        add_action( 'autoseo_rank_check', [ $this, 'run_check' ] );
        if ( ! wp_next_scheduled( 'autoseo_rank_check' ) ) {
            wp_schedule_event( time(), 'daily', 'autoseo_rank_check' );
        }
    }

    /**
     * 查询关键词在 Google / 百度的排名（返回排名位置，-1 表示未进入前100）
     */
    public function check_keyword( string $keyword, string $engine = 'google' ): array {
        $settings = get_option( 'autoseo_settings', [] );
        return $engine === 'baidu'
            ? $this->check_baidu( $keyword, $settings )
            : $this->check_google( $keyword, $settings );
    }

    private function check_google( string $keyword, array $settings ): array {
        $key = $settings['google_api_key'] ?? '';
        $cx  = $settings['google_cx']      ?? '';
        if ( empty( $key ) || empty( $cx ) ) {
            return [ 'error' => 'Google API Key 或 CX 未配置' ];
        }

        $site = parse_url( get_site_url(), PHP_URL_HOST );
        $url  = add_query_arg( [
            'key'   => $key,
            'cx'    => $cx,
            'q'     => $keyword,
            'num'   => 10,
        ], 'https://www.googleapis.com/customsearch/v1' );

        $resp = wp_remote_get( $url, [ 'timeout' => 15 ] );
        if ( is_wp_error( $resp ) ) return [ 'error' => $resp->get_error_message() ];

        $body  = json_decode( wp_remote_retrieve_body( $resp ), true );
        $items = $body['items'] ?? [];
        $rank  = -1;
        foreach ( $items as $i => $item ) {
            if ( strpos( $item['link'], $site ) !== false ) { $rank = $i + 1; break; }
        }
        return [ 'keyword' => $keyword, 'engine' => 'google', 'rank' => $rank, 'checked_at' => time() ];
    }

    private function check_baidu( string $keyword, array $settings ): array {
        // 百度搜索 API（需申请 百度搜索推广 API 或第三方 SEO API）
        $key = $settings['baidu_api_key'] ?? '';
        if ( empty( $key ) ) return [ 'error' => '百度 API Key 未配置' ];

        // 示例：使用百度搜索结果抓取（生产环境请替换为真实 API 端点）
        $url  = 'https://www.baidu.com/s?wd=' . urlencode( $keyword ) . '&rn=10';
        $resp = wp_remote_get( $url, [
            'timeout'    => 15,
            'user-agent' => 'Mozilla/5.0 (compatible; AutoSEO-Pro/2.0)',
        ] );
        if ( is_wp_error( $resp ) ) return [ 'error' => $resp->get_error_message() ];

        $html = wp_remote_retrieve_body( $resp );
        $site = parse_url( get_site_url(), PHP_URL_HOST );
        preg_match_all( '/<h3[^>]*>.*?<\/h3>/is', $html, $titles );
        $rank = -1;
        foreach ( $titles[0] as $i => $block ) {
            if ( strpos( $block, $site ) !== false ) { $rank = $i + 1; break; }
        }
        return [ 'keyword' => $keyword, 'engine' => 'baidu', 'rank' => $rank, 'checked_at' => time() ];
    }

    /**
     * 每日定时任务：检查所有监控关键词并发送变动通知
     */
    public function run_check() {
        $keywords = get_option( 'autoseo_monitor_keywords', [] );
        if ( empty( $keywords ) ) return;

        $settings = get_option( 'autoseo_settings', [] );
        $email    = $settings['notify_email'] ?? get_option( 'admin_email' );
        $changes  = [];

        foreach ( $keywords as &$kw ) {
            $old_rank = $kw['rank'] ?? -1;
            $result   = $this->check_keyword( $kw['keyword'], $kw['engine'] ?? 'google' );
            if ( isset( $result['error'] ) ) continue;

            $new_rank    = $result['rank'];
            $kw['rank']  = $new_rank;
            $kw['checked_at'] = time();

            if ( $old_rank !== -1 && abs( $new_rank - $old_rank ) >= 3 ) {
                $direction = $new_rank < $old_rank ? '⬆ 上升' : '⬇ 下降';
                $changes[] = "[{$kw['engine']}] 关键词「{$kw['keyword']}」排名 {$direction}：#{$old_rank} → #{$new_rank}";
            }
        }

        update_option( 'autoseo_monitor_keywords', $keywords );

        if ( ! empty( $changes ) && $email ) {
            wp_mail(
                $email,
                '[AutoSEO Pro] 关键词排名变动提醒',
                implode( "\n", $changes ) . "\n\n查看详情：" . admin_url( 'admin.php?page=autoseo-pro' )
            );
        }
    }
}
