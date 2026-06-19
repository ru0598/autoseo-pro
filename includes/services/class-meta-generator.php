<?php
namespace AutoSEO\Services;

class MetaGenerator {

    private $ai;

    public function __construct() {
        $this->ai = new AIClient();
    }

    /**
     * 生成 Meta Description + 社交分享文案
     * @return array{ meta: string, social: string }|WP_Error
     */
    public function generate( int $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post || empty( trim( $post->post_content ) ) ) {
            return new \WP_Error( 'no_content', '文章内容为空，请先写入正文内容再生成摘要' );
        }
        $content = wp_strip_all_tags( $post->post_content );
        $excerpt = mb_substr( $content, 0, 800 );

        $prompt = <<<PROMPT
你是一位 SEO 和社交媒体专家。根据以下文章内容生成两段文字：

1. Meta Description（120-155 字符），面向搜索引擎，包含核心关键词，有行动号召。
2. 社交媒体分享文案（微博/微信风格，80-120 字），有话题标签，更口语化，带 emoji。

严格按以下格式输出，不要多余文字：
META: <meta description 内容>
SOCIAL: <社交分享文案内容>

文章内容摘要：
{$excerpt}
PROMPT;

        $result = $this->ai->ask( $prompt, 400 );
        if ( is_wp_error( $result ) ) return $result;

        $meta = $social = '';
        foreach ( explode( "\n", $result ) as $line ) {
            if ( strncmp( $line, 'META:', 5 ) === 0 ) {
                $meta = trim( substr( $line, 5 ) );
            } elseif ( strncmp( $line, 'SOCIAL:', 7 ) === 0 ) {
                $social = trim( substr( $line, 7 ) );
            }
        }

        // 回退：截取正文前 150 字符
        if ( empty( $meta ) ) {
            $meta = mb_substr( wp_strip_all_tags( $content ), 0, 150 ) . '...';
        }

        return compact( 'meta', 'social' );
    }

    /**
     * 将生成结果保存到 postmeta
     */
    public function save( int $post_id, array $data ) {
        update_post_meta( $post_id, '_autoseo_meta_desc',    $data['meta']   ?? '' );
        update_post_meta( $post_id, '_autoseo_social_copy',  $data['social'] ?? '' );
    }
}
