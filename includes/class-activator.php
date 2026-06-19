<?php
namespace AutoSEO;

class Activator {
    public static function activate() {
        if ( false === get_option( 'autoseo_settings' ) ) {
            add_option( 'autoseo_settings', self::defaults() );
        }
        update_option( 'autoseo_activated_at', time() );
        flush_rewrite_rules();
    }

    public static function defaults() {
        return [
            'ai_provider'      => 'openai',   // openai | claude | deepseek | qwen | ernie | hunyuan | doubao
            'openai_key'       => '',
            'claude_key'       => '',
            'openai_model'     => 'gpt-4o-mini',
            'claude_model'     => 'claude-3-haiku-20240307',
            // 国产大模型
            'deepseek_key'     => '',
            'deepseek_model'   => 'deepseek-chat',
            'qwen_key'         => '',
            'qwen_model'       => 'qwen-plus',
            'ernie_key'        => '',
            'ernie_model'      => 'ernie-4.0-8k',
            'hunyuan_key'      => '',
            'hunyuan_model'    => 'hunyuan-standard',
            'doubao_key'       => '',
            'doubao_model'     => 'doubao-pro-32k',
            'ark_key'          => '',
            'ark_model'        => 'ep-xxxxxx',
            'glm_key'          => '',
            'glm_model'        => 'glm-4-flash',
            'kimi_key'         => '',
            'kimi_model'       => 'moonshot-v1-8k',
            'minimax_key'      => '',
            'minimax_model'    => 'MiniMax-Text-01',
            'spark_key'        => '',
            'spark_model'      => 'lite',
            'yi_key'           => '',
            'yi_model'         => 'yi-large',
            'stepfun_key'      => '',
            'stepfun_model'    => 'step-1-8k',
            'baichuan_key'     => '',
            'baichuan_model'   => 'Baichuan4',
            'google_api_key'      => '',
            'google_cx'           => '',
            'baidu_api_key'       => '',
            'baidu_zhanzhang_key' => '',
            'notify_email'        => get_option('admin_email'),
            'schema_type'      => 'Article',
            'sitemap_enabled'  => true,
            'breadcrumb_sep'   => ' › ',
            'title_sep'        => ' - ',
            'home_title'       => get_bloginfo('name'),
            'home_desc'           => '',
            'default_og_image'    => '',
            'noindex_archives' => false,
        ];
    }
}
