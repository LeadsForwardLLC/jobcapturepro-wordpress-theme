<?php
/**
 * Simplify the Page template dropdown in WP Admin.
 *
 * @package JCP_Core
 */

/**
 * Slug → template file for one-off app/marketing routes.
 *
 * @return array<string, string>
 */
function jcp_admin_special_page_templates(): array {
	return [
		'pricing'                => 'page-pricing.php',
		'demo'                   => 'page-demo.php',
		'directory'              => 'page-directory.php',
		'contact'                => 'page-contact.php',
		'contact-success'        => 'page-contact-success.php',
		'early-access'           => 'page-early-access.php',
		'early-access-success'   => 'page-early-access-success.php',
		'help'                   => 'page-help.php',
		'referral-program'       => 'page-referral-program.php',
		'estimate'               => 'page-estimate.php',
		'prototype'              => 'page-prototype.php',
		'company'                => 'page-company.php',
		'wp-plugin-prototype'    => 'page-wp-plugin-prototype.php',
		'ui-library'             => 'page-ui-library.php',
	];
}

/**
 * Limit template choices so editors are not faced with 16+ options.
 *
 * @param array<string, string> $templates Template slug => label.
 * @param WP_Theme              $theme     Theme.
 * @param WP_Post               $post      Post being edited.
 * @return array<string, string>
 */
function jcp_admin_filter_page_templates( array $templates, $theme, $post ): array {
	if ( ! is_admin() || ! $post instanceof WP_Post || $post->post_type !== 'page' ) {
		return $templates;
	}

	$slug    = (string) ( $post->post_name ?? '' );
	$current = (string) get_page_template_slug( $post );
	$front   = (int) get_option( 'page_on_front' );
	$is_front = $post->ID > 0 && $post->ID === $front;

	$out = [];

	if ( isset( $templates[''] ) ) {
		$out[''] = $templates[''];
	}

	if ( isset( $templates['page-jcp-blocks.php'] ) ) {
		$out['page-jcp-blocks.php'] = $templates['page-jcp-blocks.php'];
	}

	if ( ( $is_front || $slug === 'home' ) && isset( $templates['page-home.php'] ) ) {
		$out['page-home.php'] = $templates['page-home.php'];
	}

	$slug_map = jcp_admin_special_page_templates();
	if ( $slug !== '' && isset( $slug_map[ $slug ], $templates[ $slug_map[ $slug ] ] ) ) {
		$file = $slug_map[ $slug ];
		$out[ $file ] = $templates[ $file ];
	}

	if ( $current !== '' && ! isset( $out[ $current ] ) && isset( $templates[ $current ] ) ) {
		$out[ $current ] = $templates[ $current ];
	}

	return $out !== [] ? $out : $templates;
}
add_filter( 'theme_page_templates', 'jcp_admin_filter_page_templates', 10, 3 );

/**
 * Helper note under the template control on page edit screens.
 */
function jcp_admin_page_template_help(): void {
	global $post;
	if ( ! $post instanceof WP_Post || $post->post_type !== 'page' ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen || $screen->base !== 'post' ) {
		return;
	}

	$uses_editor = function_exists( 'jcp_admin_page_uses_editor' ) && jcp_admin_page_uses_editor( $post );
	?>
	<script>
	document.addEventListener('DOMContentLoaded', function () {
		var select = document.getElementById('page_template');
		if (!select || select.dataset.jcpHelp) return;
		select.dataset.jcpHelp = '1';
		var p = document.createElement('p');
		p.className = 'description jcp-template-help';
		p.style.marginTop = '8px';
		<?php if ( $uses_editor ) : ?>
		p.textContent = <?php echo wp_json_encode( __( 'This page uses the JCP block editor. Use “JCP Block Page” for marketing landers, or the special template that matches this URL (Home, Referral Program, etc.).', 'jcp-core' ) ); ?>;
		<?php else : ?>
		p.innerHTML = <?php echo wp_json_encode(
			sprintf(
				/* translators: %s: template name */
				__( '<strong>Building a marketing page?</strong> Choose <em>JCP Block Page</em> and click Update. Default template is for simple text pages only.', 'jcp-core' )
			)
		); ?>;
		<?php endif; ?>
		select.parentNode && select.parentNode.appendChild(p);
	});
	</script>
	<?php
}
add_action( 'admin_footer-post.php', 'jcp_admin_page_template_help' );
add_action( 'admin_footer-post-new.php', 'jcp_admin_page_template_help' );
