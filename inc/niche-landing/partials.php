<?php
/**
 * Reusable markup partials for industry pages (matches homepage components).
 *
 * Components are small building blocks (cards, meta strips, checklists).
 * Full page sections are Blocks in inc/page-blocks/registry.php.
 *
 * @package JCP_Core
 */

/**
 * Ranking factor card (homepage pattern).
 *
 * @param string               $title            Card title.
 * @param string               $icon             Lucide icon name.
 * @param string               $stat_value       Optional stat value.
 * @param string               $stat_label       Optional stat label.
 * @param callable(): void|null $body_cb          Optional inner HTML callback.
 * @param string               $title_path       JSON path for title.
 * @param string               $stat_value_path  JSON path for stat value.
 * @param string               $stat_label_path  JSON path for stat label.
 * @param int                  $array_index      Optional list index for add/remove UI (-1 = none).
 */
function jcp_niche_factor_card( string $title, string $icon, string $stat_value = '', string $stat_label = '', ?callable $body_cb = null, string $title_path = '', string $stat_value_path = '', string $stat_label_path = '', int $array_index = -1 ): void {
	?>
	<div class="ranking-factor-card"<?php if ( $array_index >= 0 ) { jcp_niche_array_item_attr( $array_index ); } ?>>
		<div class="factor-icon-wrapper">
			<img src="<?php echo esc_url( jcp_core_icon( $icon ) ); ?>" class="factor-icon" alt="" width="32" height="32" />
		</div>
		<h3 class="factor-title"<?php if ( $title_path !== '' ) { jcp_niche_editable_attr( $title_path ); } ?>><?php echo esc_html( $title ); ?></h3>
		<?php if ( $body_cb ) : ?>
			<div class="factor-description"><?php $body_cb(); ?></div>
		<?php endif; ?>
		<?php if ( $stat_value !== '' ) : ?>
			<div class="factor-stat">
				<span class="stat-value"<?php if ( $stat_value_path !== '' ) { jcp_niche_editable_attr( $stat_value_path ); } ?>><?php echo esc_html( $stat_value ); ?></span>
				<?php if ( $stat_label !== '' ) : ?>
					<span class="stat-label"<?php if ( $stat_label_path !== '' ) { jcp_niche_editable_attr( $stat_label_path ); } ?>><?php echo esc_html( $stat_label ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Hero-style meta strip (1 photo / 4 channels pattern).
 *
 * @param array<int, array<string, string>> $items       Items with value, label, detail, icon keys.
 * @param string                            $path_prefix JSON path prefix (e.g. core_mechanic).
 */
function jcp_niche_render_meta_strip( array $items, string $path_prefix = '' ): void {
	if ( empty( $items ) ) {
		return;
	}
	$default_icons = [ 'camera', 'map', 'clock' ];
	?>
	<div class="directory-meta jcp-niche-meta-strip">
		<?php foreach ( $items as $i => $item ) : ?>
			<?php
			if ( ! is_array( $item ) ) {
				continue;
			}
			$icon = ! empty( $item['icon'] ) ? (string) $item['icon'] : ( $default_icons[ $i ] ?? 'check' );
			$value = (string) ( $item['value'] ?? '' );
			$label = (string) ( $item['label'] ?? '' );
			$combined = trim( $value . ' ' . $label );
			$base = $path_prefix !== '' ? $path_prefix . '.' . $i : '';
			?>
			<div class="meta-item">
				<div class="meta-label">
					<img src="<?php echo esc_url( jcp_core_icon( $icon ) ); ?>" class="meta-icon" alt="" width="20" height="20" />
					<?php if ( $base !== '' ) : ?>
						<strong>
							<span<?php jcp_niche_editable_attr( $base . '.value' ); ?>><?php echo esc_html( $value ); ?></span>
							<?php if ( $label !== '' ) : ?>
								<span<?php jcp_niche_editable_attr( $base . '.label' ); ?>><?php echo esc_html( ' ' . $label ); ?></span>
							<?php endif; ?>
						</strong>
					<?php else : ?>
						<strong><?php echo esc_html( $combined ); ?></strong>
					<?php endif; ?>
				</div>
				<?php if ( ! empty( $item['detail'] ) ) : ?>
					<span<?php if ( $base !== '' ) { jcp_niche_editable_attr( $base . '.detail' ); } ?>><?php echo esc_html( (string) $item['detail'] ); ?></span>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Checkmark list for differentiation bullets.
 *
 * @param array<int, string|array<string, string>> $lines        Lines.
 * @param string                                   $path_prefix JSON path prefix (e.g. conversion.points).
 */
function jcp_niche_render_conversion_points( array $lines, string $path_prefix = '' ): void {
	?>
	<div class="conversion-points jcp-niche-conversion-points"<?php
	if ( $path_prefix !== '' ) {
		jcp_niche_array_attr( $path_prefix );
	}
	?>>
		<?php foreach ( $lines as $i => $line ) : ?>
			<?php
			$text = is_array( $line ) ? (string) ( $line['text'] ?? '' ) : (string) $line;
			if ( $text === '' ) {
				continue;
			}
			$path = $path_prefix !== '' ? $path_prefix . '.' . $i : '';
			?>
			<div class="conversion-point"<?php if ( $path_prefix !== '' ) { jcp_niche_array_item_attr( (int) $i ); } ?>>
				<div class="conversion-point-icon">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
						<polyline points="22 4 12 14.01 9 11.01"/>
					</svg>
				</div>
				<div class="conversion-point-text">
					<strong<?php if ( $path !== '' ) { jcp_niche_editable_attr( $path ); } ?>><?php echo esc_html( $text ); ?></strong>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Centered closing line below a section.
 *
 * @param string $text Text.
 * @param string $path Optional JSON path for inline editor.
 */
function jcp_niche_render_section_closing( string $text, string $path = '' ): void {
	if ( $text === '' ) {
		return;
	}
	?>
	<p class="rankings-supporting jcp-niche-section-closing"<?php
	if ( $path !== '' ) {
		jcp_niche_editable_attr( $path );
	}
	?>><?php echo esc_html( $text ); ?></p>
	<?php
}
