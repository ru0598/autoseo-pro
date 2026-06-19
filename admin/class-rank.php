<?php
namespace AutoSEO\Admin;

class Rank {

    public function render() {
        $keywords = get_option( 'autoseo_monitor_keywords', [] );
        $nonce    = wp_create_nonce( 'autoseo_ajax' );
        ?>
        <div id="autoseo-wrap">
        <div class="aseo-wrap">

        <div class="aseo-page-header">
          <div>
            <div class="aseo-breadcrumb">
              <a href="<?php echo esc_url( admin_url('admin.php?page=autoseo-pro') ); ?>">AutoSEO Pro</a>
              <span>›</span><span>关键词排名监控</span>
            </div>
            <div class="aseo-page-title">关键词排名监控</div>
            <div class="aseo-page-desc">跟踪核心关键词在 Google / 百度的排名变化，排名波动 ≥ 3 位将自动发送邮件通知</div>
          </div>
          <div style="display:flex;gap:8px">
            <button class="aseo-btn" id="aseo-check-now" data-nonce="<?php echo $nonce; ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
              立即检查全部
            </button>
          </div>
        </div>

        <!-- 统计卡片 -->
        <?php if ( $keywords ) :
          $checked = array_filter( $keywords, fn($k) => $k['rank'] > 0 );
          $top10   = array_filter( $keywords, fn($k) => $k['rank'] > 0 && $k['rank'] <= 10 );
        ?>
        <section class="aseo-metrics" style="grid-template-columns:repeat(3,1fr)">
          <article class="aseo-metric">
            <div class="aseo-metric-head"><span class="aseo-metric-label">监控关键词</span><span class="aseo-tag aseo-tag-neutral">总计</span></div>
            <div style="display:flex;align-items:baseline;gap:6px;margin-top:2px"><span class="aseo-metric-value"><?php echo count($keywords); ?></span><span class="aseo-metric-unit">个</span></div>
          </article>
          <article class="aseo-metric">
            <div class="aseo-metric-head"><span class="aseo-metric-label">已检测</span><span class="aseo-tag aseo-tag-success">有排名</span></div>
            <div style="display:flex;align-items:baseline;gap:6px;margin-top:2px"><span class="aseo-metric-value"><?php echo count($checked); ?></span><span class="aseo-metric-unit">个</span></div>
          </article>
          <article class="aseo-metric">
            <div class="aseo-metric-head"><span class="aseo-metric-label">前 10 排名</span><span class="aseo-tag aseo-tag-accent">优质</span></div>
            <div style="display:flex;align-items:baseline;gap:6px;margin-top:2px"><span class="aseo-metric-value"><?php echo count($top10); ?></span><span class="aseo-metric-unit">个</span></div>
          </article>
        </section>
        <?php endif; ?>

        <!-- 添加关键词 -->
        <div class="aseo-add-kw-form">
          <div class="aseo-form-group">
            <label>关键词</label>
            <input type="text" id="aseo-kw-input" placeholder="输入关键词…" />
          </div>
          <div class="aseo-form-group">
            <label>搜索引擎</label>
            <select id="aseo-engine-select">
              <option value="google">Google</option>
              <option value="baidu">百度</option>
            </select>
          </div>
          <div style="padding-bottom:1px">
            <button class="aseo-btn aseo-btn-primary" id="aseo-add-kw">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
              添加关键词
            </button>
          </div>
          <div id="aseo-add-result" style="padding-bottom:1px"></div>
        </div>

        <!-- 关键词卡片网格 -->
        <?php if ( $keywords ) : ?>
        <div class="aseo-kw-grid" id="aseo-kw-grid">
          <?php foreach ( $keywords as $i => $kw ) :
            $rank = $kw['rank'] ?? -1;
            $rank_cls = $rank <= 0 ? 'aseo-tag-neutral' : ( $rank <= 3 ? 'aseo-tag-success' : ( $rank <= 10 ? 'aseo-tag-accent' : ( $rank <= 30 ? 'aseo-tag-warning' : 'aseo-tag-danger' ) ) );
            $rank_str = $rank > 0 ? '#' . $rank : '—';
            $checked_at = $kw['checked_at'] ?? 0;
            $last = $checked_at ? human_time_diff( $checked_at ) . '前' : '未检测';
          ?>
          <div class="aseo-kw-card">
            <div class="aseo-kw-card-top">
              <div>
                <div class="aseo-kw-word"><?php echo esc_html( $kw['keyword'] ); ?></div>
                <div class="aseo-kw-engine"><?php echo strtoupper( $kw['engine'] ?? 'google' ); ?> 搜索</div>
              </div>
              <div style="text-align:right">
                <div class="aseo-kw-rank"><?php echo $rank_str; ?></div>
                <div class="aseo-kw-rank-label"><span class="aseo-tag <?php echo $rank_cls; ?>" style="font-size:10px"><?php
                  echo $rank <= 0 ? '未检测' : ( $rank <= 3 ? '前三名' : ( $rank <= 10 ? '首页' : ( $rank <= 30 ? '前三页' : '排名靠后' ) ) );
                ?></span></div>
              </div>
            </div>
            <div class="aseo-kw-foot">
              <span>最后检查：<?php echo $last; ?></span>
              <button class="aseo-btn aseo-btn-sm aseo-btn-danger aseo-del-kw" data-index="<?php echo $i; ?>" data-nonce="<?php echo $nonce; ?>">删除</button>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else : ?>
        <div style="text-align:center;padding:60px 20px;background:var(--surface);border:1px dashed var(--border);border-radius:var(--radius);color:var(--fg-muted)">
          <div style="font-size:32px;margin-bottom:12px">📊</div>
          <div style="font-size:15px;font-weight:600;color:var(--fg);margin-bottom:6px">还没有监控任何关键词</div>
          <div>在上方添加您想要追踪排名的关键词</div>
        </div>
        <?php endif; ?>

        <!-- 排名列表表格 -->
        <?php if ( $keywords ) : ?>
        <div class="aseo-card" style="margin-top:20px">
          <div class="aseo-card-head">
            <span class="aseo-card-title">排名详情</span>
            <span style="font-size:12px;color:var(--fg-muted)">每日自动检查，排名波动 ≥ 3 位发送邮件通知</span>
          </div>
          <div class="aseo-table-wrap" style="border:none;border-radius:0;box-shadow:none">
            <table>
              <thead><tr>
                <th>关键词</th><th>搜索引擎</th>
                <th class="num">当前排名</th><th class="num">状态</th>
                <th>最后检查</th><th style="width:1%"></th>
              </tr></thead>
              <tbody>
              <?php foreach ( $keywords as $i => $kw ) :
                $rank = $kw['rank'] ?? -1;
                $ncls = $rank <= 0 ? '' : ( $rank <= 10 ? 'aseo-num-high' : ( $rank <= 30 ? 'aseo-num-mid' : 'aseo-num-low' ) );
                $checked_at = $kw['checked_at'] ?? 0;
              ?>
              <tr>
                <td style="font-weight:500"><?php echo esc_html( $kw['keyword'] ); ?></td>
                <td><?php echo strtoupper( $kw['engine'] ?? 'google' ); ?></td>
                <td class="aseo-num-cell <?php echo $ncls; ?>"><?php echo $rank > 0 ? '#' . $rank : '—'; ?></td>
                <td class="aseo-num-cell"><?php
                  if ( $rank <= 0 ) echo '<span class="aseo-pill aseo-pill-neutral"><span class="aseo-pill-dot"></span>未检测</span>';
                  elseif ( $rank <= 3 ) echo '<span class="aseo-pill aseo-pill-success"><span class="aseo-pill-dot"></span>前三名</span>';
                  elseif ( $rank <= 10 ) echo '<span class="aseo-pill aseo-pill-accent"><span class="aseo-pill-dot"></span>首页</span>';
                  else echo '<span class="aseo-pill aseo-pill-warning"><span class="aseo-pill-dot"></span>第 ' . ceil($rank/10) . ' 页</span>';
                ?></td>
                <td style="font-size:12px;color:var(--fg-muted)"><?php echo $checked_at ? date( 'Y-m-d H:i', $checked_at ) : '—'; ?></td>
                <td style="text-align:right">
                  <button class="aseo-btn aseo-btn-sm aseo-del-kw" data-index="<?php echo $i; ?>" data-nonce="<?php echo $nonce; ?>">删除</button>
                </td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
        <?php endif; ?>

        </div></div>
        <script>
        (function($){
          var nonce = '<?php echo $nonce; ?>';
          $('#aseo-add-kw').on('click', function(){
            var kw = $('#aseo-kw-input').val().trim();
            var eng = $('#aseo-engine-select').val();
            if (!kw) { $('#aseo-add-result').html('<span class="aseo-notice aseo-notice-warning">请输入关键词</span>'); return; }
            $(this).prop('disabled',true).html('<span class="aseo-spin"></span>');
            $.post(ajaxurl, {action:'autoseo_add_keyword', keyword:kw, engine:eng, nonce:nonce}, function(r){
              $('#aseo-add-kw').prop('disabled',false).html('+ 添加关键词');
              if (r.success) { location.reload(); }
              else { $('#aseo-add-result').html('<span class="aseo-notice aseo-notice-error">'+r.data+'</span>'); }
            });
          });
          $('#aseo-check-now').on('click', function(){
            $(this).prop('disabled',true).html('<span class="aseo-spin"></span> 检查中…');
            $.post(ajaxurl, {action:'autoseo_rank_check_now', nonce:nonce}, function(){ location.reload(); });
          });
          $(document).on('click', '.aseo-del-kw', function(){
            if (!confirm('确认删除此关键词？')) return;
            var idx = $(this).data('index');
            $.post(ajaxurl, {action:'autoseo_delete_keyword', index:idx, nonce:nonce}, function(r){
              if (r.success) location.reload();
            });
          });
        })(jQuery);
        </script>
        <?php
    }
}
