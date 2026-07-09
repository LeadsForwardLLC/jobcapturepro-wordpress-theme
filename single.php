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
    $post_title = get_the_title();
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
      </div>
    </div>
  </section>

  <?php
  if ( function_exists( 'jcp_blog_conversion_render_end' ) ) {
    jcp_blog_conversion_render_end( (int) get_the_ID() );
  }
  ?>

  <?php if ( comments_open() || get_comments_number() ) : ?>
  <section class="jcp-section jcp-single-post-footer-section">
    <div class="jcp-container jcp-single-post-container">
      <div class="jcp-single-post-wrapper">
        <?php comments_template(); ?>
      </div>
    </div>
  </section>
  <?php endif; ?>
  <?php endwhile; ?>
</main>

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
