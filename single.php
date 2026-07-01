<?php
/**
 * Single Post Template
 * 
 * Displays individual blog posts.
 *
 * @package JCP_Core
 */

get_header();
?>

<div class="jcp-read-progress" role="presentation" aria-hidden="true">
  <div class="jcp-read-progress-bar" id="jcp-read-progress-bar"></div>
</div>

<main class="jcp-marketing">
  <?php
  while ( have_posts() ) :
    the_post();
    $post_url   = get_permalink();
    $post_title = get_the_title();
    $share_url  = rawurlencode( $post_url );
    $share_text = rawurlencode( $post_title );
    $icon_base  = get_stylesheet_directory_uri() . '/assets/shared/assets/icons/lucide';
    $content    = get_the_content();
    $content    = wp_strip_all_tags( $content );
    $word_count = str_word_count( $content );
    $read_mins  = max( 1, (int) ceil( $word_count / 200 ) );
    ?>
  <section class="jcp-section jcp-single-post-section">
    <div class="jcp-container jcp-single-post-container">
      <div class="jcp-single-post-wrapper">
        <article id="post-<?php the_ID(); ?>" <?php post_class( 'jcp-single-post' ); ?>>
          <header class="jcp-single-post-header">
            <h1 class="jcp-single-hero-title"><?php echo esc_html( $post_title ); ?></h1>
            <div class="jcp-post-meta jcp-single-hero-meta">
              <div class="jcp-post-meta-line jcp-post-meta-details">
                <time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>" class="jcp-post-date">
                  <?php echo esc_html( get_the_date() ); ?>
                </time>
                <?php
                $categories = get_the_category();
                if ( ! empty( $categories ) ) :
                  ?>
                  <span class="jcp-post-meta-sep" aria-hidden="true">·</span>
                  <span class="jcp-post-categories">
                    <?php
                    foreach ( $categories as $category ) {
                      echo '<a href="' . esc_url( get_category_link( $category->term_id ) ) . '" class="jcp-post-category">' . esc_html( $category->name ) . '</a>';
                    }
                    ?>
                  </span>
                <?php endif; ?>
              </div>
              <span class="jcp-post-reading-time"><?php echo esc_html( sprintf( __( '%1$s min read', 'jcp-core' ), (int) $read_mins ) ); ?></span>
            </div>
          </header>

          <div class="jcp-post-content">
            <?php
            the_content();

            wp_link_pages( [
              'before' => '<nav class="jcp-page-links"><p class="jcp-page-links-title">' . esc_html__( 'Pages:', 'jcp-core' ) . '</p>',
              'after'  => '</nav>',
            ] );
            ?>
          </div>

          <?php
          $tags = get_the_tags();
          if ( $tags ) :
            ?>
            <footer class="jcp-post-footer">
              <div class="jcp-post-tags">
                <span class="jcp-post-tags-label"><?php esc_html_e( 'Tags:', 'jcp-core' ); ?></span>
                <?php
                foreach ( $tags as $tag ) {
                  echo '<a href="' . esc_url( get_tag_link( $tag->term_id ) ) . '" class="jcp-post-tag">' . esc_html( $tag->name ) . '</a>';
                }
                ?>
              </div>
            </footer>
          <?php endif; ?>
        </article>

        <div class="jcp-post-share-section">
          <span class="jcp-post-share-label"><?php esc_html_e( 'Share this post', 'jcp-core' ); ?></span>
          <div class="jcp-post-share-buttons">
            <a href="https://twitter.com/intent/tweet?url=<?php echo $share_url; ?>&amp;text=<?php echo $share_text; ?>" class="jcp-post-share-btn jcp-post-share-twitter" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on X', 'jcp-core' ); ?>">
              <img src="<?php echo esc_url( $icon_base . '/twitter.svg' ); ?>" width="20" height="20" alt="" aria-hidden="true">
            </a>
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo $share_url; ?>" class="jcp-post-share-btn jcp-post-share-linkedin" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on LinkedIn', 'jcp-core' ); ?>">
              <img src="<?php echo esc_url( $icon_base . '/linkedin.svg' ); ?>" width="20" height="20" alt="" aria-hidden="true">
            </a>
            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $share_url; ?>" class="jcp-post-share-btn jcp-post-share-facebook" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e( 'Share on Facebook', 'jcp-core' ); ?>">
              <img src="<?php echo esc_url( $icon_base . '/facebook.svg' ); ?>" width="20" height="20" alt="" aria-hidden="true">
            </a>
            <button type="button" class="jcp-post-share-btn jcp-post-share-copy" data-url="<?php echo esc_attr( $post_url ); ?>" aria-label="<?php esc_attr_e( 'Copy link', 'jcp-core' ); ?>">
              <img src="<?php echo esc_url( $icon_base . '/link.svg' ); ?>" width="20" height="20" alt="" aria-hidden="true">
            </button>
          </div>
        </div>

        <nav class="jcp-post-navigation">
          <?php
          $prev_post = get_previous_post();
          $next_post = get_next_post();
          ?>
          <?php if ( $prev_post ) : ?>
            <div class="jcp-post-nav-prev">
              <a href="<?php echo esc_url( get_permalink( $prev_post->ID ) ); ?>" class="btn btn-secondary">
                ← Previous: <?php echo esc_html( get_the_title( $prev_post->ID ) ); ?>
              </a>
            </div>
          <?php endif; ?>
          <?php if ( $next_post ) : ?>
            <div class="jcp-post-nav-next">
              <a href="<?php echo esc_url( get_permalink( $next_post->ID ) ); ?>" class="btn btn-secondary">
                Next: <?php echo esc_html( get_the_title( $next_post->ID ) ); ?> →
              </a>
            </div>
          <?php endif; ?>
        </nav>

        <?php
        if ( comments_open() || get_comments_number() ) {
          comments_template();
        }
        ?>
      </div>
    </div>
  </section>
  <?php endwhile; ?>
</main>

<script>
(function() {
  var btn = document.querySelector('.jcp-post-share-copy');
  if (!btn) return;
  btn.addEventListener('click', function() {
    var url = btn.getAttribute('data-url');
    if (!url) return;
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(url).then(function() {
        btn.classList.add('jcp-share-copied');
        btn.setAttribute('aria-label', '<?php echo esc_js( __( 'Copied!', 'jcp-core' ) ); ?>');
        setTimeout(function() {
          btn.classList.remove('jcp-share-copied');
          btn.setAttribute('aria-label', '<?php echo esc_js( __( 'Copy link', 'jcp-core' ) ); ?>');
        }, 2000);
      });
    } else {
      var input = document.createElement('input');
      input.value = url;
      document.body.appendChild(input);
      input.select();
      document.execCommand('copy');
      document.body.removeChild(input);
      btn.classList.add('jcp-share-copied');
      setTimeout(function() { btn.classList.remove('jcp-share-copied'); }, 2000);
    }
  });
})();
</script>
<script>
(function() {
  var bar = document.getElementById('jcp-read-progress-bar');
  if (!bar) return;
  function updateProgress() {
    var scrollTop = window.scrollY || document.documentElement.scrollTop;
    var scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
    var pct = scrollHeight <= 0 ? 0 : Math.min(100, (scrollTop / scrollHeight) * 100);
    bar.style.width = pct + '%';
  }
  window.addEventListener('scroll', updateProgress, { passive: true });
  window.addEventListener('resize', updateProgress);
  updateProgress();
})();
</script>
<?php
get_footer();
