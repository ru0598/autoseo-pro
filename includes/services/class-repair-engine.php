<?php
namespace AutoSEO\Services;

class RepairEngine {

    private $ai;

    public function __construct() {
        $this->ai = new AIClient();
    }

    /**
     * 一键全量修复
     */
    public function repair_all( int $post_id ): array {
        $log = [];
        $log = array_merge( $log, $this->fix_h_tags( $post_id ) );
        $log = array_merge( $log, $this->fix_alt_tags( $post_id ) );
        $log = array_merge( $log, $this->fix_duplicate_content( $post_id ) );
        return $log;
    }

    // ── H 标签修复 ───────────────────────────────────────
    public function fix_h_tags( int $post_id ): array {
        $post    = get_post( $post_id );
        $content = $post->post_content;
        $log     = [];

        preg_match_all( '/<h1\b[^>]*>/i', $content, $m );
        $count = count( $m[0] );

        if ( $count > 1 ) {
            // 保留第一个 H1，其余降为 H2
            $first = true;
            $content = preg_replace_callback( '/<(h1)(\b[^>]*)>(.*?)<\/h1>/is', function( $matches ) use ( &$first ) {
                if ( $first ) { $first = false; return $matches[0]; }
                return '<h2' . $matches[2] . '>' . $matches[3] . '</h2>';
            }, $content );
            $log[] = "修复 H1 标签：将 {$count} 个 H1 中的多余 " . ( $count - 1 ) . " 个降级为 H2";
        } elseif ( $count === 0 ) {
            $log[] = '警告：未发现 H1 标签，请手动在文章中添加一个 H1';
        }

        if ( $content !== $post->post_content ) {
            wp_update_post( [ 'ID' => $post_id, 'post_content' => $content ] );
        }
        return $log;
    }

    // ── IMG ALT 修复 ──────────────────────────────────────
    public function fix_alt_tags( int $post_id ): array {
        $post    = get_post( $post_id );
        $content = $post->post_content;
        $log     = [];
        $fixed   = 0;

        $content = preg_replace_callback(
            '/<img\b([^>]*)>/i',
            function( $m ) use ( $post_id, &$fixed ) {
                $tag = $m[0];
                // 已有非空 alt 则跳过
                if ( preg_match( '/\balt\s*=\s*["\'][^"\']+["\']/i', $tag ) ) return $tag;

                // 尝试从 title 或 src 提取关键词
                $alt = '';
                if ( preg_match( '/\btitle\s*=\s*["\']([^"\']+)["\']/i', $tag, $t ) ) {
                    $alt = $t[1];
                } elseif ( preg_match( '/\bsrc\s*=\s*["\'][^"\']*\/([^"\'\/]+)\.[a-z]{2,4}["\']/i', $tag, $s ) ) {
                    $alt = str_replace( [ '-', '_' ], ' ', $s[1] );
                }
                if ( empty( $alt ) ) $alt = get_the_title( $post_id ) . ' 相关图片';

                $fixed++;
                // 插入 alt 属性
                if ( preg_match( '/\balt\s*=\s*["\']["\']/', $tag ) ) {
                    return preg_replace( '/\balt\s*=\s*["\']["\']/', 'alt="' . esc_attr( $alt ) . '"', $tag );
                }
                return str_replace( '<img', '<img alt="' . esc_attr( $alt ) . '"', $tag );
            },
            $content
        );

        if ( $fixed ) {
            wp_update_post( [ 'ID' => $post_id, 'post_content' => $content ] );
            $log[] = "修复 ALT 标签：为 {$fixed} 张图片补充了 ALT 属性";
        } else {
            $log[] = 'ALT 标签检查：所有图片均已有 ALT 属性';
        }
        return $log;
    }

    // ── AI 重复内容检测与改写 ──────────────────────────────
    public function fix_duplicate_content( int $post_id ): array {
        $post    = get_post( $post_id );
        $content = wp_strip_all_tags( $post->post_content );
        if ( mb_strlen( $content ) < 200 ) return [ '内容过短，跳过重复检测' ];

        $prompt = "以下段落是否存在大量重复表达或冗余句子？如果有，请精简重写后输出；如果没有，只回复\"NO_CHANGE\"。\n\n" . mb_substr( $content, 0, 1200 );
        $result = $this->ai->ask( $prompt, 800 );
        if ( is_wp_error( $result ) ) return [ '重复内容检测：AI 调用失败 - ' . $result->get_error_message() ];
        if ( trim( $result ) === 'NO_CHANGE' ) return [ '重复内容检测：未发现明显冗余' ];

        return [ 'AI 重复内容检测完成，建议手动审阅 AI 改写建议后更新正文' ];
    }
}
