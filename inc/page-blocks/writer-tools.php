<?php
/**
 * Writer workflow helpers: presets, skeleton documents, templates.
 *
 * @package JCP_Core
 */

/**
 * Meta key for selected layout preset on block pages.
 */
function jcp_writer_layout_preset_meta_key(): string {
	return '_jcp_page_layout_preset';
}

/**
 * Resolve the active layout preset for a post.
 *
 * @param WP_Post|null         $post    Post.
 * @param array<string, mixed> $content Stored content (optional).
 */
function jcp_writer_resolve_preset( ?WP_Post $post, array $content = [] ): string {
	if ( ! empty( $content['preset'] ) ) {
		$preset = sanitize_key( (string) $content['preset'] );
		if ( jcp_page_get_preset( $preset ) ) {
			return $preset;
		}
	}

	if ( ! $post instanceof WP_Post ) {
		return 'marketing';
	}

	if ( $post->post_type === 'jcp_niche_landing' ) {
		return 'industry';
	}

	if ( get_page_template_slug( $post->ID ) === 'page-referral-program.php' || $post->post_name === 'referral-program' ) {
		return 'referral';
	}

	if ( get_page_template_slug( $post->ID ) === 'page-home.php' || (int) get_option( 'page_on_front' ) === (int) $post->ID ) {
		return 'home';
	}

	$stored = get_post_meta( $post->ID, jcp_writer_layout_preset_meta_key(), true );
	if ( is_string( $stored ) && $stored !== '' && jcp_page_get_preset( $stored ) ) {
		return sanitize_key( $stored );
	}

	if ( get_page_template_slug( $post->ID ) === 'page-jcp-blocks.php' ) {
		return 'marketing';
	}

	return 'marketing';
}

/**
 * Layout presets writers can pick on block pages.
 *
 * @return array<string, string> slug => label
 */
function jcp_writer_selectable_layout_presets(): array {
	$choices = [ 'marketing', 'features', 'comparison', 'minimal' ];
	$out     = [];
	foreach ( $choices as $slug ) {
		$def = jcp_page_get_preset( $slug );
		if ( $def ) {
			$out[ $slug ] = (string) ( $def['label'] ?? $slug );
		}
	}
	return $out;
}

/**
 * Human label for a preset slug.
 *
 * @param string $preset Preset slug.
 */
function jcp_writer_preset_label( string $preset ): string {
	$def = jcp_page_get_preset( $preset );
	return $def ? (string) ( $def['label'] ?? $preset ) : $preset;
}

/**
 * Build an empty block document for admin / new pages.
 *
 * @param WP_Post $post   Post.
 * @param string  $preset Preset slug.
 * @return array<string, mixed>
 */
function jcp_page_create_skeleton_document( WP_Post $post, string $preset ): array {
	$def = jcp_page_get_preset( $preset );
	if ( ! $def ) {
		$preset = 'marketing';
		$def    = jcp_page_get_preset( 'marketing' );
	}
	$page_kind = (string) ( $def['page_kind'] ?? 'marketing' );
	$label     = get_the_title( $post );
	$key       = $post->post_name !== '' ? $post->post_name : sanitize_title( $label );

	return [
		'version'      => 1,
		'page_kind'    => $page_kind,
		'page_key'     => $key,
		'page_label'   => $label,
		'niche_key'    => $key,
		'niche_label'  => $label,
		'preset'       => $preset,
		'blocks'       => jcp_page_blocks_from_preset( $preset ),
		'seo'          => [ 'keywords' => [] ],
		'settings'     => [ 'hide_breadcrumb' => false ],
	];
}

/**
 * JSON string for the admin editor textarea.
 *
 * @param WP_Post $post Post.
 */
function jcp_page_get_admin_editor_json( WP_Post $post ): string {
	$stored = jcp_page_get_content( (int) $post->ID );
	if ( ! empty( $stored['blocks'] ) ) {
		return wp_json_encode( $stored, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ?: '';
	}

	if ( $post->post_name === 'plumbing' ) {
		return wp_json_encode( jcp_page_legacy_to_blocks( jcp_page_load_preset( 'plumbing' ), 0 ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ?: '';
	}
	if ( $post->post_name === 'hvac' ) {
		return wp_json_encode( jcp_page_legacy_to_blocks( jcp_page_load_preset( 'hvac' ), 0 ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ?: '';
	}
	if ( get_page_template_slug( $post->ID ) === 'page-home.php' || (int) get_option( 'page_on_front' ) === (int) $post->ID ) {
		return wp_json_encode( jcp_page_legacy_to_blocks( jcp_page_load_preset( 'home' ), (int) $post->ID ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ?: '';
	}
	if ( $post->post_name === 'referral-program' || get_page_template_slug( $post->ID ) === 'page-referral-program.php' ) {
		return wp_json_encode( jcp_page_legacy_to_blocks( jcp_page_load_preset( 'referral-program' ), 0 ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ?: '';
	}

	if ( $post->post_type === 'jcp_niche_landing' || ( $post->post_type === 'page' && jcp_page_uses_block_template( (int) $post->ID ) ) ) {
		$preset   = jcp_writer_resolve_preset( $post, $stored );
		$skeleton = jcp_page_create_skeleton_document( $post, $preset );
		return wp_json_encode( $skeleton, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) ?: '';
	}

	return '';
}

/**
 * Writer document skeleton for copy/paste.
 *
 * @param string $preset Optional preset slug (industry, features, comparison, marketing, minimal).
 */
function jcp_writer_get_document_template( string $preset = 'industry' ): string {
	$preset = sanitize_key( $preset );
	if ( $preset === 'industry' && function_exists( 'jcp_theme_docs_get_industry_writer_template' ) ) {
		return jcp_theme_docs_get_industry_writer_template();
	}

	$headers = [];
	$def     = jcp_page_get_preset( $preset );
	if ( $def ) {
		foreach ( (array) ( $def['block_types'] ?? [] ) as $entry ) {
			$parsed = jcp_page_parse_preset_block_entry( $entry );
			$type   = $parsed['type'];
			if ( $type === '' || $type === 'breadcrumb' ) {
				continue;
			}
			if ( $type === 'media_text' && $parsed['legacy_key'] !== '' ) {
				$map = array_flip( jcp_page_doc_media_sections() );
				$headers[] = $map[ $parsed['legacy_key'] ] ?? 'MEDIA';
				continue;
			}
			$block = jcp_block_get( $type );
			foreach ( (array) ( $block['doc_sections'] ?? [] ) as $doc ) {
				$header = jcp_page_doc_normalize_section_header( (string) $doc );
				if ( $header ) {
					$headers[] = $header;
					break;
				}
			}
		}
	}

	$body = jcp_writer_template_body_for_headers( $headers );
	$meta = <<<'META'
Word count:

Primary Keyword: keyword one, keyword two, keyword three

SEO Title (website/blogs only):

Meta Description (website/blogs only):

Page options (set in Page Structure after import — not written in this doc):
- Breadcrumb show/hide
- Per-section background (white, off-white, dark, image + overlay, custom color with opacity)
- Show/hide subheadlines, section buttons, supporting text, and CTA notes

↓ Write Content Here ↓


META;

	return $meta . $body;
}

/**
 * Placeholder section bodies for writer templates.
 *
 * @param array<int, string> $headers Section headers.
 */
function jcp_writer_template_body_for_headers( array $headers ): string {
	$snippets = jcp_writer_template_section_snippets();
	$parts    = [];
	foreach ( $headers as $header ) {
		$parts[] = $snippets[ $header ] ?? ( $header . "\n[Content for this section]\n" );
	}
	return implode( "\n", $parts );
}

/**
 * @return array<string, string>
 */
function jcp_writer_template_section_snippets(): array {
	return [
		'HERO' => <<<'SNIP'
HERO
H1
[Main headline]
Subheadline
[Supporting paragraph]
CTA
Start free trial
See how it works
Trust Line
No credit card · Free trial · Setup in under 10 minutes
SNIP,
		'WHAT IT IS' => <<<'SNIP'
WHAT IT IS
Headline
[Section headline]
Subheadline
[Section subheadline]
[Key point one]
[Key point two]
[Key point three]
Closing Line
[Closing sentence]
CTA
[Optional — leave blank to hide]
SNIP,
		'CORE MECHANIC' => <<<'SNIP'
CORE MECHANIC
1 photo
 Proof created instantly
4 channels
 Google, website, social, directory
0 busywork
 Nothing new for your crew
SNIP,
		'HOW IT WORKS' => <<<'SNIP'
HOW IT WORKS
Headline
How it works
Subheadline
[Short subheadline]

01 Step one
 [Detail line — indent with leading space]
02 Step two
 [Detail line]
CTA
See it in action
SNIP,
		'BENEFITS' => <<<'SNIP'
BENEFITS
Headline
[Benefits headline]
Subheadline
[Benefits subheadline]

[Benefit title one]
 [Benefit body — indent with leading space]
[Benefit title two]
 [Benefit body]
CTA
[Optional]
SNIP,
		'PROBLEM' => <<<'SNIP'
PROBLEM
Headline
[Problem headline]
Subheadline
[Problem subheadline]
[Problem point one]
[Problem point two]
[Problem point three]
CTA
[Optional]
SNIP,
		'DIFFERENTIATION' => <<<'SNIP'
DIFFERENTIATION
Headline
[Why we're different]
Subheadline
[Supporting line]

[Differentiator one]
 [Detail — indent with leading space]
[Differentiator two]
 [Detail]
CTA
[Optional]
SNIP,
		"WHO IT'S FOR" => <<<'SNIP'
WHO IT'S FOR
Headline
[Audience headline]
Subheadline
[Audience subheadline]

[Audience segment one]
 [Detail]
[Audience segment two]
 [Detail]
CTA
[Optional]
SNIP,
		'FAQ' => <<<'SNIP'
FAQ
Headline
Frequently asked questions

[Question one?]
 [Answer — indent with leading space]
[Question two?]
 [Answer]
SNIP,
		'CONVERSION' => <<<'SNIP'
CONVERSION
Headline
[Conversion headline]
Subheadline
[Conversion subheadline]
[Proof point one]
[Proof point two]
[Proof point three]
CTA
Start free trial
SNIP,
		'FINAL CTA' => <<<'SNIP'
FINAL CTA
Headline
[Final headline]
Subheadline
[Final subheadline — optional; hide in Page Structure if unused]
CTA Note
[Text under the button — optional]
CTA
Start free trial
See how it works
SNIP,
		'MEDIA CORE' => <<<'SNIP'
MEDIA CORE
Headline
[Optional headline]
Subheadline
[Optional subheadline]
Body
[Optional body copy]
CTA
See how it works
SNIP,
		'CHECK-INS' => <<<'SNIP'
CHECK-INS
Headline
[Headline]
Subheadline
[Subheadline]
[Feature title]
 [Feature body — indent with leading space]
CTA
[Optional]
SNIP,
		'MEDIA CHECK-INS' => <<<'SNIP'
MEDIA CHECK-INS
Headline
[Optional headline]
Subheadline
[Optional subheadline]
Body
[Optional body]
CTA
[Optional]
SNIP,
		'MEDIA PROBLEM' => <<<'SNIP'
MEDIA PROBLEM
Headline
[Optional headline]
Subheadline
[Optional subheadline]
Body
[Optional body]
CTA
[Optional]
SNIP,
	];
}
