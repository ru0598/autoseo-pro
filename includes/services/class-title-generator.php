<?php
namespace AutoSEO\Services;

class TitleGenerator {

    private $ai;

    public function __construct() {
        $this->ai = new AIClient();
    }

    /**
     * 根据文章正文生成 5 个高点击率标题
     * @return string[]|\WP_Error
     */
    public function generate( int $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post || empty( trim( $post->post_content ) ) ) {
            return new \WP_Error( 'no_content', '文章内容为空，请先写入正文内容再生成标题' );
        }

        $content  = wp_strip_all_tags( $post->post_content );
        $excerpt  = mb_substr( $content, 0, 600 );
        $focus_kw = get_post_meta( $post_id, '_autoseo_focus_kw', true );
        $kw_line  = $focus_kw ? "关键词：{$focus_kw}\n" : '';

        $system = '你是中文 SEO 专家。用户发来文章摘要，你直接输出一个 JSON 数组，数组包含 5 个中文标题字符串。除了这个 JSON 数组之外，不输出任何其他内容——不要解释，不要编号，不要代码块，不要英文。';

        $user = "文章摘要：\n{$excerpt}\n{$kw_line}\n"
              . "输出格式（只输出这一行）：\n"
              . '["标题A","标题B","标题C","标题D","标题E"]';

        $raw = $this->ai->ask( $user, 400, $system );
        if ( is_wp_error( $raw ) ) return $raw;

        return $this->parse_titles( $raw );
    }

    private function parse_titles( string $raw ): array {
        $raw = trim( $raw );

        // 去掉 markdown 代码块
        $raw = preg_replace( '/^```(?:json)?\s*/i', '', $raw );
        $raw = preg_replace( '/\s*```\s*$/', '', trim( $raw ) );
        $raw = trim( $raw );

        // 尝试提取最外层 JSON 数组
        if ( preg_match( '/\[.*?\]/s', $raw, $m ) ) {
            $arr = json_decode( $m[0], true );
            if ( is_array( $arr ) ) {
                $titles = $this->filter_titles( $arr );
                if ( count( $titles ) >= 3 ) {
                    return array_slice( $titles, 0, 5 );
                }
            }
        }

        // 降级：逐行提取，过滤掉英文/说明行
        $titles = [];
        foreach ( explode( "\n", $raw ) as $line ) {
            // 去掉序号、引号、括号等
            $line = trim( $line );
            $line = preg_replace( '/^[\d\[\]"\'`，,.\-\*、。\s]+/', '', $line );
            $line = preg_replace( '/[\[\]"\'`，,\s]+$/', '', $line );
            $line = trim( $line );
            if ( $this->is_valid_title( $line ) ) {
                $titles[] = $line;
            }
        }
        return array_slice( $titles, 0, 5 );
    }

    private function filter_titles( array $arr ): array {
        $out = [];
        foreach ( $arr as $t ) {
            $t = trim( (string) $t );
            if ( $this->is_valid_title( $t ) ) {
                $out[] = $t;
            }
        }
        return $out;
    }

    /**
     * 判断是否为有效的中文标题：
     * - 长度 5-80 字符
     * - 含至少 3 个中文字符
     * - 不是纯英文说明句
     */
    private function is_valid_title( string $t ): bool {
        $len = mb_strlen( $t );
        if ( $len < 5 || $len > 80 ) return false;

        // 至少包含 3 个中文字符
        preg_match_all( '/[\x{4e00}-\x{9fff}]/u', $t, $cn );
        if ( count( $cn[0] ) < 3 ) return false;

        // 排除明显是指令/说明的句子（含典型英文指令词）
        $ban = [ 'chars', 'characters', 'let me', 'here are', 'make sure',
                 'count', 'carefully', 'between', 'titles:', 'output' ];
        $lower = strtolower( $t );
        foreach ( $ban as $b ) {
            if ( strpos( $lower, $b ) !== false ) return false;
        }

        return true;
    }
}
