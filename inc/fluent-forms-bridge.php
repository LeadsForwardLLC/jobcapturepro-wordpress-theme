<?php
/**
 * Theme-owned Fluent Forms bridge (styles, modal, form_embed helpers).
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether Fluent Forms plugin appears active.
 */
function jcp_fluent_forms_plugin_active(): bool {
	return defined( 'FLUENTFORM' ) || class_exists( '\FluentForm\Framework\Foundation\Application' ) || shortcode_exists( 'fluentform' );
}

/**
 * Fluent bridge settings (from global settings).
 *
 * @return array{enabled: bool, default_shortcode: string, mount_global_modal: bool}
 */
function jcp_fluent_bridge_settings(): array {
	$g = function_exists( 'jcp_global_settings' ) ? jcp_global_settings() : [];
	$f = is_array( $g['fluent_forms'] ?? null ) ? $g['fluent_forms'] : [];
	return [
		'enabled'            => ! array_key_exists( 'enabled', $f ) || ! empty( $f['enabled'] ),
		'default_shortcode'  => trim( (string) ( $f['default_shortcode'] ?? '' ) ),
		'mount_global_modal' => ! empty( $f['mount_global_modal'] ),
	];
}

/**
 * Allow only a single shortcode tag (Fluent Forms preferred).
 *
 * @param string $raw Raw shortcode string.
 */
function jcp_fluent_sanitize_shortcode( string $raw ): string {
	$raw = trim( wp_unslash( $raw ) );
	if ( $raw === '' ) {
		return '';
	}
	// Strip accidental wrapping backticks / quotes.
	$raw = trim( $raw, " \t\n\r\0\x0B`\"'" );
	if ( ! preg_match( '/^\[[a-zA-Z][a-zA-Z0-9_-]*(?:\s[^\]]*)?\]$/', $raw ) ) {
		return '';
	}
	return $raw;
}

/**
 * Whether current request likely needs the Fluent bridge assets.
 */
function jcp_fluent_bridge_should_enqueue(): bool {
	$settings = jcp_fluent_bridge_settings();
	if ( empty( $settings['enabled'] ) ) {
		return false;
	}

	if ( ! empty( $settings['mount_global_modal'] ) && $settings['default_shortcode'] !== '' ) {
		return true;
	}

	if ( ! is_singular() ) {
		return (bool) apply_filters( 'jcp_fluent_bridge_force_enqueue', false );
	}

	$post = get_queried_object();
	if ( ! $post instanceof WP_Post ) {
		return (bool) apply_filters( 'jcp_fluent_bridge_force_enqueue', false );
	}

	$content = '';
	if ( function_exists( 'jcp_page_get_content' ) && ( $post->post_type === 'page' || $post->post_type === 'jcp_niche_landing' ) ) {
		$doc = jcp_page_get_content( (int) $post->ID );
		foreach ( (array) ( $doc['blocks'] ?? [] ) as $block ) {
			if ( ! is_array( $block ) ) {
				continue;
			}
			if ( ( $block['type'] ?? '' ) === 'form_embed' ) {
				return true;
			}
		}
		$content = wp_json_encode( $doc );
	}

	$haystack = $post->post_content . ' ' . $content;
	if ( stripos( $haystack, '[fluentform' ) !== false || stripos( $haystack, 'form_embed' ) !== false ) {
		return true;
	}

	if ( function_exists( 'jcp_is_form_landing_template' ) && jcp_is_form_landing_template( $post ) && function_exists( 'jcp_form_landing_get_settings' ) ) {
		$fl = jcp_form_landing_get_settings( (int) $post->ID );
		if ( ! empty( $fl['embed'] ) && function_exists( 'jcp_form_landing_embed_needs_fluent' ) && jcp_form_landing_embed_needs_fluent( (string) $fl['embed'] ) ) {
			return true;
		}
	}

	return (bool) apply_filters( 'jcp_fluent_bridge_force_enqueue', false );
}

/**
 * Always load Fluent bridge assets on the Contact / Support page (form is theme-rendered).
 *
 * @param bool $force Current force flag.
 */
function jcp_fluent_bridge_force_on_contact( bool $force ): bool {
	if ( $force ) {
		return true;
	}
	if ( function_exists( 'jcp_core_get_page_detection' ) ) {
		$pages = jcp_core_get_page_detection();
		if ( ! empty( $pages['is_contact'] ) ) {
			return true;
		}
	}
	return is_page_template( 'page-contact.php' ) || is_page( 'contact' );
}
add_filter( 'jcp_fluent_bridge_force_enqueue', 'jcp_fluent_bridge_force_on_contact' );

/**
 * Enqueue Fluent bridge CSS/JS when needed.
 */
function jcp_fluent_bridge_enqueue_assets(): void {
	if ( is_admin() || ! jcp_fluent_bridge_should_enqueue() ) {
		return;
	}

	jcp_core_enqueue_style( 'jcp-fluent-forms-bridge', 'css/fluent-forms-bridge.css', [ 'jcp-core-base' ] );
	jcp_core_enqueue_script( 'jcp-fluent-forms-bridge', 'js/fluent-forms-bridge.js', [ 'jquery' ] );
}
add_action( 'wp_enqueue_scripts', 'jcp_fluent_bridge_enqueue_assets', 40 );

/**
 * Optionally mount a global quote modal in the footer.
 */
function jcp_fluent_bridge_render_global_modal(): void {
	$settings = jcp_fluent_bridge_settings();
	if ( empty( $settings['enabled'] ) || empty( $settings['mount_global_modal'] ) ) {
		return;
	}
	// Page-level form_embed modal already mounts #jcp-form-modal.
	if ( is_singular() && function_exists( 'jcp_page_get_content' ) ) {
		$post_id = (int) get_queried_object_id();
		if ( $post_id > 0 ) {
			$doc = jcp_page_get_content( $post_id );
			foreach ( (array) ( $doc['blocks'] ?? [] ) as $block ) {
				if ( ! is_array( $block ) ) {
					continue;
				}
				if ( ( $block['type'] ?? '' ) === 'form_embed' && sanitize_key( (string) ( $block['props']['display'] ?? 'inline' ) ) === 'modal' ) {
					return;
				}
			}
		}
	}
	$shortcode = jcp_fluent_sanitize_shortcode( $settings['default_shortcode'] );
	if ( $shortcode === '' ) {
		return;
	}
	jcp_fluent_render_quote_modal(
		[
			'id'          => 'jcp-form-modal',
			'headline'    => __( 'Get started', 'jcp-core' ),
			'subheadline' => '',
			'shortcode'   => $shortcode,
		]
	);
}
add_action( 'wp_footer', 'jcp_fluent_bridge_render_global_modal', 25 );

/**
 * Render the full-screen quote modal shell + shortcode.
 *
 * @param array<string, mixed> $args Modal args.
 */
function jcp_fluent_render_quote_modal( array $args ): void {
	$id          = sanitize_html_class( (string) ( $args['id'] ?? 'jcp-form-modal' ) );
	$headline    = (string) ( $args['headline'] ?? '' );
	$subheadline = (string) ( $args['subheadline'] ?? '' );
	$shortcode   = jcp_fluent_sanitize_shortcode( (string) ( $args['shortcode'] ?? '' ) );
	$takeover    = ! empty( $args['takeover'] );
	if ( $shortcode === '' ) {
		return;
	}
	$classes = 'jcp-fluent-quote-modal jcp-fluent-bridge';
	if ( $takeover ) {
		$classes .= ' jcp-fluent-quote-modal--takeover';
	}
	?>
	<div
		id="<?php echo esc_attr( $id ); ?>"
		class="<?php echo esc_attr( $classes ); ?>"
		role="dialog"
		aria-modal="true"
		aria-hidden="true"
		aria-labelledby="<?php echo esc_attr( $id ); ?>-title"
	>
		<button type="button" class="jcp-fluent-quote-modal__overlay" data-jcp-form-close aria-label="<?php esc_attr_e( 'Close', 'jcp-core' ); ?>"></button>
		<div class="jcp-fluent-quote-modal__dialog">
			<div class="jcp-fluent-quote-modal__header">
				<div>
					<?php if ( $headline !== '' ) : ?>
						<h2 id="<?php echo esc_attr( $id ); ?>-title" class="jcp-fluent-quote-modal__title"><?php echo esc_html( $headline ); ?></h2>
					<?php else : ?>
						<h2 id="<?php echo esc_attr( $id ); ?>-title" class="jcp-fluent-quote-modal__title screen-reader-text"><?php esc_html_e( 'Application form', 'jcp-core' ); ?></h2>
					<?php endif; ?>
					<?php if ( $subheadline !== '' ) : ?>
						<p class="jcp-fluent-quote-modal__subtitle"><?php echo esc_html( $subheadline ); ?></p>
					<?php endif; ?>
				</div>
				<button type="button" class="jcp-fluent-quote-modal__close" data-jcp-form-close aria-label="<?php esc_attr_e( 'Close form', 'jcp-core' ); ?>">&times;</button>
			</div>
			<div class="jcp-fluent-quote-modal__body">
				<?php echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Render form_embed block (inline or modal).
 *
 * @param array<string, mixed> $props Block props.
 */
function jcp_niche_render_form_embed( array $props ): void {
	$headline    = trim( (string) ( $props['headline'] ?? '' ) );
	$subheadline = trim( (string) ( $props['subheadline'] ?? '' ) );
	$shortcode   = jcp_fluent_sanitize_shortcode( (string) ( $props['shortcode'] ?? '' ) );
	$display     = sanitize_key( (string) ( $props['display'] ?? 'inline' ) );
	if ( $display !== 'modal' ) {
		$display = 'inline';
	}

	// Case study campaign: always use fullscreen modal takeover.
	if ( is_singular() && get_post_field( 'post_name', get_queried_object_id() ) === 'case-study' ) {
		$display = 'modal';
	}

	$show_headline    = ! array_key_exists( 'show_headline', $props ) || ! empty( $props['show_headline'] );
	$show_subheadline = ! array_key_exists( 'show_subheadline', $props ) || ! empty( $props['show_subheadline'] );

	if ( $shortcode === '' && $headline === '' && $subheadline === '' ) {
		return;
	}

	$settings = jcp_fluent_bridge_settings();
	if ( $shortcode === '' && ! empty( $settings['default_shortcode'] ) ) {
		$shortcode = jcp_fluent_sanitize_shortcode( $settings['default_shortcode'] );
	}

	if ( $display === 'modal' ) {
		$cta_label = $headline !== '' ? $headline : __( 'Apply now', 'jcp-core' );
		?>
		<section id="apply" class="jcp-section jcp-form-embed jcp-form-embed--modal-cta" data-jcp-form-display="modal">
			<div class="jcp-container">
				<div class="jcp-form-embed__intro">
					<?php if ( $show_headline && $headline !== '' ) : ?>
						<h2<?php if ( function_exists( 'jcp_niche_editable_attr' ) ) { jcp_niche_editable_attr( 'form_embed.headline' ); } ?>><?php echo esc_html( $headline ); ?></h2>
					<?php endif; ?>
					<?php if ( $show_subheadline && $subheadline !== '' ) : ?>
						<p<?php if ( function_exists( 'jcp_niche_editable_attr' ) ) { jcp_niche_editable_attr( 'form_embed.subheadline' ); } ?>><?php echo esc_html( $subheadline ); ?></p>
					<?php endif; ?>
					<p class="jcp-form-embed__modal-actions">
						<button
							type="button"
							class="btn btn-primary"
							data-jcp-form-trigger
							data-jcp-form-target="jcp-form-modal"
						>
							<?php esc_html_e( 'Apply for a Case Study Spot', 'jcp-core' ); ?>
						</button>
					</p>
				</div>
				<?php if ( current_user_can( 'edit_posts' ) ) : ?>
					<label class="jcp-form-embed__shortcode-field">
						<span class="jcp-form-embed__shortcode-label"><?php esc_html_e( 'Fluent Forms shortcode', 'jcp-core' ); ?></span>
						<input
							type="text"
							class="jcp-form-embed__shortcode-input"
							data-jcp-input-path="form_embed.shortcode"
							value="<?php echo esc_attr( $shortcode ); ?>"
							placeholder='[fluentform id="12"]'
							autocomplete="off"
							spellcheck="false"
						/>
						<span class="jcp-form-embed__shortcode-hint"><?php esc_html_e( 'Form opens in a fullscreen modal for visitors. Paste shortcode, then Save.', 'jcp-core' ); ?></span>
					</label>
				<?php endif; ?>
			</div>
		</section>
		<?php
		jcp_fluent_render_quote_modal(
			[
				'id'          => 'jcp-form-modal',
				'headline'    => $cta_label,
				'subheadline' => $subheadline,
				'shortcode'   => $shortcode,
				'takeover'    => true,
			]
		);
		return;
	}

	$hl_vis  = function_exists( 'jcp_niche_field_visibility' ) ? jcp_niche_field_visibility( $props, 'show_headline', true ) : [ 'show' => $show_headline, 'attr' => '' ];
	$sub_vis = function_exists( 'jcp_niche_field_visibility' ) ? jcp_niche_field_visibility( $props, 'show_subheadline', true ) : [ 'show' => $show_subheadline, 'attr' => '' ];
	?>
	<section id="apply" class="jcp-section jcp-form-embed" data-jcp-form-display="inline">
		<div class="jcp-container">
			<?php if ( ( ! empty( $hl_vis['show'] ) && $headline !== '' ) || ( ! empty( $sub_vis['show'] ) && $subheadline !== '' ) ) : ?>
				<div class="jcp-form-embed__intro">
					<?php if ( ! empty( $hl_vis['show'] ) && $headline !== '' ) : ?>
						<h2<?php echo $hl_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php if ( function_exists( 'jcp_niche_editable_attr' ) ) { jcp_niche_editable_attr( 'form_embed.headline' ); } ?>><?php echo esc_html( $headline ); ?></h2>
					<?php endif; ?>
					<?php if ( ! empty( $sub_vis['show'] ) && $subheadline !== '' ) : ?>
						<p<?php echo $sub_vis['attr']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php if ( function_exists( 'jcp_niche_editable_attr' ) ) { jcp_niche_editable_attr( 'form_embed.subheadline' ); } ?>><?php echo esc_html( $subheadline ); ?></p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="jcp-form-embed__panel jcp-fluent-bridge jcp-fluent-bridge--inline" data-jcp-form-panel>
				<?php if ( current_user_can( 'edit_posts' ) ) : ?>
					<label class="jcp-form-embed__shortcode-field">
						<span class="jcp-form-embed__shortcode-label"><?php esc_html_e( 'Fluent Forms shortcode', 'jcp-core' ); ?></span>
						<input
							type="text"
							class="jcp-form-embed__shortcode-input"
							data-jcp-input-path="form_embed.shortcode"
							value="<?php echo esc_attr( $shortcode ); ?>"
							placeholder='[fluentform id="12"]'
							autocomplete="off"
							spellcheck="false"
						/>
						<span class="jcp-form-embed__shortcode-hint"><?php esc_html_e( 'Paste your shortcode, then Save on the live editor (or Update in WP admin).', 'jcp-core' ); ?></span>
					</label>
				<?php endif; ?>
				<?php if ( $shortcode !== '' ) : ?>
					<?php echo do_shortcode( $shortcode ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php elseif ( current_user_can( 'edit_posts' ) ) : ?>
					<p class="jcp-form-embed__empty" role="status"><?php esc_html_e( 'Form will appear here after you save a shortcode.', 'jcp-core' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}
