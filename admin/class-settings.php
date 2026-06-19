<?php
namespace AutoSEO\Admin;

class Settings {

    private static function providers(): array {
        return [
            'claude'   => ['label'=>'Claude',   'meta'=>'Anthropic',        'mark'=>'C',  'color'=>'#D97757','key_field'=>'claude_key',   'model_field'=>'claude_model',   'default'=>'claude-3-haiku-20240307','models'=>['claude-opus-4-5','claude-sonnet-4-5','claude-3-5-sonnet-20241022','claude-3-haiku-20240307'],'doc'=>'console.anthropic.com'],
            'openai'   => ['label'=>'OpenAI',   'meta'=>'GPT-4o / GPT-4 Turbo','mark'=>'O','color'=>'#10A37F','key_field'=>'openai_key',  'model_field'=>'openai_model',   'default'=>'gpt-4o-mini','models'=>['gpt-4o','gpt-4o-mini','gpt-4-turbo','gpt-3.5-turbo'],'doc'=>'platform.openai.com'],
            'deepseek' => ['label'=>'DeepSeek', 'meta'=>'深度求索',         'mark'=>'D',  'color'=>'#4D6BFE','key_field'=>'deepseek_key', 'model_field'=>'deepseek_model', 'default'=>'deepseek-chat','models'=>['deepseek-chat','deepseek-reasoner','deepseek-coder'],'doc'=>'platform.deepseek.com'],
            'qwen'     => ['label'=>'通义千问', 'meta'=>'阿里云',           'mark'=>'通', 'color'=>'#FF6A00','key_field'=>'qwen_key',     'model_field'=>'qwen_model',     'default'=>'qwen-turbo','models'=>['qwen-turbo','qwen-plus','qwen-max','qwen-long'],'doc'=>'dashscope.aliyuncs.com'],
            'ernie'    => ['label'=>'文心一言', 'meta'=>'百度',             'mark'=>'文', 'color'=>'#1E88E5','key_field'=>'ernie_key',    'model_field'=>'ernie_model',    'default'=>'ernie-4.0-8k','models'=>['ernie-4.0-8k','ernie-3.5-8k','ernie-speed-8k'],'doc'=>'console.bce.baidu.com'],
            'glm'      => ['label'=>'智谱',     'meta'=>'GLM-4',            'mark'=>'智', 'color'=>'#3859FF','key_field'=>'glm_key',      'model_field'=>'glm_model',      'default'=>'glm-4-flash','models'=>['glm-4-flash','glm-4','glm-4-air','glm-4-long'],'doc'=>'open.bigmodel.cn'],
            'doubao'   => ['label'=>'豆包',     'meta'=>'字节跳动',         'mark'=>'豆', 'color'=>'#1F1F1F','key_field'=>'doubao_key',   'model_field'=>'doubao_model',   'default'=>'doubao-pro-32k','models'=>['doubao-pro-32k','doubao-lite-32k'],'doc'=>'console.volcengine.com'],
            'hunyuan'  => ['label'=>'混元',     'meta'=>'腾讯云',           'mark'=>'混', 'color'=>'#006EFF','key_field'=>'hunyuan_key',  'model_field'=>'hunyuan_model',  'default'=>'hunyuan-pro','models'=>['hunyuan-pro','hunyuan-standard','hunyuan-lite'],'doc'=>'cloud.tencent.com/product/hunyuan'],
            'ark'      => ['label'=>'火山方舟', 'meta'=>'字节跳动',         'mark'=>'火', 'color'=>'#2E5BFF','key_field'=>'ark_key',      'model_field'=>'ark_model',      'default'=>'doubao-pro-32k-240828','models'=>['doubao-pro-32k-240828','doubao-1-5-pro-32k-250115','doubao-lite-32k-240828','deepseek-v3-241226','deepseek-r1-250120'],'doc'=>'ark.cn-beijing.volces.com/api/coding'],
            'kimi'     => ['label'=>'Kimi',     'meta'=>'月之暗面',         'mark'=>'K',  'color'=>'#1A1A1A','key_field'=>'kimi_key',     'model_field'=>'kimi_model',     'default'=>'moonshot-v1-8k','models'=>['moonshot-v1-8k','moonshot-v1-32k','moonshot-v1-128k'],'doc'=>'platform.moonshot.cn'],
            'minimax'  => ['label'=>'MiniMax',  'meta'=>'MiniMax',          'mark'=>'M',  'color'=>'linear-gradient(135deg,#FFB800 0%,#FF6A00 100%)','key_field'=>'minimax_key','model_field'=>'minimax_model','default'=>'MiniMax-Text-01','models'=>['MiniMax-Text-01','MiniMax-M1','abab6.5s-chat','abab6.5-chat'],'doc'=>'api.minimax.chat'],
            'spark'    => ['label'=>'讯飞星火', 'meta'=>'科大讯飞',         'mark'=>'讯', 'color'=>'#00B0FF','key_field'=>'spark_key',    'model_field'=>'spark_model',    'default'=>'spark-max','models'=>['spark-max','spark-pro','spark-lite'],'doc'=>'xinghuo.xfyun.cn'],
            'baichuan' => ['label'=>'百川',     'meta'=>'百川智能',         'mark'=>'百', 'color'=>'#C62828','key_field'=>'baichuan_key', 'model_field'=>'baichuan_model', 'default'=>'Baichuan4','models'=>['Baichuan4','Baichuan3-Turbo','Baichuan3-Turbo-128k'],'doc'=>'platform.baichuan-ai.com'],
            'yi'       => ['label'=>'零一万物', 'meta'=>'01.AI',            'mark'=>'零', 'color'=>'#333333','key_field'=>'yi_key',       'model_field'=>'yi_model',       'default'=>'yi-large','models'=>['yi-large','yi-medium','yi-spark'],'doc'=>'platform.lingyiwanwu.com'],
            'stepfun'  => ['label'=>'阶跃星辰', 'meta'=>'StepFun',          'mark'=>'阶', 'color'=>'#5B21B6','key_field'=>'stepfun_key',  'model_field'=>'stepfun_model',  'default'=>'step-1','models'=>['step-1','step-1-flash','step-2'],'doc'=>'platform.stepfun.com'],
        ];
    }

    public function __construct() {
        add_action( 'admin_init', [ $this, 'register' ] );
    }

    public function register() {
        register_setting( 'autoseo_settings_group', 'autoseo_settings', [
            'sanitize_callback' => [ $this, 'sanitize' ],
        ] );
    }

    public function sanitize( $input ): array {
        $defaults = \AutoSEO\Activator::defaults();
        $clean = $defaults;
        foreach ( $defaults as $k => $default ) {
            if ( ! isset( $input[$k] ) ) continue;
            $clean[$k] = is_bool($default) ? (bool)$input[$k] : sanitize_textarea_field((string)$input[$k]);
        }
        return $clean;
    }

    public function render() {
        $s         = get_option( 'autoseo_settings', \AutoSEO\Activator::defaults() );
        $provider  = $s['ai_provider'] ?? 'deepseek';
        $providers = self::providers();
        $saved     = isset( $_GET['settings-updated'] );
        $GLOBALS['_autoseo_provider'] = $provider;
        $this->render_wrap_open( $saved );
        $this->render_section_ai( $s, $provider, $providers );
        $this->render_section_search( $s );
        $this->render_section_seo( $s );
        $this->render_save_bar( $saved );
        $this->render_wrap_close( $providers );
    }

    private function render_wrap_open( bool $saved ) { ?>
        <div id="autoseo-wrap"><div id="autoseo-settings-page">
        <?php if ($saved): ?>
        <div class="notice notice-success is-dismissible"><p><strong>设置已保存。</strong></p></div>
        <?php endif; ?>
        <form method="post" action="options.php">
        <?php settings_fields('autoseo_settings_group'); ?>
        <input type="hidden" name="autoseo_settings[ai_provider]" id="st-provider-val" value="<?php echo esc_attr($GLOBALS['_autoseo_provider']??'deepseek'); ?>" />
        <header class="st-page-header">
          <div class="st-page-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9"/></svg></div>
          <div>
            <h1 class="st-page-title">AutoSEO Pro 设置</h1>
            <p class="st-page-subtitle">管理 AI 引擎、搜索引擎连接与 SEO 全局选项</p>
          </div>
        </header>
    <?php }
    private function render_section_ai( array $s, string $provider, array $providers ) {
        $GLOBALS['_autoseo_provider'] = $provider;
        ?>
        <section class="st-section">
          <div class="st-section-head">
            <h2 class="st-section-title">AI 引擎配置</h2>
            <p class="st-section-desc">选择用于生成标题建议与摘要的 AI 服务商。填入 API 密钥后即可使用。</p>
          </div>
          <div class="st-section-body">
            <div class="engine-grid">
              <?php foreach ($providers as $pkey => $p):
                $sel = $provider===$pkey?' selected':'';
              ?>
              <button type="button" class="engine-card<?php echo $sel; ?>" data-provider="<?php echo esc_attr($pkey); ?>">
                <div class="engine-mark" style="background:<?php echo esc_attr($p['color']); ?>"><?php echo esc_html($p['mark']); ?></div>
                <div class="engine-text">
                  <div class="engine-name"><?php echo esc_html($p['label']); ?></div>
                  <div class="engine-meta"><?php echo esc_html($p['meta']); ?></div>
                </div>
                <div class="engine-check"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
              </button>
              <?php endforeach; ?>
            </div>
            <?php foreach ($providers as $pkey => $p):
              $kv  = esc_attr($s[$p['key_field']]??'');
              $mv  = esc_attr($s[$p['model_field']]??$p['default']);
              $show = $provider===$pkey?'':'display:none';
              $has = !empty($s[$p['key_field']]);
            ?>
            <div class="sub-form" id="st-panel-<?php echo $pkey; ?>" style="<?php echo $show; ?>">
              <div>
                <div class="field-label-row">
                  <label class="field-label" for="st-key-<?php echo $pkey; ?>"><?php echo esc_html($p['label']); ?> API 密钥</label>
                  <?php if($has):?><span class="pill-status">已配置</span><?php endif;?>
                </div>
                <input class="st-input mono<?php echo $has?' is-saved':''; ?>" type="password" id="st-key-<?php echo $pkey; ?>"
                  name="autoseo_settings[<?php echo esc_attr($p['key_field']);?>]"
                  value="<?php echo $kv;?>" placeholder="粘贴 API 密钥…" autocomplete="new-password" />
                <p class="st-field-hint">获取地址：<a href="https://<?php echo esc_attr($p['doc']);?>" target="_blank"><?php echo esc_html($p['doc']);?></a></p>
              </div>
              <div>
                <label class="field-label" for="st-model-<?php echo $pkey; ?>">模型</label>
                <div class="st-model-wrap">
                  <input class="st-model-input" type="text" id="st-model-<?php echo $pkey; ?>"
                    list="st-list-<?php echo $pkey; ?>"
                    name="autoseo_settings[<?php echo esc_attr($p['model_field']);?>]"
                    value="<?php echo $mv;?>" placeholder="输入或选择模型…" />
                  <datalist id="st-list-<?php echo $pkey; ?>"><?php foreach($p['models'] as $m) echo '<option value="'.esc_attr($m).'">'; ?></datalist>
                </div>
                <p class="st-field-hint">可从列表选择，也可直接输入自定义模型名</p>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </section>
    <?php }
    private function render_section_search( array $s ) { ?>
        <section class="st-section">
          <div class="st-section-head">
            <h2 class="st-section-title">搜索引擎设置</h2>
            <p class="st-section-desc">配置用于查询关键词排名与站点收录数据的 API 凭证。</p>
          </div>
          <div class="st-section-body">
            <div class="st-field">
              <label class="st-field-label" for="st-baidu-zz">百度站长链接提交 Token</label>
              <div>
                <?php $__bz = $s['baidu_zhanzhang_key']??''; ?>
                <input class="st-input mono<?php echo $__bz?' is-saved':''; ?>" type="password" id="st-baidu-zz" name="autoseo_settings[baidu_zhanzhang_key]" value="<?php echo esc_attr($__bz); ?>" placeholder="xxxxxxxxxxxxxxxxxxxx" autocomplete="new-password" />
                <p class="st-field-hint">填入接口 URL 中 <code>token=</code> 后面的值。例如地址为 <code>http://data.zz.baidu.com/urls?site=xxx&amp;token=<strong>HKL5b9Ja...</strong></code>，只填 <code>HKL5b9Ja...</code> 这一段。</p>
              </div>
            </div>
            <div class="st-field">
              <label class="st-field-label" for="st-google-cx">谷歌搜索引擎 ID (CX)</label>
              <div>
                <?php $__cx = $s['google_cx']??''; ?>
                <input class="st-input mono<?php echo $__cx?' is-saved':''; ?>" type="text" id="st-google-cx" name="autoseo_settings[google_cx]" value="<?php echo esc_attr($__cx); ?>" placeholder="例如：u7d3j0x1y2z" />
                <p class="st-field-hint">即搜索引擎 ID，格式如 <code>a1b2c3d4e5f6g7h8i</code>。前往 <a href="https://programmablesearchengine.google.com" target="_blank">Google 可编程搜索</a> 创建针对你站点的搜索引擎，在控制面板复制「搜索引擎 ID」即可。</p>
              </div>
            </div>
            <div class="st-field">
              <label class="st-field-label" for="st-google-key">谷歌 API 密钥</label>
              <div>
                <?php $__gk = $s['google_api_key']??''; ?>
                <input class="st-input mono<?php echo $__gk?' is-saved':''; ?>" type="password" id="st-google-key" name="autoseo_settings[google_api_key]" value="<?php echo esc_attr($__gk); ?>" placeholder="••••••••" autocomplete="new-password" />
                <p class="st-field-hint">格式为 <code>AIzaSy...</code> 的字符串，不是 JSON 文件。前往 <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Google Cloud 凭据页</a> → 创建凭据 → API 密钥。</p>
              </div>
            </div>
            <div class="st-field">
              <label class="st-field-label" for="st-notify-email">告警通知邮符1</label>
              <div>
                <input class="st-input" type="email" id="st-notify-email" name="autoseo_settings[notify_email]" value="<?php echo esc_attr($s['notify_email']??get_option('admin_email')); ?>" />
                <p class="st-field-hint">接收排名波动（≥ 3 位）告警与抓取异常通知的邮符1地址。</p>
              </div>
            </div>
          </div>
        </section>
    <?php }
    private function render_section_seo( array $s ) { ?>
        <section class="st-section">
          <div class="st-section-head">
            <h2 class="st-section-title">SEO 全局设置</h2>
            <p class="st-section-desc">控制全站 SEO 行为、标题格式与结构化数据输出。</p>
          </div>
          <div class="st-section-body">
            <div class="st-field">
              <label class="st-field-label" for="st-title-sep">标题分隔符</label>
              <div>
                <select class="st-select" id="st-title-sep" name="autoseo_settings[title_sep]" style="max-width:120px">
                  <?php foreach([' | ',' - ',' — ',' · ',' // '] as $sep):?>
                  <option value="<?php echo esc_attr($sep);?>" <?php selected($s['title_sep']??'  -  ',$sep);?>><?php echo esc_html($sep);?></option>
                  <?php endforeach;?>
                </select>
                <p class="st-field-hint">用于组合「文章标题 + 分隔符 + 站点标题」。</p>
              </div>
            </div>
            <div class="st-field">
              <label class="st-field-label" for="st-home-title">首页 SEO 标题</label>
              <div>
                <input class="st-input" type="text" id="st-home-title" name="autoseo_settings[home_title]" value="<?php echo esc_attr($s['home_title']??get_bloginfo('name'));?>" />
                <p class="st-field-hint">建议 30 字以内，将显示在浏览器标签与搜索结果中。</p>
              </div>
            </div>
            <div class="st-field">
              <label class="st-field-label" for="st-home-desc">首页描述</label>
              <div>
                <input class="st-input" type="text" id="st-home-desc" name="autoseo_settings[home_desc]" value="<?php echo esc_attr($s['home_desc']??'');?>" placeholder="一段简短的首页描述，160 字以内" />
                <p class="st-field-hint">支持模板代码：<code>%site_name%</code> <code>%description%</code></p>
              </div>
            </div>
            <div class="st-field">
              <label class="st-field-label" for="st-og-image">默认 OG 分享图片</label>
              <div>
                <input class="st-input" type="url" id="st-og-image" name="autoseo_settings[default_og_image]" value="<?php echo esc_attr($s['default_og_image']??'');?>" placeholder="https://yoursite.com/og-image.jpg" />
                <p class="st-field-hint">文章没有封面图时用此图片作为分享卡片图。建议尺寸 1200&times;630px，微信要求最小 300&times;300px。</p>
              </div>
            </div>
            <div class="st-field">
              <label class="st-field-label" for="st-schema-type">结构化数据类型</label>
              <div>
                <select class="st-select" id="st-schema-type" name="autoseo_settings[schema_type]">
                  <?php foreach(['Article'=>'文章 (Article)','NewsArticle'=>'新闻文章 (NewsArticle)','BlogPosting'=>'博客帖子 (BlogPosting)','TechArticle'=>'技术文档 (TechArticle)','WebPage'=>'网页 (WebPage)'] as $v=>$l):?>
                  <option value="<?php echo esc_attr($v);?>" <?php selected($s['schema_type']??'Article',$v);?>><?php echo esc_html($l);?></option>
                  <?php endforeach;?>
                </select>
                <p class="st-field-hint">通过 JSON-LD 输出到页面头部，帮助搜索引擎理解内容结构。</p>
              </div>
            </div>
            <div class="st-field">
              <label class="st-field-label" for="st-sitemap">站点地图 (Sitemap)</label>
              <div>
                <label class="st-check-row">
                  <input type="checkbox" id="st-sitemap" name="autoseo_settings[sitemap_enabled]" value="1" <?php checked($s['sitemap_enabled']??true,true);?> />
                  <div class="cr-body">
                    <div class="cr-title">自动生成站点地图</div>
                    <div class="cr-hint">访问地址：<code style="font-family:var(--font-mono);font-size:11px;background:var(--surface-2);padding:1px 5px;border-radius:3px">/sitemap.xml</code></div>
                  </div>
                </label>
              </div>
            </div>
            <div class="st-field">
              <label class="st-field-label" for="st-noindex-archives">归档页 noindex</label>
              <div>
                <label class="st-check-row">
                  <input type="checkbox" id="st-noindex-archives" name="autoseo_settings[noindex_archives]" value="1" <?php checked($s['noindex_archives']??false,true);?> />
                  <div class="cr-body">
                    <div class="cr-title">对按日期、分类、标签等归档页设置 <code style="font-family:var(--font-mono);font-size:11px;background:var(--surface-2);padding:1px 5px;border-radius:3px">noindex</code></div>
                    <div class="cr-hint">避免搜索引擎收录重复的归档页面，集中权重到正文。</div>
                  </div>
                </label>
              </div>
            </div>
          </div>
        </section>
    <?php }
    private function render_save_bar( bool $saved ) { ?>
        <div class="st-save-bar">
          <div class="st-save-status">
            <span class="dot"></span>
            <span id="st-save-label"><?php echo $saved?'设置已保存':'修改将在保存后生效'; ?></span>
          </div>
          <div class="st-save-actions">
            <button type="button" class="aseo-btn" id="st-reset-btn">恢复默认</button>
            <?php submit_button('保存设置','primary aseo-btn aseo-btn-primary','submit',false); ?>
          </div>
        </div>
    <?php }
    private function render_wrap_close( array $providers ) { ?>
        </form>
        </div></div>
        <script>
        (function($){
          $('.engine-card').on('click',function(){
            var p=$(this).data('provider');
            $('.engine-card').removeClass('selected');
            $(this).addClass('selected');
            $('#st-provider-val').val(p);
            $('.sub-form').hide();
            $('#st-panel-'+p).show();
          });
          $('form').on('change input', function(){
            $('#st-save-label').text('有未保存的更改');
          });
          // 密钥输入框实时状态
          $(document).on('input', '.st-input[type=password], .st-input[type=text], .st-input[type=email]', function(){
            if ($(this).val().trim()) {
              $(this).addClass('is-saved');
            } else {
              $(this).removeClass('is-saved');
            }
          });
        })(jQuery);
        </script>
    <?php }
}
