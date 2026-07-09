<?php
/**
 * Comments Template
 * 
 * Displays comments and comment form.
 *
 * @package JCP_Core
 */

if ( post_password_required() ) {
  return;
}
?>

<div id="comments" class="jcp-comments">
  <?php if ( have_comments() ) : ?>
    <h2 class="jcp-comments-title">
      <?php
      $comments_number = get_comments_number();
      if ( '1' === $comments_number ) {
        echo esc_html__( 'One Comment', 'jcp-core' );
      } else {
        printf(
          esc_html( _n( '%s Comment', '%s Comments', $comments_number, 'jcp-core' ) ),
          number_format_i18n( $comments_number )
        );
      }
      ?>
    </h2>

    <ol class="jcp-comment-list">
      <?php
      wp_list_comments( [
        'style'       => 'ol',
        'short_ping'  => true,
        'avatar_size' => 48,
      ] );
      ?>
    </ol>

    <?php
    the_comments_pagination( [
      'prev_text' => '<span aria-hidden="true">←</span> <span class="screen-reader-text">' . esc_html__( 'Previous', 'jcp-core' ) . '</span>',
      'next_text' => '<span class="screen-reader-text">' . esc_html__( 'Next', 'jcp-core' ) . '</span> <span aria-hidden="true">→</span>',
    ] );
    ?>
  <?php endif; ?>

  <?php
  if ( ! comments_open() && get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
    ?>
    <p class="jcp-no-comments"><?php esc_html_e( 'Comments are closed.', 'jcp-core' ); ?></p>
  <?php endif; ?>

  <div class="comment-form-wrapper">
    <?php
    comment_form( [
      'title_reply'        => esc_html__( 'Leave a Comment', 'jcp-core' ),
      'title_reply_to'     => esc_html__( 'Leave a Reply to %s', 'jcp-core' ),
      'cancel_reply_link'  => esc_html__( 'Cancel Reply', 'jcp-core' ),
      'label_submit'       => esc_html__( 'Post Comment', 'jcp-core' ),
      'class_submit'       => 'btn btn-secondary jcp-comment-submit',
      'class_form'         => 'comment-form',
      'comment_field'     => '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Comment', 'jcp-core' ) . ' <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="4" required></textarea></p>',
      'fields'             => [
        'author' => '<p class="comment-form-author"><label for="author">' . esc_html__( 'Name', 'jcp-core' ) . ' <span class="required">*</span></label><input id="author" name="author" type="text" value="" size="30" required /></p>',
        'email'  => '<p class="comment-form-email"><label for="email">' . esc_html__( 'Email', 'jcp-core' ) . ' <span class="required">*</span></label><input id="email" name="email" type="email" value="" size="30" required /></p>',
        'url'    => '<p class="comment-form-url"><label for="url">' . esc_html__( 'Website', 'jcp-core' ) . '</label><input id="url" name="url" type="url" value="" size="30" /></p>',
      ],
    ] );
    ?>
  </div>
</div>
