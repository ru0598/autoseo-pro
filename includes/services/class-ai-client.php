<?php
namespace AutoSEO\Services;

/**
 * AI 统一客户端
 * 支持：OpenAI、Claude、DeepSeek、通义千问、文心一言、混元、豆包、
 *       火山方舟、GLM（智谱）、Kimi（月之暗面）、MiniMax、Grok
 */
class AIClient {

    private $settings;

    /**
     * OpenAI 兼容通道的 provider 配置表
     * key_field : 存储密钥的 option 字段名
     * reasoner_pattern : 正则，命中则视为推理模型（自动加大 max_tokens）
     */
    private static $compat_providers = [

        // ── 海外 ──────────────────────────────────────────────────────────
        'openai' => [
            'label'    => 'OpenAI',
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
            'default'  => 'gpt-4o-mini',
            'models'   => [ 'gpt-4o', 'gpt-4o-mini', 'gpt-4-turbo', 'gpt-3.5-turbo', 'o1', 'o1-mini', 'o3-mini' ],
            'key_field'=> 'openai_key',
            'doc'      => 'https://platform.openai.com/api-keys',
            'tag'      => '海外',
        ],

        // ── 国产 ──────────────────────────────────────────────────────────
        'deepseek' => [
            'label'    => 'DeepSeek',
            'endpoint' => 'https://api.deepseek.com/v1/chat/completions',
            'default'  => 'deepseek-chat',
            'models'   => [ 'deepseek-chat', 'deepseek-reasoner', 'deepseek-v3', 'deepseek-r1' ],
            'key_field'=> 'deepseek_key',
            'doc'      => 'https://platform.deepseek.com/api_keys',
            'tag'      => '国产',
        ],
        'qwen' => [
            'label'    => '通义千问（阿里云）',
            'endpoint' => 'https://dashscope.aliyuncs.com/compatible-mode/v1/chat/completions',
            'default'  => 'qwen-plus',
            'models'   => [ 'qwen-turbo', 'qwen-plus', 'qwen-max', 'qwen-long',
                            'qwen2.5-72b-instruct', 'qwen2.5-coder-32b-instruct', 'qwq-32b' ],
            'key_field'=> 'qwen_key',
            'doc'      => 'https://dashscope.console.aliyun.com/apiKey',
            'tag'      => '国产',
        ],
        'ernie' => [
            'label'    => '文心一言（百度）',
            'endpoint' => 'https://qianfan.baidubce.com/v2/chat/completions',
            'default'  => 'ernie-4.0-8k',
            'models'   => [ 'ernie-4.0-8k', 'ernie-4.0-turbo-8k', 'ernie-3.5-8k',
                            'ernie-lite-8k', 'ernie-speed-128k', 'ernie-x1' ],
            'key_field'=> 'ernie_key',
            'doc'      => 'https://console.bce.baidu.com/iam/#/iam/accesslist',
            'tag'      => '国产',
        ],
        'hunyuan' => [
            'label'    => '混元（腾讯云）',
            'endpoint' => 'https://api.hunyuan.cloud.tencent.com/v1/chat/completions',
            'default'  => 'hunyuan-standard',
            'models'   => [ 'hunyuan-standard', 'hunyuan-pro', 'hunyuan-lite',
                            'hunyuan-turbo', 'hunyuan-turbos', 'hunyuan-t1' ],
            'key_field'=> 'hunyuan_key',
            'doc'      => 'https://console.cloud.tencent.com/hunyuan/api-key',
            'tag'      => '国产',
        ],
        'doubao' => [
            'label'    => '豆包（字节跳动）',
            'endpoint' => 'https://ark.cn-beijing.volces.com/api/v3/chat/completions',
            'default'  => 'doubao-pro-32k',
            'models'   => [ 'doubao-pro-256k', 'doubao-pro-32k', 'doubao-pro-4k',
                            'doubao-lite-32k', 'doubao-lite-4k', 'doubao-1-5-pro-32k' ],
            'key_field'=> 'doubao_key',
            'doc'      => 'https://console.volcengine.com/ark/region:ark+cn-beijing/apiKey',
            'tag'      => '国产',
        ],
        'ark' => [
            'label'    => '火山方舟（字节）',
            'endpoint' => 'https://ark.cn-beijing.volces.com/api/coding/v3/chat/completions',
            'default'  => 'doubao-pro-32k-240828',
            'models'   => [ 'doubao-pro-32k-240828', 'doubao-1-5-pro-32k-250115',
                            'doubao-lite-32k-240828', 'deepseek-v3-241226',
                            'deepseek-r1-250120', 'moonshot-v1-8k' ],
            'key_field'=> 'ark_key',
            'doc'      => 'https://ark.cn-beijing.volces.com/api/coding',
            'tag'      => '国产',
            'note'     => '使用兼容 OpenAI 接口协议，Endpoint: ark.cn-beijing.volces.com/api/coding/v3',
        ],
        'glm' => [
            'label'    => 'GLM（智谱 AI）',
            'endpoint' => 'https://open.bigmodel.cn/api/paas/v4/chat/completions',
            'default'  => 'glm-4-flash',
            'models'   => [ 'glm-4-flash', 'glm-4-air', 'glm-4-airx', 'glm-4',
                            'glm-4-plus', 'glm-4-long', 'glm-z1-flash', 'codegeex-4' ],
            'key_field'=> 'glm_key',
            'doc'      => 'https://bigmodel.cn/usercenter/proj-mgmt/apikeys',
            'tag'      => '国产',
        ],
        'kimi' => [
            'label'    => 'Kimi（月之暗面）',
            'endpoint' => 'https://api.moonshot.cn/v1/chat/completions',
            'default'  => 'moonshot-v1-8k',
            'models'   => [ 'moonshot-v1-8k', 'moonshot-v1-32k', 'moonshot-v1-128k',
                            'kimi-latest', 'kimi-thinking-preview' ],
            'key_field'=> 'kimi_key',
            'doc'      => 'https://platform.moonshot.cn/console/api-keys',
            'tag'      => '国产',
        ],
        'minimax' => [
            'label'    => 'MiniMax',
            'endpoint' => 'https://api.minimax.chat/v1/text/chatcompletion_v2',
            'default'  => 'MiniMax-Text-01',
            'models'   => [ 'MiniMax-Text-01', 'abab6.5s-chat', 'abab6.5g-chat',
                            'abab5.5s-chat', 'MiniMax-M1' ],
            'key_field'=> 'minimax_key',
            'doc'      => 'https://platform.minimaxi.com/user-center/basic-information/interface-key',
            'tag'      => '国产',
        ],
        'spark' => [
            'label'    => '讯飞星火',
            'endpoint' => 'https://spark-api-open.xf-yun.com/v1/chat/completions',
            'default'  => 'lite',
            'models'   => [ 'lite', 'generalv3', 'pro-128k', 'generalv3.5', 'max-32k', '4.0Ultra' ],
            'key_field'=> 'spark_key',
            'doc'      => 'https://console.xfyun.cn/services/bm35',
            'tag'      => '国产',
        ],
        'yi' => [
            'label'    => '零一万物（Yi）',
            'endpoint' => 'https://api.lingyiwanwu.com/v1/chat/completions',
            'default'  => 'yi-large',
            'models'   => [ 'yi-large', 'yi-medium', 'yi-spark', 'yi-large-turbo',
                            'yi-large-rag', 'yi-large-fc' ],
            'key_field'=> 'yi_key',
            'doc'      => 'https://platform.lingyiwanwu.com/apikeys',
            'tag'      => '国产',
        ],
        'stepfun' => [
            'label'    => '阶跃星辰（Step）',
            'endpoint' => 'https://api.stepfun.com/v1/chat/completions',
            'default'  => 'step-1-8k',
            'models'   => [ 'step-1-8k', 'step-1-32k', 'step-1-128k', 'step-1-256k',
                            'step-2-16k', 'step-1-flash' ],
            'key_field'=> 'stepfun_key',
            'doc'      => 'https://platform.stepfun.com/account-info',
            'tag'      => '国产',
        ],
        'baichuan' => [
            'label'    => '百川智能',
            'endpoint' => 'https://api.baichuan-ai.com/v1/chat/completions',
            'default'  => 'Baichuan4',
            'models'   => [ 'Baichuan4', 'Baichuan3-Turbo', 'Baichuan3-Turbo-128k',
                            'Baichuan2-Turbo', 'Baichuan2-Turbo-192k' ],
            'key_field'=> 'baichuan_key',
            'doc'      => 'https://platform.baichuan-ai.com/console/apikey',
            'tag'      => '国产',
        ],
    ];

    public function __construct() {
        $this->settings = get_option( 'autoseo_settings', [] );
    }

    /**
     * 获取所有 provider 配置（供 Settings 页使用）
     */
    public static function get_compat_providers(): array {
        return self::$compat_providers;
    }

    /**
     * 向 AI 发送请求，返回文字结果或 WP_Error
     */
    public function ask( string $prompt, int $max_tokens = 400, string $system = '' ) {
        $provider = $this->settings['ai_provider'] ?? 'openai';

        if ( $provider === 'claude' ) {
            return $this->ask_claude( $prompt, $max_tokens, $system );
        }

        if ( isset( self::$compat_providers[ $provider ] ) ) {
            return $this->ask_compat( $provider, $prompt, $max_tokens, $system );
        }

        return $this->ask_compat( 'openai', $prompt, $max_tokens, $system );
    }


    // ── Anthropic Claude（独立协议）──────────────────────────────────────────
    private function ask_claude( string $prompt, int $max_tokens, string $system = '' ) {
        $key   = $this->settings['claude_key'] ?? '';
        $model = $this->settings['claude_model'] ?? 'claude-3-haiku-20240307';

        if ( empty( $key ) ) {
            return new \WP_Error( 'no_key', 'Claude 密钥未配置，请前往「AI 配置」填写' );
        }

        $resp = wp_remote_post( 'https://api.anthropic.com/v1/messages', [
            'timeout' => 30,
            'headers' => [
                'x-api-key'         => $key,
                'anthropic-version' => '2023-06-01',
                'Content-Type'      => 'application/json',
            ],
            'body' => wp_json_encode( array_filter( [
                'model'      => $model,
                'max_tokens' => $max_tokens,
                'system'     => $system ?: null,
                'messages'   => [ [ 'role' => 'user', 'content' => $prompt ] ],
            ] ) ),
        ] );

        if ( is_wp_error( $resp ) ) return $resp;

        $body = json_decode( wp_remote_retrieve_body( $resp ), true );
        if ( ! empty( $body['error'] ) ) {
            return new \WP_Error( 'claude_error', $body['error']['message'] ?? '未知错误' );
        }
        return trim( $body['content'][0]['text'] ?? '' );
    }

    // ── 国产模型 / OpenAI 兼容通道 ────────────────────────────────────────────
    private function ask_compat( string $provider, string $prompt, int $max_tokens, string $system = '' ) {
        $cfg       = self::$compat_providers[ $provider ];
        $key_field = $cfg['key_field'];
        $key       = $this->settings[ $key_field ] ?? '';
        $model     = $this->settings[ $provider . '_model' ] ?? $cfg['default'];

        if ( empty( $key ) ) {
            return new \WP_Error( 'no_key', $cfg['label'] . ' 密钥未配置，请前往「AI 配置」填写' );
        }

        return $this->call_openai_compat( $cfg['endpoint'], $key, $model, $prompt, $max_tokens, $system );
    }

    // ── 通用 OpenAI Chat Completions 调用 ─────────────────────────────────────
    private function call_openai_compat( string $endpoint, string $key, string $model, string $prompt, int $max_tokens, string $system = '' ) {
        // 推理模型（DeepSeek R1/v4-pro/Reasoner、QwQ 等）需要更大的 token 空间
        $is_reasoner = (bool) preg_match( '/reasoner|\br1\b|qwq|v\d+-pro/i', $model );
        if ( $is_reasoner ) {
            $max_tokens = max( $max_tokens, 4000 );
        }

        $resp = wp_remote_post( $endpoint, [
            'timeout' => 60,
            'headers' => [
                'Authorization' => 'Bearer ' . $key,
                'Content-Type'  => 'application/json',
            ],
            'body' => wp_json_encode( [
                'model'       => $model,
                'messages'    => array_merge(
                    $system ? [ [ 'role' => 'system', 'content' => $system ] ] : [],
                    [ [ 'role' => 'user', 'content' => $prompt ] ]
                ),
                'max_tokens'  => $max_tokens,
                'temperature' => $is_reasoner ? 1.0 : 0.7,
            ] ),
        ] );

        if ( is_wp_error( $resp ) ) return $resp;

        $code = wp_remote_retrieve_response_code( $resp );
        $body = json_decode( wp_remote_retrieve_body( $resp ), true );

        if ( ! empty( $body['error'] ) ) {
            $msg = $body['error']['message'] ?? $body['error']['msg'] ?? '未知错误';
            return new \WP_Error( 'ai_error', $msg );
        }
        if ( (int) $code >= 400 ) {
            return new \WP_Error( 'ai_http_error', "接口返回 HTTP {$code}，请检查密钥是否正确" );
        }

        $msg = $body['choices'][0]['message'] ?? [];

        // 取模型最终回答（content 字段）
        // 注意：reasoning_content 是内部推理链，不能当作输出使用
        $content = trim( $msg['content'] ?? '' );

        if ( $content === '' ) {
            return new \WP_Error( 'ai_empty', 'AI 返回内容为空，请检查模型名称是否正确，或适当增大 max_tokens' );
        }

        return $content;
    }
}
