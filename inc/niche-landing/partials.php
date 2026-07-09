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
 * @param string               $icon_path        JSON path for icon slug (e.g. benefits.items.0.icon).
 * @param bool                 $show_icon        Whether to render the icon wrapper.
 */
function jcp_niche_factor_card( string $title, string $icon, string $stat_value = '', string $stat_label = '', ?callable $body_cb = null, string $title_path = '', string $stat_value_path = '', string $stat_label_path = '', int $array_index = -1, string $icon_path = '', bool $show_icon = true ): void {
	?>
	<div class="ranking-factor-card"<?php if ( $array_index >= 0 ) { jcp_niche_array_item_attr( $array_index ); } ?>>
		<?php if ( $show_icon ) : ?>
		<div class="factor-icon-wrapper"<?php if ( $icon_path !== '' ) { echo ' data-jcp-icon-path="' . esc_attr( $icon_path ) . '" title="' . esc_attr__( 'Click to change icon', 'jcp-core' ) . '" role="button" tabindex="0"'; } ?>>
			<img src="<?php echo esc_url( jcp_core_icon( $icon ) ); ?>" class="factor-icon" alt="" width="32" height="32" />
		</div>
		<?php endif; ?>
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
 * Normalize core mechanic items for the homepage-style stat row.
 *
 * @param array<int, array<string, string>> $items Raw items.
 * @return array<int, array<string, string>>
 */
function jcp_niche_normalize_core_mechanic_items( array $items ): array {
	$stat_classes = [ 'meta-stat-photo', 'meta-stat-channels', 'meta-stat-busywork' ];
	$icons        = [ 'camera', 'map', 'clock' ];
	$out          = [];

	foreach ( $items as $i => $item ) {
		if ( ! is_array( $item ) ) {
			continue;
		}
		$value  = trim( (string) ( $item['value'] ?? '' ) );
		$label  = trim( (string) ( $item['label'] ?? '' ) );
		$detail = trim( (string) ( $item['detail'] ?? '' ) );
		$combined = trim( $value . ' ' . $label );
		if ( $combined === '' && ! empty( $item['label'] ) ) {
			$combined = $label;
		}
		$out[] = [
			'icon'      => ! empty( $item['icon'] ) ? (string) $item['icon'] : ( $icons[ $i ] ?? 'check' ),
			'label'     => $combined,
			'detail'    => $detail,
			'css_class' => (string) ( $item['css_class'] ?? ( $stat_classes[ $i ] ?? '' ) ),
		];
	}

	return $out;
}

/**
 * Checklist lines inside a how-it-works step card.
 *
 * @param array<int, string> $lines       Lines.
 * @param string             $path_prefix JSON path prefix.
 */
function jcp_niche_render_step_lines( array $lines, string $path_prefix ): void {
	if ( empty( $lines ) && $path_prefix === '' ) {
		return;
	}
	?>
	<ul class="jcp-step-checklist jcp-niche-checklist"<?php
	if ( $path_prefix !== '' ) {
		jcp_niche_array_attr( $path_prefix );
	}
	?>>
		<?php foreach ( $lines as $li => $line ) : ?>
			<?php
			$text = is_array( $line ) ? (string) ( $line['text'] ?? '' ) : (string) $line;
			if ( $text === '' ) {
				continue;
			}
			$path = $path_prefix !== '' ? $path_prefix . '.' . $li : '';
			?>
			<li<?php if ( $path_prefix !== '' ) { jcp_niche_array_item_attr( (int) $li ); echo ' class="jcp-collection-item"'; } ?>>
				<span class="jcp-step-checklist__icon" aria-hidden="true">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
				</span>
				<span class="jcp-step-checklist__text"<?php if ( $path !== '' ) { jcp_niche_editable_attr( $path ); } ?>><?php echo esc_html( jcp_niche_clean_step_line( $text ) ); ?></span>
				<?php if ( $path_prefix !== '' ) { jcp_niche_collection_remove_btn( true ); } ?>
			</li>
		<?php endforeach; ?>
		<?php if ( $path_prefix !== '' ) { jcp_niche_collection_add_btn( __( '+ Add point', 'jcp-core' ) ); } ?>
	</ul>
	<?php
}

/**
 * Homepage-style core mechanic row (1 photo / 4 channels / 0 busywork).
 *
 * @param array<int, array<string, string>> $items       Items.
 * @param string                            $path_prefix JSON path prefix.
 */
function jcp_niche_render_core_mechanic_strip( array $items, string $path_prefix = 'core_mechanic' ): void {
	$normalized = jcp_niche_normalize_core_mechanic_items( $items );
	if ( empty( $normalized ) ) {
		return;
	}
	?>
	<div class="directory-meta jcp-core-mechanic-meta"<?php if ( $path_prefix !== '' ) { jcp_niche_array_attr( $path_prefix ); } ?>>
		<?php foreach ( $normalized as $i => $item ) : ?>
			<?php
			$raw = $items[ $i ] ?? [];
			if ( ! is_array( $raw ) ) {
				continue;
			}
			$icon   = (string) ( $item['icon'] ?? 'check' );
			$value  = trim( (string) ( $raw['value'] ?? '' ) );
			$word   = trim( (string) ( $raw['label'] ?? '' ) );
			$detail = (string) ( $item['detail'] ?? '' );
			$base   = $path_prefix !== '' ? $path_prefix . '.' . $i : '';
			$class  = (string) ( $item['css_class'] ?? '' );
			$combined = (string) ( $item['label'] ?? '' );
			?>
			<div class="meta-item jcp-collection-item<?php echo $class !== '' ? ' ' . esc_attr( $class ) : ''; ?>"<?php if ( $path_prefix !== '' ) { jcp_niche_array_item_attr( (int) $i ); } ?>>
				<div class="meta-label">
					<span class="factor-icon-wrapper jcp-hero-meta-icon"<?php if ( $base !== '' ) { ?> data-jcp-icon-path="<?php echo esc_attr( $base . '.icon' ); ?>" title="<?php esc_attr_e( 'Click to change icon', 'jcp-core' ); ?>" role="button" tabindex="0"<?php } ?>>
						<img src="<?php echo esc_url( jcp_core_icon( $icon ) ); ?>" class="meta-icon" alt="" width="20" height="20" />
					</span>
					<strong>
						<?php if ( $base !== '' && ( $value !== '' || $word !== '' ) ) : ?>
							<span<?php jcp_niche_editable_attr( $base . '.value' ); ?>><?php echo esc_html( $value ); ?></span><?php if ( $word !== '' ) : ?><span<?php jcp_niche_editable_attr( $base . '.label' ); ?>><?php echo esc_html( ' ' . $word ); ?></span><?php endif; ?>
						<?php else : ?>
							<?php echo esc_html( $combined ); ?>
						<?php endif; ?>
					</strong>
				</div>
				<?php if ( $detail !== '' ) : ?>
					<span class="meta-detail"<?php if ( $base !== '' ) { jcp_niche_editable_attr( $base . '.detail' ); } ?>><?php echo esc_html( $detail ); ?></span>
				<?php endif; ?>
				<?php if ( $path_prefix !== '' ) { jcp_niche_collection_remove_btn(); } ?>
			</div>
		<?php endforeach; ?>
		<?php if ( $path_prefix !== '' ) { jcp_niche_collection_add_btn( __( '+ Add stat', 'jcp-core' ) ); } ?>
	</div>
	<?php
}

/**
 * Single checkmark bullet row.
 *
 * @param string $text        Display text.
 * @param string $path        Optional JSON path for inline editor.
 * @param int    $index       Array index.
 * @param bool   $editable    Whether remove control is shown.
 */
function jcp_niche_render_conversion_point( string $text, string $path, int $index, bool $editable ): void {
	if ( $text === '' && ! $editable ) {
		return;
	}
	?>
	<div class="conversion-point jcp-collection-item"<?php if ( $editable ) { jcp_niche_array_item_attr( $index ); } ?>>
		<div class="conversion-point-icon" aria-hidden="true">
			<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
				<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
				<polyline points="22 4 12 14.01 9 11.01"/>
			</svg>
		</div>
		<div class="conversion-point-text">
			<strong<?php if ( $path !== '' ) { jcp_niche_editable_attr( $path ); } ?>><?php echo esc_html( $text !== '' ? $text : __( 'New point', 'jcp-core' ) ); ?></strong>
		</div>
		<?php if ( $editable ) { jcp_niche_collection_remove_btn(); } ?>
	</div>
	<?php
}

/**
 * Checkmark list for differentiation bullets and conversion points.
 *
 * @param array<int, string|array<string, string>> $lines        Lines.
 * @param string                                   $path_prefix JSON path prefix (e.g. conversion.points).
 * @param array<string, mixed>                     $options     {
 *   @type string $layout     stack|columns — columns fills down then across.
 *   @type int    $per_column Max items per column when layout is columns.
 * }
 */
function jcp_niche_render_conversion_points( array $lines, string $path_prefix = '', array $options = [] ): void {
	if ( empty( $lines ) && $path_prefix === '' ) {
		return;
	}

	$layout     = (string) ( $options['layout'] ?? 'stack' );
	$per_column = max( 1, min( 8, (int) ( $options['per_column'] ?? 5 ) ) );
	$columns    = $layout === 'columns';
	$editable   = $path_prefix !== '';

	$classes = 'conversion-points jcp-niche-conversion-points';
	if ( $columns ) {
		$classes .= ' conversion-points--columns jcp-niche-diff-points';
	}
	?>
	<div class="<?php echo esc_attr( $classes ); ?>"<?php
	if ( $editable ) {
		jcp_niche_array_attr( $path_prefix );
	}
	if ( $columns ) {
		printf( ' data-jcp-per-column="%d"', $per_column );
	}
	?>>
		<?php if ( $columns ) : ?>
			<div class="conversion-points__columns" style="<?php printf( '--jcp-points-per-column:%d;', $per_column ); ?>">
		<?php endif; ?>
		<?php foreach ( $lines as $i => $line ) : ?>
			<?php
			$text = is_array( $line ) ? (string) ( $line['text'] ?? '' ) : (string) $line;
			if ( $text === '' && ! $editable ) {
				continue;
			}
			$path = $editable ? $path_prefix . '.' . $i : '';
			jcp_niche_render_conversion_point( $text, $path, (int) $i, $editable );
			?>
		<?php endforeach; ?>
		<?php if ( $columns ) : ?>
			</div>
		<?php endif; ?>
		<?php if ( $editable ) { jcp_niche_collection_add_btn( __( '+ Add point', 'jcp-core' ) ); } ?>
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

/**
 * Optional primary (+ optional secondary) CTA row for any section.
 *
 * @param array<string, mixed> $props     Section props (cta_primary, cta_secondary).
 * @param string               $base_path JSON path prefix (e.g. check_ins).
 * @param string               $niche_key Page key for URL resolution.
 * @param array<string, mixed> $options   secondary (bool), secondary_kind (cta|link).
 */
function jcp_niche_render_section_optional_ctas( array $props, string $base_path, string $niche_key = '', array $options = [] ): void {
	$allow_secondary = ! empty( $options['secondary'] );
	$inline_edit     = jcp_niche_user_can_inline_edit();
	$secondary_kind  = (string) ( $options['secondary_kind'] ?? 'link' );
	$primary         = jcp_niche_resolve_cta( $props['cta_primary'] ?? [], $niche_key );
	$secondary       = jcp_niche_resolve_cta( $props['cta_secondary'] ?? [], $niche_key );
	$has_primary     = $primary['label'] !== '';
	$show_primary    = jcp_niche_show_field( $props, 'show_cta', $has_primary );
	$show_secondary  = $allow_secondary && jcp_niche_show_field( $props, 'show_cta_secondary', false );
	if ( ! $show_primary && ! $show_secondary && ! $inline_edit ) {
		return;
	}
	$primary_label   = __( 'Section button', 'jcp-core' );
	$secondary_label = __( 'Secondary link', 'jcp-core' );
	$has_secondary   = $allow_secondary && $secondary['label'] !== '';
	$row_classes     = 'jcp-section-cta-row benefits-cta-row';
	if ( $has_primary && ! $has_secondary ) {
		$row_classes .= ' jcp-section-cta-row--solo';
	}
	$row_style = ( $inline_edit && ! $show_primary && ! $show_secondary ) ? ' style="display:none"' : '';
	?>
	<div class="<?php echo esc_attr( $row_classes ); ?>"<?php echo $row_style; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php if ( $show_primary || $inline_edit ) : ?>
		<div class="benefits-cta-slot jcp-section-cta-slot"<?php jcp_niche_optional_slot_attr( $base_path . '.cta_primary', 'cta', $primary_label ); ?>>
			<?php if ( $has_primary ) : ?>
				<a href="<?php echo esc_url( $primary['url'] ); ?>" class="btn btn-primary"<?php jcp_niche_editable_link_attr( $base_path . '.cta_primary' ); ?>><?php echo esc_html( $primary['label'] ); ?></a>
			<?php endif; ?>
		</div>
		<?php endif; ?>
		<?php if ( $allow_secondary && ( ( $show_secondary && $has_secondary ) || jcp_niche_user_can_inline_edit() ) ) : ?>
			<div class="benefits-cta-slot jcp-section-cta-slot"<?php jcp_niche_optional_slot_attr( $base_path . '.cta_secondary', $secondary_kind, $secondary_label ); ?>>
				<?php if ( $secondary['label'] !== '' ) : ?>
					<a href="<?php echo esc_url( $secondary['url'] ); ?>" class="benefits-cta-link"<?php jcp_niche_editable_link_attr( $base_path . '.cta_secondary' ); ?>>
						<?php echo esc_html( $secondary['label'] ); ?>
						<?php jcp_component_chevron_svg(); ?>
					</a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
}
