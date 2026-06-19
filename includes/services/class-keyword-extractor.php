<?php
namespace AutoSEO\Services;

class KeywordExtractor {

    private $ai;

    public function __construct() {
        $this->ai = new AIClient();
    }

    /**
     * 从文章正文提取最佳聚焦关键词
     * @return string|\WP_Error
     */
    public function extract( int $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post || empty( trim( $post->post_content ) ) ) {
            return new \WP_Error( 'no_content', '文章内容为空，请先写入正文再提取关键词' );
        }

        $content = wp_strip_all_tags( $post->post_content );
        $excerpt = mb_substr( $content, 0, 800 );
        $title   = $post->post_title;

        $system = '你是专业的中文 SEO 关键词分析师。只输出一个关键词或短语，不输出任何其他内容。';

        $user = "文章标题：{$title}\n文章摘要：\n{$excerpt}\n\n"
              . "请提取这篇文章最核心的 SEO 聚焦关键词（1个，2-8个汉字，用户最可能搜索的词）。\n"
              . "直接输出关键词本身，不加任何说明、标点或引号。";

        $result = $this->ai->ask( $user, 50, $system );
        if ( is_wp_error( $result ) ) return $result;

        // 清理：去掉引号、标点、多余空白
        $kw = trim( $result );
        $kw = trim( $kw, '"""\'\'\'。，、：:' );
        $kw = preg_replace( '/\s+/', '', $kw );

        // 只取第一行（防止模型输出多行）
        $lines = preg_split( '/[\n\r]+/', $kw );
        $kw = trim( $lines[0] );

        if ( empty( $kw ) ) {
            return new \WP_Error( 'empty_result', 'AI 未能提取关键词，请手动填写' );
        }

        return $kw;
    }
}
