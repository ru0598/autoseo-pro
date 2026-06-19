<?php
namespace AutoSEO\Services;

class SEOAnalyzer {

    public function analyze( int $post_id ): array {
        $post    = get_post( $post_id );
        $content = $post->post_content;
        $text    = wp_strip_all_tags( $content );
        $title   = $post->post_title;
        $issues  = [];
        $score   = 100;

        // 标题长度
        $tlen = mb_strlen( $title );
        if ( $tlen < 10 )       { $issues[] = [ 'level' => 'error',   'msg' => '标题过短（< 10 字符）' ]; $score -= 15; }
        elseif ( $tlen > 70 )   { $issues[] = [ 'level' => 'warning', 'msg' => '标题过长（> 70 字符），搜索引擎可能截断' ]; $score -= 8; }

        // 元描述
        $meta = get_post_meta( $post_id, '_autoseo_meta_desc', true );
        if ( empty( $meta ) )   { $issues[] = [ 'level' => 'error',   'msg' => '缺少元描述（Meta Description）' ]; $score -= 20; }
        elseif ( mb_strlen( $meta ) < 70 )  { $issues[] = [ 'level' => 'warning', 'msg' => '元描述过短，建议 120-160 字符' ]; $score -= 5; }
        elseif ( mb_strlen( $meta ) > 160 ) { $issues[] = [ 'level' => 'warning', 'msg' => '元描述超过 160 字符，搜索引擎可能截断' ]; $score -= 5; }

        // H1 标签
        preg_match_all( '/<h1\b[^>]*>/i', $content, $h1 );
        $h1c = count( $h1[0] );
        if ( $h1c === 0 )       { $issues[] = [ 'level' => 'error',   'msg' => '正文无 H1 标签' ]; $score -= 15; }
        elseif ( $h1c > 1 )     { $issues[] = [ 'level' => 'warning', 'msg' => "存在 {$h1c} 个 H1 标签，建议只保留一个" ]; $score -= 10; }

        // 图片 ALT
        preg_match_all( '/<img\b[^>]*>/i', $content, $imgs );
        $no_alt = 0;
        foreach ( $imgs[0] as $img ) {
            if ( ! preg_match( '/\balt\s*=\s*["\'][^"\']+["\']/i', $img ) ) $no_alt++;
        }
        if ( $no_alt > 0 )      { $issues[] = [ 'level' => 'warning', 'msg' => "{$no_alt} 张图片缺少 ALT 属性" ]; $score -= min( $no_alt * 4, 16 ); }

        // 内容字数：中文按汉字计数，英文按单词计数，去掉空白和标点
        preg_match_all( '/[\x{4e00}-\x{9fff}\x{3400}-\x{4dbf}\x{f900}-\x{faff}]/u', $text, $cn_chars );
        $cn_count = count( $cn_chars[0] );
        // 英文单词数（连续英文字符串）
        preg_match_all( '/[a-zA-Z]+/', $text, $en_words );
        $en_count = count( $en_words[0] );
        $wc = $cn_count + $en_count;
        if ( $wc < 300 )        { $issues[] = [ 'level' => 'error',   'msg' => "正文字数 {$wc}，建议 ≥ 300 字" ]; $score -= 15; }
        elseif ( $wc < 600 )    { $issues[] = [ 'level' => 'warning', 'msg' => "正文字数 {$wc}，建议 ≥ 600 字" ]; $score -= 8; }

        // 关键词密度
        $focus_kw = get_post_meta( $post_id, '_autoseo_focus_kw', true );
        if ( $focus_kw && $wc > 0 ) {
            $kw_count = mb_substr_count( $text, $focus_kw );
            $density  = round( ( $kw_count / max( $wc, 1 ) ) * 100, 2 );
            if ( $density < 0.5 )   { $issues[] = [ 'level' => 'warning', 'msg' => "关键词「{$focus_kw}」密度 {$density}%，偏低" ]; $score -= 8; }
            elseif ( $density > 3 ) { $issues[] = [ 'level' => 'error',   'msg' => "关键词「{$focus_kw}」密度 {$density}%，过高（堆砌）" ]; $score -= 10; }
        }

        // 内部链接
        preg_match_all( '/<a\b[^>]*href=["\'][^"\']+["\'][^>]*>/i', $content, $links );
        if ( count( $links[0] ) === 0 ) {
            $issues[] = [ 'level' => 'info', 'msg' => '正文无内部链接，建议添加相关文章链接' ];
            $score -= 5;
        }

        if ( empty( $issues ) ) {
            $issues[] = [ 'level' => 'success', 'msg' => '未发现明显 SEO 问题，继续保持！' ];
        }

        return [ 'score' => max( 0, $score ), 'issues' => $issues, 'word_count' => $wc ];
    }
}
