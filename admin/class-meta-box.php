<?php
namespace AutoSEO\Admin;

class MetaBox {

    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'register' ] );
        add_action( 'save_post',      [ $this, 'save' ], 10, 2 );
    }

    public function register() {
        add_meta_box( 'autoseo_meta_box', 'AutoSEO Pro', [ $this, 'render' ], [ 'post', 'page' ], 'normal', 'high' );
    }

    public function render( \WP_Post $post ) {
        wp_nonce_field( 'autoseo_save_meta', 'autoseo_nonce' );
        $meta_desc   = get_post_meta( $post->ID, '_autoseo_meta_desc',   true );
        $seo_title   = get_post_meta( $post->ID, '_autoseo_seo_title',   true );
        $focus_kw    = get_post_meta( $post->ID, '_autoseo_focus_kw',    true );
        $canonical   = get_post_meta( $post->ID, '_autoseo_canonical',   true );
        $og_title    = get_post_meta( $post->ID, '_autoseo_og_title',    true );
        $noindex     = get_post_meta( $post->ID, '_autoseo_noindex',     true );
        $social_copy = get_post_meta( $post->ID, '_autoseo_social_copy', true );
        $robots_adv  = get_post_meta( $post->ID, '_autoseo_robots_adv',  true );
        $custom_js   = get_post_meta( $post->ID, '_autoseo_custom_js',   true );
        $redirect    = get_post_meta( $post->ID, '_autoseo_redirect',    true );
        $nonce       = wp_create_nonce( 'autoseo_ajax' );
        $site_url    = parse_url( get_site_url(), PHP_URL_HOST );
        $analyzer    = new \AutoSEO\Services\SEOAnalyzer();
        $result      = $post->post_status !== 'auto-draft' ? $analyzer->analyze( $post->ID ) : [ 'score' => 0, 'issues' => [], 'word_count' => 0 ];
        $score       = $result['score'];
        $score_label = $score >= 80 ? '优秀' : ( $score >= 60 ? '良好' : ( $score >= 40 ? '需优化' : '较差' ) );
        $score_ink   = $score >= 80 ? 'var(--success-ink)' : ( $score >= 60 ? 'var(--warning-ink)' : 'var(--danger-ink)' );
        $score_soft  = $score >= 80 ? 'var(--success-soft)' : ( $score >= 60 ? 'var(--warning-soft)' : 'var(--danger-soft)' );
        $stroke_color = $score >= 80 ? 'var(--success)' : ( $score >= 60 ? 'var(--warning)' : 'var(--danger)' );
        $circumference = 2 * M_PI * 27;
        $offset = $circumference * ( 1 - $score / 100 );
        $fail = count( array_filter( $result['issues'], fn($i) => $i['level'] === 'error' ) );
        $this->render_head( $post, $score, $score_label, $score_ink, $score_soft, $nonce );
        $this->render_tab_seo( $post, $seo_title, $focus_kw, $meta_desc, $result, $score, $score_label, $stroke_color, $circumference, $offset, $fail );
        $this->render_tab_ai( $post, $nonce );
        $this->render_tab_social( $post, $og_title, $seo_title, $meta_desc, $social_copy, $site_url );
        $this->render_tab_advanced( $post, $canonical, $noindex, $robots_adv, $custom_js, $redirect );
        $this->render_js( $post, $nonce, $og_title, $seo_title );
    }

    private function render_head( $post, $score, $score_label, $score_ink, $score_soft, $nonce ) { ?>
        <div id="autoseo-metabox"><div class="aseo-mb">
        <header class="aseo-mb-head">
          <div class="aseo-mb-brand"><div class="aseo-mb-mark">A</div><div><span class="aseo-mb-name">AutoSEO Pro</span><span class="aseo-mb-version">v<?php echo AUTOSEO_VERSION; ?></span></div></div>
          <div style="display:flex;align-items:center;gap:10px">
            <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:500;background:<?php echo $score_soft; ?>;color:<?php echo $score_ink; ?>">
              <span style="width:6px;height:6px;border-radius:50%;background:currentColor"></span>
              <span style="font-weight:700;font-variant-numeric:tabular-nums"><?php echo $score; ?></span>
              <span style="opacity:.7">/ 100 &middot; <?php echo $score_label; ?></span>
            </span>
          </div>
        </header>
        <nav class="aseo-mb-tabs">
          <button class="aseo-mb-tab active" data-tab="seo">SEO 基础</button>
          <button class="aseo-mb-tab" data-tab="ai">AI 工具</button>
          <button class="aseo-mb-tab" data-tab="social">社交媒体</button>
          <button class="aseo-mb-tab" data-tab="advanced">高级</button>
        </nav>
    <?php }
    private function render_tab_seo( $post, $seo_title, $focus_kw, $meta_desc, $result, $score, $score_label, $stroke_color, $circ, $offset, $fail ) { ?>
        <div class="aseo-mb-pane active" data-pane="seo">
          <div class="aseo-section">
            <div class="aseo-section-head"><span class="aseo-section-title">当前评分</span><span class="aseo-pill <?php echo $score>=80?'aseo-pill-success':($score>=60?'aseo-pill-warning':'aseo-pill-danger'); ?>"><span class="aseo-pill-dot"></span><?php echo $score_label; ?></span></div>
            <div class="aseo-score-card">
              <div class="aseo-score-ring">
                <svg width="64" height="64" viewBox="0 0 64 64"><circle class="track" cx="32" cy="32" r="27" fill="none" stroke-width="6"/><circle class="fill" cx="32" cy="32" r="27" fill="none" stroke-width="6" stroke="<?php echo $stroke_color; ?>" stroke-dasharray="<?php echo round($circ,2); ?>" stroke-dashoffset="<?php echo round($offset,2); ?>" stroke-linecap="round"/></svg>
                <span class="num" style="color:<?php echo $stroke_color; ?>"><?php echo $score; ?></span>
              </div>
              <div class="aseo-score-meta">
                <div class="aseo-score-status"><span class="dot" style="background:<?php echo $stroke_color; ?>"></span><?php echo $score_label; ?> &middot; <?php echo $fail>0?"还有 {$fail} 项待修复":'继续保持！'; ?></div>
                <div class="aseo-score-summary">字数：<?php echo $result['word_count']; ?> &middot; <?php echo count($result['issues']); ?> 项检查</div>
              </div>
            </div>
          </div>
          <div class="aseo-section">
            <div class="aseo-section-head"><span class="aseo-section-title">SEO 标题</span><span style="font-size:12px;color:var(--fg-subtle)" id="aseo-seo-title-count">0 / 60</span></div>
            <input class="aseo-input" type="text" id="aseo-seo-title" name="autoseo_seo_title" value="<?php echo esc_attr($seo_title); ?>" placeholder="留空则使用文章标题…" />
            <div class="aseo-field-hint">搜索结果中显示的标题，建议 30–60 字符，包含核心关键词</div>
          </div>
          <div class="aseo-section">
            <div class="aseo-section-head"><span class="aseo-section-title">聚焦关键词</span><button class="aseo-btn aseo-btn-sm" type="button" id="aseo-gen-kw">✨ AI 生成</button></div>
            <div id="aseo-kw-notice"></div>
            <input class="aseo-input" type="text" id="aseo-focus-kw" name="autoseo_focus_kw" value="<?php echo esc_attr($focus_kw); ?>" placeholder="输入主要关键词…" />
            <div class="aseo-field-hint">用于计算关键词密度与检查清单，建议选择 1 个核心词</div>
          </div>
          <div class="aseo-section">
            <div class="aseo-section-head"><span class="aseo-section-title">元描述（Meta Description）</span><span class="aseo-char-count" id="aseo-meta-count">0 / 160</span></div>
            <textarea class="aseo-textarea" id="aseo-meta-desc" name="autoseo_meta_desc" rows="3" placeholder="搜索引擎摘要，留空将从文章开头自动提取…"><?php echo esc_textarea($meta_desc); ?></textarea>
            <div class="aseo-field-hint">建议 120–160 字符，直接影响搜索结果点击率</div>
          </div>
          <?php if ( !empty($result['issues']) ) : ?>
          <div class="aseo-section">
            <div class="aseo-section-head"><span class="aseo-section-title">检查清单</span><span style="font-size:12px;color:var(--fg-muted)"><?php echo $fail; ?> 项待修复</span></div>
            <div class="aseo-checklist">
            <?php foreach ($result['issues'] as $issue) :
              $cls = $issue['level']==='error'?'fail':($issue['level']==='warning'?'warn':'pass');
              $icon = $cls==='pass'?'<path d="M20 6 9 17l-5-5"/>':($cls==='fail'?'<path d="M18 6 6 18M6 6l12 12"/>':'<path d="M12 9v4m0 4h.01"/>');
            ?>
              <div class="aseo-check-item <?php echo $cls; ?>">
                <svg class="aseo-check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><?php echo $icon; ?></svg>
                <span class="aseo-check-label"><?php echo esc_html($issue['msg']); ?></span>
              </div>
            <?php endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
        </div>
    <?php }
    private function render_tab_ai( $post, $nonce ) { ?>
        <div class="aseo-mb-pane" data-pane="ai">
          <div class="aseo-section">
            <div class="aseo-section-head"><span class="aseo-section-title">AI 标题建议</span><button class="aseo-btn aseo-btn-sm" id="aseo-gen-titles" data-post="<?php echo (int)$post->ID; ?>" data-nonce="<?php echo $nonce; ?>" type="button">✨ 生成建议</button></div>
            <p class="aseo-field-hint" style="margin-bottom:10px">根据文章正文与关键词，生成 5 个高点击率标题供选择</p>
            <div id="aseo-titles-list"></div>
          </div>
          <div class="aseo-section">
            <div class="aseo-section-head"><span class="aseo-section-title">AI 摘要生成</span><button class="aseo-btn aseo-btn-sm" id="aseo-gen-meta" data-post="<?php echo (int)$post->ID; ?>" data-nonce="<?php echo $nonce; ?>" type="button">✨ 生成摘要</button></div>
            <p class="aseo-field-hint">自动生成 Meta Description 和社交媒体分享文案</p>
            <div id="aseo-meta-result" style="margin-top:8px"></div>
          </div>
          <div class="aseo-section">
            <div class="aseo-section-head"><span class="aseo-section-title">一键 SEO 修复</span><button class="aseo-btn aseo-btn-sm" id="aseo-repair-all" data-post="<?php echo (int)$post->ID; ?>" data-nonce="<?php echo $nonce; ?>" type="button">🔧 一键修复</button></div>
            <p class="aseo-field-hint" style="margin-bottom:8px">自动修复 H 标签层级、补充缺失 ALT、检测重复内容</p>
            <div id="aseo-repair-log"></div>
          </div>
        </div>
    <?php }
    private function render_tab_social( $post, $og_title, $seo_title, $meta_desc, $social_copy, $site_url ) { ?>
        <div class="aseo-mb-pane" data-pane="social">
          <div class="aseo-section">
            <div class="aseo-section-head"><span class="aseo-section-title">OG 标题（Facebook / 微信分享）</span><span style="font-size:12px;color:var(--fg-subtle)" id="aseo-og-count">0 / 70</span></div>
            <input class="aseo-input" type="text" id="aseo-og-title" name="autoseo_og_title" value="<?php echo esc_attr($og_title); ?>" placeholder="留空则使用 SEO 标题…" />
            <div class="aseo-og-preview">
              <div class="aseo-og-preview-head"><span>分享预览</span><span>Facebook / 微信</span></div>
              <div class="aseo-og-preview-body">
                <div class="aseo-og-domain"><?php echo strtoupper($site_url); ?></div>
                <div class="aseo-og-title" id="aseo-og-preview-title"><?php echo esc_html($og_title?:($seo_title?:$post->post_title)); ?></div>
                <div class="aseo-og-desc" id="aseo-og-preview-desc"><?php echo esc_html($meta_desc?mb_substr($meta_desc,0,80).'…':'（元描述将显示在这里）'); ?></div>
              </div>
            </div>
          </div>
          <div class="aseo-section">
            <div class="aseo-section-head"><span class="aseo-section-title">社交媒体分享文案</span><button class="aseo-btn aseo-btn-sm aseo-btn-primary" type="button" id="aseo-gen-social">✨ AI 生成文案</button></div>
            <div id="aseo-social-notice"></div>
            <textarea class="aseo-textarea" id="aseo-social-copy" name="autoseo_social_copy" rows="4" placeholder="点击「AI 生成文案」自动生成微博/微信分享文案，或手动填写…"><?php echo esc_textarea($social_copy); ?></textarea>
            <div class="aseo-field-row"><span style="color:var(--fg-muted)">适合微博/微信分享，留空则前端使用文章摘要</span><span id="aseo-social-count">0 / 280</span></div>
          </div>
        </div>
    <?php }
    private function render_tab_advanced( $post, $canonical, $noindex, $robots_adv, $custom_js, $redirect ) { ?>
        <div class="aseo-mb-pane" data-pane="advanced">
          <div class="aseo-section">
            <div class="aseo-section-head"><span class="aseo-section-title">规范链接（Canonical URL）</span></div>
            <input class="aseo-input" type="url" name="autoseo_canonical" value="<?php echo esc_attr($canonical); ?>" placeholder="<?php echo esc_attr(get_permalink($post->ID)?:'https://example.com/path'); ?>" />
            <div class="aseo-field-hint">留空则使用文章永久链接。告诉搜索引擎内容的标准 URL，避免重复内容扭分</div>
          </div>
          <div class="aseo-section">
            <div class="aseo-section-head"><span class="aseo-section-title">搜索引擎设置</span></div>
            <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer">
              <input type="checkbox" name="autoseo_noindex" value="1" <?php checked($noindex,'1'); ?> style="margin-top:2px" />
              <div><div style="font-weight:500;font-size:13px">noindex — 不允许搜索引擎收录此页面</div><div class="aseo-field-hint">适用于草稿、测试页或登录后可见的内容</div></div>
            </label>
          </div>
          <div class="aseo-section">
            <details class="aseo-disclosure">
              <summary>自定义 robots meta 标签</summary>
              <div class="aseo-disclosure-body">
                <input class="aseo-input" type="text" name="autoseo_robots_adv" value="<?php echo esc_attr($robots_adv); ?>" placeholder="noarchive, nosnippet" />
                <div class="aseo-field-hint">多个值以逗号分隔。常用：noarchive、nosnippet、noimageindex</div>
              </div>
            </details>
          </div>
          <div class="aseo-section">
            <details class="aseo-disclosure">
              <summary>页面级分析代码</summary>
              <div class="aseo-disclosure-body">
                <textarea class="aseo-textarea" name="autoseo_custom_js" rows="3" placeholder="<!-- 仅在本页插入的自定义 script -->
"><?php echo esc_textarea($custom_js); ?></textarea>
              </div>
            </details>
            <details class="aseo-disclosure" style="margin-top:4px">
              <summary>301 重定向</summary>
              <div class="aseo-disclosure-body">
                <input class="aseo-input" type="url" name="autoseo_redirect" value="<?php echo esc_attr($redirect); ?>" placeholder="https://example.com/new-permalink" />
                <div class="aseo-field-hint">设置后访问旧 URL 将自动跳转，并保留原有搜索权重</div>
              </div>
            </details>
          </div>
        </div>
        </div></div><!-- close aseo-mb + autoseo-metabox -->
    <?php }
    private function render_js( $post, $nonce, $og_title, $seo_title ) {
        $default_og = esc_js( $og_title ?: ( $seo_title ?: $post->post_title ) );
        ?>
        <script>
        (function($){
          var postId=<?php echo (int)$post->ID; ?>, nonce='<?php echo $nonce; ?>';
          // tabs
          $('.aseo-mb-tab').on('click',function(){ var k=$(this).data('tab'); $('.aseo-mb-tab').removeClass('active'); $('.aseo-mb-pane').removeClass('active'); $(this).addClass('active'); $('[data-pane="'+k+'"]').addClass('active'); });
          // char counters
          $('#aseo-seo-title').on('input',function(){ var l=$(this).val().length; $('#aseo-seo-title-count').text(l+' / 60').css('color',l>60?'var(--danger)':(l>=30?'var(--success)':'var(--fg-subtle)')); }).trigger('input');
          $('#aseo-meta-desc').on('input',function(){ var l=$(this).val().length; $('#aseo-meta-count').text(l+' / 160').css('color',l>160?'var(--danger)':(l>=120?'var(--success)':'var(--fg-subtle)')); }).trigger('input');
          $('#aseo-og-title').on('input',function(){ var l=$(this).val().length,v=$(this).val()||'<?php echo $default_og; ?>'; $('#aseo-og-count').text(l+' / 70'); $('#aseo-og-preview-title').text(v); }).trigger('input');
          $('#aseo-social-copy').on('input',function(){ $('#aseo-social-count').text($(this).val().length+' / 280'); }).trigger('input');
          function checkId(){ if(postId<=0){ alert('请先保存文章再使用 AI 功能'); return false; } return true; }
          // AI 标题
          $('#aseo-gen-titles').on('click',function(){
            if(!checkId())return;
            var $b=$(this).prop('disabled',true).html('<span class="aseo-spin"></span> 生成中…');
            $('#aseo-titles-list').html('<div style="color:var(--fg-muted);font-size:13px">正在调用 AI，请稍候…</div>');
            $.post(ajaxurl,{action:'autoseo_gen_titles',post_id:postId,nonce:nonce},function(r){
              $b.prop('disabled',false).html('✨ 生成建议');
              if(!r.success){$('#aseo-titles-list').html('<div class="aseo-notice aseo-notice-error">❌ '+(r.data||'生成失败，请检查 AI 密钥配置')+'</div>');return;}
              if(!r.data||!r.data.length){$('#aseo-titles-list').html('<div class="aseo-notice aseo-notice-warning">⚠ AI 未返回标题，请确认模型配置正确</div>');return;}
              var html='',first=true;
              $.each(r.data,function(i,t){ if(!t)return;
                html+='<div class="aseo-ai-suggestion'+(first?' recommended':'')+'" data-title="'+$('<div>').text(t).html()+'"><div class="aseo-ai-head">'+(first?'<span class="aseo-pill aseo-pill-accent">推荐</span>':'<span class="aseo-pill aseo-pill-neutral">备选</span>')+'<span class="aseo-ai-meta">'+t.length+' 字符</span></div><div class="aseo-ai-title">'+$('<div>').text(t).html()+'</div><div class="aseo-ai-foot"><span></span><button class="aseo-btn aseo-btn-sm'+(first?' aseo-btn-primary':'')+' aseo-apply-title" type="button">'+(first?'应用此建议':'应用')+'</button></div></div>';
                first=false;
              });
              $('#aseo-titles-list').html(html);
            }).fail(function(x){$b.prop('disabled',false).html('✨ 生成建议');$('#aseo-titles-list').html('<div class="aseo-notice aseo-notice-error">请求失败 (HTTP '+x.status+')</div>');});
          });
          $(document).on('click','.aseo-apply-title',function(){
            var t=$(this).closest('.aseo-ai-suggestion').data('title');
            $('#aseo-seo-title').val(t).trigger('input');
            $('#title').val(t);
            $('.aseo-ai-suggestion').css('outline','');
            $(this).closest('.aseo-ai-suggestion').css('outline','2px solid var(--success)');
          });
          // AI 摘要
          $('#aseo-gen-meta').on('click',function(){
            if(!checkId())return;
            var $b=$(this).prop('disabled',true).html('<span class="aseo-spin"></span> 生成中…');
            $.post(ajaxurl,{action:'autoseo_gen_meta',post_id:postId,nonce:nonce},function(r){
              $b.prop('disabled',false).html('✨ 生成摘要');
              if(!r.success){$('#aseo-meta-result').html('<div class="aseo-notice aseo-notice-error">❌ '+(r.data||'生成失败')+'</div>');return;}
              if(r.data&&r.data.meta)$('#aseo-meta-desc').val(r.data.meta).trigger('input');
              if(r.data&&r.data.social)$('#aseo-social-copy').val(r.data.social).trigger('input');
              $('#aseo-meta-result').html('<div class="aseo-notice aseo-notice-success">✅ 摘要已填入，请检查后点击「更新」保存</div>');
              setTimeout(function(){$('#aseo-meta-result .aseo-notice').fadeOut();},5000);
            }).fail(function(x){$b.prop('disabled',false).html('✨ 生成摘要');$('#aseo-meta-result').html('<div class="aseo-notice aseo-notice-error">请求失败 (HTTP '+x.status+')</div>');});
          });
          // 聚焦关键词 AI 生成
          $('#aseo-gen-kw').on('click',function(){
            if(!checkId())return;
            var $b=$(this).prop('disabled',true).html('<span class="aseo-spin"></span> 生成中…');
            $('#aseo-kw-notice').html('');
            $.post(ajaxurl,{action:'autoseo_gen_kw',post_id:postId,nonce:nonce},function(r){
              $b.prop('disabled',false).html('✨ AI 生成');
              if(!r.success){$('#aseo-kw-notice').html('<div class="aseo-notice aseo-notice-error">❌ '+(r.data||'生成失败')+'</div>');return;}
              $('#aseo-focus-kw').val(r.data);
              $('#aseo-kw-notice').html('<div class="aseo-notice aseo-notice-success">✅ 关键词已填入</div>');
              setTimeout(function(){$('#aseo-kw-notice .aseo-notice').fadeOut();},3000);
            }).fail(function(x){$b.prop('disabled',false).html('✨ AI 生成');$('#aseo-kw-notice').html('<div class="aseo-notice aseo-notice-error">请求失败 (HTTP '+x.status+')</div>');});
          });
          // 社交文案 AI 生成
          $('#aseo-gen-social').on('click',function(){
            if(!checkId())return;
            var $b=$(this).prop('disabled',true).html('<span class="aseo-spin"></span> 生成中…');
            $('#aseo-social-notice').html('<div class="aseo-notice" style="color:var(--fg-muted);font-size:13px">正在调用 AI，请稍候…</div>');
            $.post(ajaxurl,{action:'autoseo_gen_meta',post_id:postId,nonce:nonce},function(r){
              $b.prop('disabled',false).html('✨ AI 生成文案');
              if(!r.success){$('#aseo-social-notice').html('<div class="aseo-notice aseo-notice-error">❌ '+(r.data||'生成失败，请检查 AI 密钥配置')+'</div>');return;}
              if(r.data&&r.data.social&&r.data.social.trim()){
                $('#aseo-social-copy').val(r.data.social).trigger('input');
                $('#aseo-social-notice').html('<div class="aseo-notice aseo-notice-success">✅ 文案已填入，请检查后点「更新」保存</div>');
              } else {
                $('#aseo-social-notice').html('<div class="aseo-notice aseo-notice-warning">⚠ AI 未返回文案，请确认文章有正文内容</div>');
              }
              setTimeout(function(){$('#aseo-social-notice .aseo-notice').fadeOut();},5000);
            }).fail(function(x){$b.prop('disabled',false).html('✨ AI 生成文案');$('#aseo-social-notice').html('<div class="aseo-notice aseo-notice-error">请求失败 (HTTP '+x.status+')</div>');});
          });
          // 一键修复
          $('#aseo-repair-all').on('click',function(){
            if(!checkId())return;
            var $b=$(this).prop('disabled',true).html('<span class="aseo-spin"></span> 修复中…');
            $('#aseo-repair-log').html('<div style="color:var(--fg-muted);font-size:13px">正在处理…</div>');
            $.post(ajaxurl,{action:'autoseo_repair_all',post_id:postId,nonce:nonce},function(r){
              $b.prop('disabled',false).html('🔧 一键修复');
              if(!r.success){$('#aseo-repair-log').html('<div class="aseo-notice aseo-notice-error">❌ '+(r.data||'修复失败')+'</div>');return;}
              var html=''; $.each(r.data,function(i,l){html+='<div class="aseo-check-item pass"><svg class="aseo-check-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg><span class="aseo-check-label">'+l+'</span></div>';});
              $('#aseo-repair-log').html('<div class="aseo-checklist">'+html+'</div>');
            }).fail(function(x){$b.prop('disabled',false).html('🔧 一键修复');$('#aseo-repair-log').html('<div class="aseo-notice aseo-notice-error">请求失败 (HTTP '+x.status+')</div>');});
          });
        })(jQuery);
        </script>
    <?php }

    public function save( int $post_id, \WP_Post $post ) {
        if ( ! isset( $_POST['autoseo_nonce'] ) || ! wp_verify_nonce( $_POST['autoseo_nonce'], 'autoseo_save_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        foreach ( [ '_autoseo_seo_title' => 'autoseo_seo_title', '_autoseo_meta_desc' => 'autoseo_meta_desc',
            '_autoseo_focus_kw' => 'autoseo_focus_kw', '_autoseo_canonical' => 'autoseo_canonical',
            '_autoseo_og_title' => 'autoseo_og_title', '_autoseo_social_copy' => 'autoseo_social_copy',
            '_autoseo_robots_adv' => 'autoseo_robots_adv', '_autoseo_custom_js' => 'autoseo_custom_js',
            '_autoseo_redirect' => 'autoseo_redirect' ] as $mk => $pk ) {
            update_post_meta( $post_id, $mk, sanitize_textarea_field( $_POST[ $pk ] ?? '' ) );
        }
        update_post_meta( $post_id, '_autoseo_noindex', ! empty( $_POST['autoseo_noindex'] ) ? '1' : '' );
    }
}
