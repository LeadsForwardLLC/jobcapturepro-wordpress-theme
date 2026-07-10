<?php
/**
 * Tag Archive Template
 * 
 * Displays tag archive pages.
 *
 * @package JCP_Core
 */

get_header();
?>

<main class="jcp-marketing">
  <!-- Hero Section (same spacing as blog archive / category / author) -->
  <section class="jcp-section rankings-section jcp-archive-hero-section">
    <div class="jcp-container">
      <div class="rankings-header">
        <h1><?php single_tag_title(); ?></h1>
        <?php
        $description = tag_description();
        if ( $description ) :
          ?>
          <p class="rankings-subtitle"><?php echo wp_kses_post( $description ); ?></p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- Tag Posts Section -->
  <section class="jcp-section rankings-section">
    <div class="jcp-container">
      <?php if ( have_posts() ) : ?>
        <?php $blog_has_posts = true; ?>
        <div class="jcp-blog-grid">
          <?php
          while ( have_posts() ) :
            the_post();
            get_template_part( 'templates/content/content', 'post-card' );
          endwhile;
          ?>
        </div>

        <?php
        get_template_part( 'templates/partials/pagination' );
      else :
        get_template_part( 'templates/content/content', 'none' );
      endif;
      ?>
    </div>
  </section>

  <?php
  if ( function_exists( 'jcp_blog_conversion_render_archive_footer' ) ) {
    jcp_blog_conversion_render_archive_footer( ! empty( $blog_has_posts ) );
  }
  ?>
</main>

<?php
get_footer();
