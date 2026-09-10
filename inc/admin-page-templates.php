<?php
/**
 * Simplify the Page template dropdown in WP Admin.
 *
 * @package JCP_Core
 */

/**
 * Whether page-template filtering should run (classic admin + block editor REST).
 */
function jcp_admin_should_filter_page_templates(): bool {
	return is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST );
}

/**
 * Slug → template file for one-off app/marketing routes.
 *
 * @return array<string, string>
 */
function jcp_admin_special_page_templates(): array {
	$map = [
		'pricing'             => 'page-pricing.php',
		'demo'                => 'page-demo.php',
		'directory'           => 'page-directory.php',
		'support'             => 'page-support.php',
		'contact'             => 'page-support.php',
		'contact-success'     => 'page-contact-success.php',
		'help'                => 'page-help.php',
		'referral-program'    => 'page-referral-program.php',
		'estimate'            => 'page-estimate.php',
		'prototype'           => 'page-prototype.php',
		'company'             => 'page-company.php',
		'wp-plugin-prototype' => 'page-wp-plugin-prototype.php',
		'ui-library'          => 'page-ui-library.php',
	];

	if ( ! current_user_can( 'manage_options' ) ) {
		unset( $map['prototype'], $map['wp-plugin-prototype'], $map['ui-library'] );
	}

	return $map;
}

/**
 * Default template list for new/unknown pages: Default + JCP Block Page only.
 *
 * @param array<string, string> $templates Template slug => label.
 * @return array<string, string>
 */
function jcp_admin_minimal_page_templates( array $templates ): array {
	$out = [];

	if ( isset( $templates[''] ) ) {
		$out[''] = $templates[''];
	}

	if ( isset( $templates['page-jcp-blocks.php'] ) ) {
		$out['page-jcp-blocks.php'] = $templates['page-jcp-blocks.php'];
	}

	if ( isset( $templates['page-form-landing.php'] ) ) {
		$out['page-form-landing.php'] = $templates['page-form-landing.php'];
	}

	if ( isset( $templates['page-sales-tool.php'] ) ) {
		$out['page-sales-tool.php'] = $templates['page-sales-tool.php'];
	}

	return $out !== [] ? $out : $templates;
}

/**
 * Limit template choices so editors are not faced with 16+ options.
 *
 * @param array<string, string> $templates Template slug => label.
 * @param WP_Theme              $theme     Theme.
 * @param WP_Post|null          $post      Post being edited.
 * @return array<string, string>
 */
function jcp_admin_filter_page_templates( array $templates, $theme, $post ): array {
	if ( ! jcp_admin_should_filter_page_templates() ) {
		return $templates;
	}

	if ( ! $post instanceof WP_Post || $post->post_type !== 'page' ) {
		return jcp_admin_minimal_page_templates( $templates );
	}

	$slug     = (string) ( $post->post_name ?? '' );
	$current  = (string) get_page_template_slug( $post );
	$front    = (int) get_option( 'page_on_front' );
	$is_front = $post->ID > 0 && $post->ID === $front;

	$out = jcp_admin_minimal_page_templates( $templates );

	if ( ( $is_front || $slug === 'home' ) && isset( $templates['page-home.php'] ) ) {
		$out['page-home.php'] = $templates['page-home.php'];
	}

	$slug_map = jcp_admin_special_page_templates();
	if ( $slug !== '' && isset( $slug_map[ $slug ], $templates[ $slug_map[ $slug ] ] ) ) {
		$file         = $slug_map[ $slug ];
		$out[ $file ] = $templates[ $file ];
	}

	if ( $current !== '' && ! isset( $out[ $current ] ) && isset( $templates[ $current ] ) ) {
		$file_path = get_stylesheet_directory() . '/' . $current;
		if ( file_exists( $file_path ) ) {
			$out[ $current ] = $templates[ $current ];
		}
	}

	return $out;
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
		p.textContent = <?php echo wp_json_encode( __( 'This page uses the JCP block editor. For new marketing landers, choose JCP Block Page. Other templates here are tied to fixed URLs (Home, Pricing, Demo, etc.) — only use them if this page’s slug matches that route.', 'jcp-core' ) ); ?>;
		<?php else : ?>
		p.innerHTML = <?php echo wp_json_encode(
			__( '<strong>Almost always:</strong> choose <em>JCP Block Page</em> for new landers. Use <em>Form Landing</em> for a full-screen form/embed page with no site chrome. Block editor: Settings (gear) → Template. Classic editor: Page Attributes → Template. Fixed routes (Pricing, Demo, etc.) appear only when the page slug matches.', 'jcp-core' )
		); ?>;
		<?php endif; ?>
		select.parentNode && select.parentNode.appendChild(p);
	});
	</script>
	<?php
}
add_action( 'admin_footer-post.php', 'jcp_admin_page_template_help' );
add_action( 'admin_footer-post-new.php', 'jcp_admin_page_template_help' );
