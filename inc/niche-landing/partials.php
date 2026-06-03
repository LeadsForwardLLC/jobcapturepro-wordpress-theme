<?php
/**
 * Reusable markup partials for industry pages (matches homepage components).
 *
 * @package JCP_Core
 */

/**
 * Ranking factor card (homepage pattern).
 *
 * @param string               $title       Card title.
 * @param string               $icon        Lucide icon name.
 * @param string               $stat_value  Optional stat value.
 * @param string               $stat_label  Optional stat label.
 * @param callable(): void|null $body_cb     Optional inner HTML callback.
 */
function jcp_niche_factor_card( string $title, string $icon, string $stat_value = '', string $stat_label = '', ?callable $body_cb = null ): void {
	?>
	<div class="ranking-factor-card">
		<div class="factor-icon-wrapper">
			<img src="<?php echo esc_url( jcp_core_icon( $icon ) ); ?>" class="factor-icon" alt="" width="32" height="32" />
		</div>
		<h3 class="factor-title"><?php echo esc_html( $title ); ?></h3>
		<?php if ( $body_cb ) : ?>
			<div class="factor-description"><?php $body_cb(); ?></div>
		<?php endif; ?>
		<?php if ( $stat_value !== '' ) : ?>
			<div class="factor-stat">
				<span class="stat-value"><?php echo esc_html( $stat_value ); ?></span>
				<?php if ( $stat_label !== '' ) : ?>
					<span class="stat-label"><?php echo esc_html( $stat_label ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Hero-style meta strip (1 photo / 4 channels pattern).
 *
 * @param array<int, array<string, string>> $items Items with value, label, detail, icon keys.
 */
function jcp_niche_render_meta_strip( array $items ): void {
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
			$label = trim( (string) ( $item['value'] ?? '' ) . ' ' . (string) ( $item['label'] ?? '' ) );
			?>
			<div class="meta-item">
				<div class="meta-label">
					<img src="<?php echo esc_url( jcp_core_icon( $icon ) ); ?>" class="meta-icon" alt="" width="20" height="20" />
					<strong><?php echo esc_html( $label ); ?></strong>
				</div>
				<?php if ( ! empty( $item['detail'] ) ) : ?>
					<span><?php echo esc_html( (string) $item['detail'] ); ?></span>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Checkmark list for differentiation bullets.
 *
 * @param array<int, string> $lines Lines.
 */
function jcp_niche_render_conversion_points( array $lines ): void {
	if ( empty( $lines ) ) {
		return;
	}
	?>
	<div class="conversion-points jcp-niche-conversion-points">
		<?php foreach ( $lines as $line ) : ?>
			<div class="conversion-point">
				<div class="conversion-point-icon">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
						<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
						<polyline points="22 4 12 14.01 9 11.01"/>
					</svg>
				</div>
				<div class="conversion-point-text">
					<strong><?php echo esc_html( (string) $line ); ?></strong>
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
