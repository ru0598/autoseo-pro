<?php
namespace AutoSEO;

class ShareBar {

    public function __construct() {
        add_filter( 'the_content', [ $this, 'append_share_bar' ], 20 );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
        add_action( 'wp_footer', [ $this, 'footer_js' ] );
    }

    public function enqueue() {
        if ( ! is_single() ) return;
        wp_enqueue_style(
            'autoseo-share-bar',
            plugin_dir_url( dirname( __FILE__ ) ) . 'assets/css/share-bar.css',
            [],
            '1.0.0'
        );
    }

    public function append_share_bar( string $content ): string {
        if ( ! is_single() ) return $content;
        // 防止在干净内容之外重复注入（如侧边栏、相关文章延伸查询）
        global $post;
        static $injected = false;
        if ( $injected ) return $content;
        // 确认是当前主文章的正文（内容长度超过 50 字）
        if ( mb_strlen( strip_tags( $content ) ) < 50 ) return $content;
        $injected = true;
        return $content . $this->render();
    }

    private function render(): string {
        $post_id      = get_the_ID();
        $share_url    = urlencode( get_permalink( $post_id ) );
        $share_title  = urlencode( get_the_title( $post_id ) );
        $social_copy  = get_post_meta( $post_id, '_autoseo_social_copy', true );
        $share_text   = urlencode( $social_copy ?: get_the_title( $post_id ) );
        $share_img    = urlencode( get_the_post_thumbnail_url( $post_id, 'large' ) ?: '' );
        $permalink    = esc_attr( get_permalink( $post_id ) );

        ob_start(); ?>
<div class="autoseo-share-bar">
  <span class="autoseo-share-label">分享到</span>
  <div class="autoseo-share-btns">

    <!-- 微博 -->
    <a class="autoseo-share-btn autoseo-share-weibo"
       href="https://service.weibo.com/share/share.php?url=<?php echo $share_url; ?>&title=<?php echo $share_text; ?>&pic=<?php echo $share_img; ?>"
       target="_blank" rel="noopener" title="分享到微博">
      <svg width="16" height="16" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13.2588 0.87856C13.3259 0.330369 13.8247 -0.0596101 14.3729 0.0075159C16.1145 0.220771 17.6703 1.03051 18.8301 2.2232C20.0185 3.44538 20.7953 5.07419 20.9311 6.88193C20.9725 7.43266 20.5596 7.91267 20.0089 7.95406C19.4581 7.99545 18.9781 7.58254 18.9367 7.03181C18.8373 5.70856 18.2696 4.51568 17.3962 3.61749C16.5434 2.74052 15.4034 2.14863 14.1298 1.99269C13.5816 1.92556 13.1917 1.42675 13.2588 0.87856Z" fill="white"/><path d="M12.7778 3.92898C12.8418 3.38042 13.3385 2.98766 13.887 3.05172C14.937 3.17434 15.882 3.64086 16.5914 4.33671C17.3188 5.05037 17.8028 6.01081 17.8875 7.08597C17.9309 7.63655 17.5198 8.11805 16.9692 8.16143C16.4186 8.20481 15.9371 7.79365 15.8937 7.24307C15.8492 6.67855 15.5953 6.16127 15.1908 5.76443L13.655 5.03822C13.1065 4.97416 12.7137 4.47753 12.7778 3.92898Z" fill="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M6.60515 4.16379C7.50282 3.74617 8.61476 3.49918 9.58643 4.0471C9.90504 4.22676 10.1512 4.49318 10.2918 4.83045C10.4239 5.14751 10.4352 5.46036 10.4133 5.7082C10.3646 6.25745 10.1358 6.76531 9.95218 7.27876C10.171 7.17482 10.4258 7.03242 10.7417 6.85758C11.146 6.63384 11.6561 6.35423 12.1664 6.19143C12.6734 6.0297 13.3764 5.91907 14.0483 6.28789C14.6932 6.64187 14.9843 7.21665 15.0682 7.7818C15.1429 8.28514 15.0587 8.80221 14.9833 9.18624C14.9497 9.35755 14.9181 9.50325 14.8897 9.63448C14.8421 9.85362 14.8033 10.0324 14.7785 10.2227C14.7673 10.3091 14.7635 10.3715 14.7627 10.4148C14.9195 10.4593 15.1628 10.4796 15.5763 10.4957C15.8377 10.5059 16.1693 10.5188 16.4676 10.5652C16.8131 10.6188 17.3146 10.742 17.7065 11.1292C18.2804 11.6961 18.6435 12.6051 18.309 13.7319C17.9999 14.7729 17.1256 15.899 15.5423 17.1446C13.8313 18.4906 11.0283 19.4639 8.33947 19.6772C5.69314 19.8872 2.74964 19.3842 1.13204 17.273C-0.485507 15.1619 -0.125522 12.6927 0.702562 10.7325C1.53357 8.7654 2.92406 7.06625 3.82855 6.18728C4.71102 5.32969 5.67128 4.59824 6.60515 4.16379ZM7.44876 5.97716C7.11391 6.13294 6.74862 6.35668 6.36676 6.63969C5.99668 6.91397 5.61102 7.2439 5.22239 7.62157C4.45477 8.36754 3.24561 9.85217 2.54491 11.5108C1.84128 13.1764 1.73749 14.7749 2.71959 16.0566C3.70164 17.3383 5.73805 17.8773 8.18131 17.6835C10.5821 17.4931 12.9784 16.6169 14.3057 15.5727C15.7608 14.428 16.2558 13.6204 16.3917 13.1627C16.4911 12.8278 16.4068 12.6689 16.327 12.5794C16.3047 12.5714 16.2541 12.556 16.1606 12.5414C15.9861 12.5143 15.792 12.5064 15.5285 12.4956C15.4738 12.4934 15.4161 12.491 15.3549 12.4884C15.045 12.4748 14.6292 12.4521 14.2414 12.3457C13.8389 12.2353 13.3461 12.002 13.0296 11.4813C12.7045 10.9463 12.7467 10.3375 12.7953 9.96425C12.8305 9.69407 12.9001 9.37041 12.9581 9.10018C12.982 8.98895 13.004 8.88676 13.0208 8.80109C13.0666 8.56785 13.1414 8.28124 13.0836 8.04261C13.0541 8.03832 12.9655 8.03581 12.7742 8.09682C12.4819 8.19008 12.1402 8.36946 11.7102 8.60744C11.2769 8.84791 10.8438 9.09839 10.3768 9.26864C9.94977 9.42431 9.21408 9.60972 8.54467 9.16819C7.84279 8.70522 7.77485 7.972 7.83776 7.48238C7.86164 7.2965 7.90822 7.10566 7.96275 6.92416C8.02961 6.70163 8.10842 6.49311 8.17194 6.32504C8.24563 6.12973 8.32187 5.93437 8.37908 5.73345C8.18971 5.72308 7.89209 5.77091 7.44876 5.97716Z" fill="white"/></svg>
      <span>微博</span>
    </a>

    <!-- 微信 -->
    <button class="autoseo-share-btn autoseo-share-wechat" title="微信扫码分享"
            data-url="<?php echo $permalink; ?>" data-type="wechat">
      <svg width="16" height="16" viewBox="0 0 21 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.1875 7.10003C7.1875 7.72598 6.6838 8.23337 6.0625 8.23337C5.4412 8.23337 4.9375 7.72598 4.9375 7.10003C4.9375 6.47413 5.4412 5.96667 6.0625 5.96667C6.6838 5.96667 7.1875 6.47413 7.1875 7.10003Z" fill="white"/><path d="M11.6875 7.10003C11.6875 7.72598 11.1838 8.23337 10.5625 8.23337C9.9412 8.23337 9.4375 7.72598 9.4375 7.10003C9.4375 6.47413 9.9412 5.96667 10.5625 5.96667C11.1838 5.96667 11.6875 6.47413 11.6875 7.10003Z" fill="white"/><path d="M12.125 12.55C12.125 13.0194 12.5028 13.4 12.9688 13.4C13.4347 13.4 13.8125 13.0194 13.8125 12.55C13.8125 12.0805 13.4347 11.7 12.9688 11.7C12.5028 11.7 12.125 12.0805 12.125 12.55Z" fill="white"/><path d="M15.5 12.55C15.5 13.0194 15.8778 13.4 16.3438 13.4C16.8097 13.4 17.1875 13.0194 17.1875 12.55C17.1875 12.0805 16.8097 11.7 16.3438 11.7C15.8778 11.7 15.5 12.0805 15.5 12.55Z" fill="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M0 9C0 4.02942 4.02942 0 9 0C13.7684 0 17.6705 3.70813 17.9802 8.39836C19.7901 9.49318 21 11.4803 21 13.75C21 14.5857 20.8355 15.3852 20.5363 16.1162L20.9866 18.8367C21.0477 19.2059 20.8975 19.5784 20.5973 19.802C20.2972 20.0255 19.8973 20.0628 19.561 19.8985L17.9575 19.1151C17.0195 19.6769 15.9215 20 14.75 20C12.9128 20 11.2608 19.2073 10.1172 17.9453C8.82334 18.0989 7.48614 17.9597 6.11593 17.5503L2.89987 18.9317C2.5639 19.076 2.17597 19.0254 1.88823 18.7998C1.60048 18.5742 1.4588 18.2096 1.51875 17.8489L2.04091 14.7075C0.766181 13.1547 0 11.1659 0 9ZM8.91889 16.0043C8.64836 15.3049 8.5 14.5448 8.5 13.75C8.5 10.2982 11.2982 7.5 14.75 7.5C15.1287 7.5 15.4995 7.53368 15.8596 7.59821C15.2105 4.4041 12.3859 2 9 2C5.13398 2 2 5.13398 2 9C2 10.825 2.69706 12.4849 3.84121 13.7315C4.04928 13.9582 4.14138 14.2681 4.09093 14.5717L3.79168 16.372L5.66785 15.5661C5.8929 15.4695 6.1456 15.4587 6.37802 15.536C7.27304 15.8336 8.11841 15.9862 8.91889 16.0043ZM10.5 13.75C10.5 11.4028 12.4028 9.5 14.75 9.5C17.0972 9.5 19 11.4028 19 13.75C19 14.395 18.857 15.0038 18.6019 15.5488C18.5542 15.6467 18.5221 15.753 18.5085 15.8637C18.4961 15.9639 18.4992 16.0643 18.5166 16.1616L18.6968 17.2503L18.3387 17.0753C18.1669 16.9871 17.973 16.9506 17.7818 16.9694C17.6468 16.9827 17.5132 17.0237 17.3897 17.0935C17.3474 17.1173 17.3068 17.1442 17.2681 17.1741C16.5634 17.6934 15.694 18 14.75 18C12.4028 18 10.5 16.0972 10.5 13.75Z" fill="white"/></svg>
      <span>微信</span>
    </button>

    <!-- Twitter / X -->
    <a class="autoseo-share-btn autoseo-share-twitter"
       href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&text=<?php echo $share_text; ?>"
       target="_blank" rel="noopener" title="分享到 X">
      <svg viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.737-8.835L1.254 2.25H8.08l4.253 5.622 5.91-5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
      <span>X</span>
    </a>

    <!-- LinkedIn -->
    <a class="autoseo-share-btn autoseo-share-linkedin"
       href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $share_url; ?>"
       target="_blank" rel="noopener" title="分享到 LinkedIn">
      <svg width="16" height="16" viewBox="0 0 21 22" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M6 8C6 7.44772 5.55228 7 5 7H1C0.447715 7 0 7.44772 0 8V20.5C0 21.0523 0.447715 21.5 1 21.5H5C5.55228 21.5 6 21.0523 6 20.5V8ZM2 19.5V8.99996H4V19.5H2Z" fill="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M6 3C6 1.34315 4.65685 0 3 0C1.34315 0 0 1.34315 0 3C0 4.65685 1.34315 6 3 6C4.65685 6 6 4.65685 6 3ZM2 3C2 2.44772 2.44772 2 3 2C3.55228 2 4 2.44772 4 3C4 3.55228 3.55228 4 3 4C2.44772 4 2 3.55228 2 3Z" fill="white"/><path fill-rule="evenodd" clip-rule="evenodd" d="M9 21.5C8.4477 21.5 8 21.0523 8 20.5V13.5C8 9.91015 10.9102 7 14.5 7C18.0898 7 21 9.91015 21 13.5V20.5C21 21.0523 20.5523 21.5 20 21.5H16.5C15.9477 21.5 15.5 21.0523 15.5 20.5V13.4062C15.5 12.854 15.0523 12.4062 14.5 12.4062C13.9477 12.4062 13.5 12.854 13.5 13.4062V20.5C13.5 21.0523 13.0523 21.5 12.5 21.5H9ZM14.5 9C16.9853 9 19 11.0147 19 13.5V19.5H17.5V13.4062C17.5 11.7494 16.1569 10.4062 14.5 10.4062C12.8432 10.4062 11.5 11.7494 11.5 13.4062V19.5H10V13.5C10 11.0147 12.0147 9 14.5 9Z" fill="white"/></svg>
      <span>LinkedIn</span>
    </a>

    <!-- 复制链接 -->
    <button class="autoseo-share-btn autoseo-share-copy"
            data-url="<?php echo $permalink; ?>" data-type="copy" title="复制链接">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><rect width="14" height="14" x="8" y="8" rx="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
      <span class="autoseo-copy-label">复制链接</span>
    </button>

  </div>
</div>
        <?php return ob_get_clean();
    }

    public function footer_js() {
        if ( ! is_single() ) return;
        ?>
        <script>
        (function(){
          document.querySelectorAll('.autoseo-share-btn[data-type]').forEach(function(btn){
            btn.addEventListener('click', function(){
              var type = this.dataset.type, url = this.dataset.url;
              if(type === 'copy'){
                var label = this.querySelector('.autoseo-copy-label');
                navigator.clipboard ? navigator.clipboard.writeText(url).then(function(){ done(btn, label); })
                  : (function(){ var t=document.createElement('textarea'); t.value=url; document.body.appendChild(t); t.select(); document.execCommand('copy'); document.body.removeChild(t); done(btn, label); })();
                function done(b, l){ if(l) l.textContent='已复制！'; b.classList.add('autoseo-copied');
                  setTimeout(function(){ if(l) l.textContent='复制链接'; b.classList.remove('autoseo-copied'); }, 2000); }
              }
              if(type === 'wechat'){
                alert('请在微信中点击右上角「…」→「分享给朋友」或「分享到朋友圈」\n\n当前页面地址：' + url);
              }
            });
          });
        })();
        </script>
        <?php
    }
}
