<?php
namespace AutoSEO\Admin;

class Dashboard {

    // ── 仪表盘主页 ───────────────────────────────────────────────
    public function render() {
        $analyzer = new \AutoSEO\Services\SEOAnalyzer();
        $posts    = get_posts( [ 'post_type' => ['post','page'], 'post_status' => 'any', 'posts_per_page' => -1, 'orderby' => 'modified', 'order' => 'DESC' ] );
        $total    = count( $posts );
        $data     = [];
        foreach ( $posts as $p ) {
            $r = $analyzer->analyze( $p->ID );
            $data[] = [ 'post' => $p, 'score' => $r['score'], 'wc' => $r['word_count'],
                'has_meta' => (bool) get_post_meta( $p->ID, '_autoseo_meta_desc', true ),
                'focus_kw' => get_post_meta( $p->ID, '_autoseo_focus_kw', true ) ];
        }
        $scores  = array_column( $data, 'score' );
        $avg     = $total ? round( array_sum( $scores ) / $total ) : 0;
        $good    = count( array_filter( $scores, fn($s) => $s >= 80 ) );
        $needs   = count( array_filter( $scores, fn($s) => $s < 60 ) );
        $nonce   = wp_create_nonce( 'autoseo_ajax' );
        ?>
        <div id="autoseo-wrap">
        <div class="aseo-wrap">

        <header class="aseo-plugin-header">
          <div class="aseo-brand">
            <div class="aseo-brand-mark">A</div>
            <div>
              <div class="aseo-brand-name">AutoSEO Pro</div>
              <div class="aseo-brand-tag">站点 SEO 健康仪表盘 · v<?php echo AUTOSEO_VERSION; ?></div>
            </div>
          </div>
          <div class="aseo-header-actions">
            <a class="aseo-btn" href="<?php echo esc_url( admin_url('admin.php?page=autoseo-bulk') ); ?>">全站分析</a>
            <button class="aseo-btn aseo-btn-primary" id="aseo-reanalyze" data-nonce="<?php echo $nonce; ?>">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/></svg>
              立即重新分析
            </button>
          </div>
        </header>

        <section class="aseo-metrics">
          <?php
          $metric_cls = $avg >= 80 ? 'aseo-tag-success' : ( $avg >= 60 ? 'aseo-tag-warning' : 'aseo-tag-danger' );
          $this->metric( '总分析文章', $total, '', 'aseo-tag-neutral', '全部' );
          $this->metric( '平均 SEO 分', $avg, '/ 100', $metric_cls, $avg >= 80 ? '优秀' : ( $avg >= 60 ? '需关注' : '待提升' ) );
          $this->metric( '优质文章', $good, '篇', 'aseo-tag-success', 'SEO 分 ≥ 80' );
          $this->metric( '待优化文章', $needs, '篇', 'aseo-tag-warning', 'SEO 分 < 60' );
          ?>
        </section>

        <div class="aseo-toolbar">
          <div class="aseo-tabs" id="aseo-filter-tabs">
            <button class="aseo-tab active" data-filter="all">全部<span class="aseo-tab-count"><?php echo $total; ?></span></button>
            <button class="aseo-tab" data-filter="good">优质<span class="aseo-tab-count"><?php echo $good; ?></span></button>
            <button class="aseo-tab" data-filter="needs">待优化<span class="aseo-tab-count"><?php echo $needs; ?></span></button>
          </div>
          <div style="display:flex;align-items:center;gap:8px">
            <div class="aseo-search">
              <svg class="aseo-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
              <input type="search" id="aseo-search" placeholder="搜索文章标题…" />
            </div>
          </div>
        </div>

        <div class="aseo-table-wrap">
          <table id="aseo-posts-table">
            <thead><tr>
              <th>文章标题</th>
              <th class="num">SEO 分</th>
              <th>元描述</th>
              <th>聚焦关键词</th>
              <th class="num">字数</th>
              <th style="width:1%"></th>
            </tr></thead>
            <tbody>
            <?php foreach ( $data as $row ) :
              $p     = $row['post'];
              $score = $row['score'];
              $ncls  = $score >= 80 ? 'aseo-num-high' : ( $score >= 60 ? 'aseo-num-mid' : 'aseo-num-low' );
              $status_label = $p->post_status === 'publish' ? '已发布' : ( $p->post_status === 'draft' ? '草稿' : $p->post_status );
              $modified = $p->post_modified !== '0000-00-00 00:00:00' ? date( 'Y-m-d', strtotime( $p->post_modified ) ) : '—';
              $filter_cls = $score >= 80 ? 'row-good' : ( $score < 60 ? 'row-needs' : 'row-ok' );
            ?>
            <tr data-filter="<?php echo $filter_cls; ?>" data-title="<?php echo esc_attr( $p->post_title ); ?>">
              <td class="aseo-title-cell">
                <a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( $p->post_title ); ?></a>
                <div class="aseo-title-meta"><span><?php echo $status_label; ?></span><span class="aseo-dot"></span><span><?php echo $modified; ?></span></div>
              </td>
              <td class="aseo-num-cell <?php echo $ncls; ?>"><?php echo $score; ?></td>
              <td><?php echo $row['has_meta']
                ? '<span class="aseo-pill aseo-pill-success"><span class="aseo-pill-dot"></span>已设置</span>'
                : '<span class="aseo-pill aseo-pill-danger"><span class="aseo-pill-dot"></span>未设置</span>'; ?></td>
              <td><?php echo $row['focus_kw']
                ? '<span class="aseo-pill aseo-pill-success"><span class="aseo-pill-dot"></span>' . esc_html( $row['focus_kw'] ) . '</span>'
                : '<span class="aseo-pill aseo-pill-neutral"><span class="aseo-pill-dot"></span>未设置</span>'; ?></td>
              <td class="aseo-num-cell"><?php echo number_format( $row['wc'] ); ?></td>
              <td style="text-align:right">
                <a class="aseo-btn aseo-btn-sm" href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>">编辑</a>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <div class="aseo-table-foot">
            <div id="aseo-row-count">共 <?php echo $total; ?> 条</div>
            <div></div>
          </div>
        </div>

        <footer class="aseo-plugin-foot" style="font-size:14px;">
          <div><a style="color:#000;text-decoration:underline;"  target="_blank" href="https://sunnywp.com">Sunny · WordPress主题开发</a> 向您致敬！</div>
          <div class="aseo-plugin-foot-right">
            <a href="<?php echo esc_url( admin_url('admin.php?page=autoseo-settings') ); ?>">设置</a>
            <a href="<?php echo esc_url( admin_url('admin.php?page=autoseo-rank') ); ?>">排名监控</a>
            <span>v<?php echo AUTOSEO_VERSION; ?></span>
          </div>
        </footer>

        </div><!-- .aseo-wrap -->
        </div><!-- #autoseo-wrap -->

        <script>
        (function($){
          // 过滤 tab
          $('#aseo-filter-tabs .aseo-tab').on('click', function(){
            $('#aseo-filter-tabs .aseo-tab').removeClass('active');
            $(this).addClass('active');
            var f = $(this).data('filter');
            filterTable();
          });
          // 搜索
          $('#aseo-search').on('input', filterTable);
          function filterTable(){
            var f = $('#aseo-filter-tabs .aseo-tab.active').data('filter');
            var q = $('#aseo-search').val().toLowerCase();
            var vis = 0;
            $('#aseo-posts-table tbody tr').each(function(){
              var cls = $(this).data('filter');
              var ttl = $(this).data('title').toLowerCase();
              var show = (f==='all' || cls==='row-'+f || (f==='needs'&&cls==='row-needs') || (f==='good'&&cls==='row-good'));
              if(q) show = show && ttl.indexOf(q)>=0;
              $(this).toggle(show);
              if(show) vis++;
            });
            $('#aseo-row-count').text('显示 '+vis+' 条');
          }
          // 重新分析（刷新页面）
          $('#aseo-reanalyze').on('click', function(){
            $(this).prop('disabled',true).html('<span class="aseo-spin"></span> 分析中…');
            location.reload();
          });
        })(jQuery);
        </script>
        <?php
    }

    // ── 全站分析 ────────────────────────────────────────────────
    public function render_bulk() {
        $analyzer = new \AutoSEO\Services\SEOAnalyzer();
        $posts    = get_posts( [ 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 100 ] );
        ?>
        <div id="autoseo-wrap">
        <div class="aseo-wrap">

        <div class="aseo-page-header">
          <div>
            <div class="aseo-breadcrumb">
              <a href="<?php echo esc_url( admin_url('admin.php?page=autoseo-pro') ); ?>">AutoSEO Pro</a>
              <span>›</span><span>全站 SEO 分析</span>
            </div>
            <div class="aseo-page-title">全站 SEO 分析</div>
            <div class="aseo-page-desc">批量查看所有已发布文章的 SEO 状态与问题</div>
          </div>
          <a class="aseo-btn" href="<?php echo esc_url( admin_url('admin.php?page=autoseo-pro') ); ?>">← 返回仪表盘</a>
        </div>

        <div class="aseo-toolbar">
          <div class="aseo-tabs"></div>
          <div class="aseo-search">
            <svg class="aseo-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input type="search" id="aseo-bulk-search" placeholder="搜索文章…" />
          </div>
        </div>

        <div class="aseo-table-wrap">
          <table>
            <thead><tr>
              <th>文章标题</th><th class="num">SEO 分</th>
              <th>主要问题</th><th class="num">字数</th><th style="width:1%"></th>
            </tr></thead>
            <tbody>
            <?php foreach ( $posts as $p ) :
              $r      = $analyzer->analyze( $p->ID );
              $score  = $r['score'];
              $ncls   = $score >= 80 ? 'aseo-num-high' : ( $score >= 60 ? 'aseo-num-mid' : 'aseo-num-low' );
              $errors = array_filter( $r['issues'], fn($i) => $i['level'] === 'error' );
            ?>
            <tr data-title="<?php echo esc_attr( $p->post_title ); ?>">
              <td class="aseo-title-cell">
                <a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( $p->post_title ); ?></a>
              </td>
              <td class="aseo-num-cell <?php echo $ncls; ?>"><?php echo $score; ?></td>
              <td style="font-size:12px">
                <?php foreach ( array_slice( $errors, 0, 2 ) as $issue ) : ?>
                <div style="color:var(--danger-ink);margin-bottom:2px">⚠ <?php echo esc_html( $issue['msg'] ); ?></div>
                <?php endforeach; ?>
                <?php if ( empty( $errors ) ) echo '<span style="color:var(--success-ink)">✓ 未发现严重问题</span>'; ?>
              </td>
              <td class="aseo-num-cell"><?php echo number_format( $r['word_count'] ); ?></td>
              <td style="text-align:right"><a class="aseo-btn aseo-btn-sm" href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>">优化</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <div class="aseo-table-foot"><div>共 <?php echo count($posts); ?> 篇已发布文章</div><div></div></div>
        </div>

        </div></div>
        <script>
        (function($){
          $('#aseo-bulk-search').on('input',function(){
            var q=$(this).val().toLowerCase();
            $('table tbody tr').each(function(){
              $(this).toggle(!q||$(this).data('title').toLowerCase().indexOf(q)>=0);
            });
          });
        })(jQuery);
        </script>
        <?php
    }

    private function metric( $label, $value, $unit, $tag_cls, $tag_text ) {
        echo '<article class="aseo-metric">';
        echo '<div class="aseo-metric-head"><span class="aseo-metric-label">' . $label . '</span>';
        echo '<span class="aseo-tag ' . $tag_cls . '">' . $tag_text . '</span></div>';
        echo '<div style="display:flex;align-items:baseline;gap:6px;margin-top:2px">';
        echo '<span class="aseo-metric-value">' . $value . '</span>';
        if ( $unit ) echo '<span class="aseo-metric-unit">' . $unit . '</span>';
        echo '</div></article>';
    }
}
