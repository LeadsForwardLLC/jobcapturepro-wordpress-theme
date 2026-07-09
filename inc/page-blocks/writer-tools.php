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
	if ( $post instanceof WP_Post && $post->post_type === 'page' && get_page_template_slug( $post->ID ) === 'page-jcp-blocks.php' ) {
		$stored = get_post_meta( $post->ID, jcp_writer_layout_preset_meta_key(), true );
		if ( is_string( $stored ) && $stored !== '' && jcp_page_get_preset( $stored ) ) {
			return sanitize_key( $stored );
		}
	}

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
	$choices = [ 'industry', 'marketing', 'features', 'comparison', 'minimal' ];
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
 * Shared document header: AI instructions, SEO fields, formatting rules.
 *
 * Shown at the top of every writer template. Parser stops at “write content here” or the first section.
 *
 * @param string $preset Optional layout preset slug (industry, home, referral, marketing, …).
 */
function jcp_writer_document_meta_block( string $preset = 'industry' ): string {
	$preset       = sanitize_key( $preset );
	$preset_label = jcp_writer_preset_label( $preset );
	$is_industry  = $preset === 'industry';
	$list_rule    = $is_industry
		? '- Use these exact list counts: 3 team-already bullets + 4 turns-into outputs; 4 how-it-works steps; 4–5 check-in job types + 4 check-in features; 4 pain points; 4 benefits + closing paragraph; 3–4 who-it\'s-for audience cards (with Badge); 4 FAQ questions; 4 conversion bullets'
		: '- Match the list counts shown in each section of this template (steps, bullets, cards, FAQ items, etc.)';
	$extra_rule   = '- Sections marked “optional on this page” in the admin guide can be omitted; extra ALL CAPS sections you add will import and append in the page editor';

	return <<<META
=== JCP WRITER DOCUMENT — DO NOT REMOVE LABELS ===

PAGE CONTEXT (fill before writing or pasting into ChatGPT / Claude):
- Page type: {$preset_label}
- Trade or service: [e.g. Plumbing — industry pages only]
- State/region (if applicable): [e.g. Indiana / IN]
- Primary focus keyword: [main keyword phrase]
- Secondary keywords: [2–4 related phrases, comma-separated]
- Target word count: 1,200–1,800 words (body only, excluding this header)

Word count:

Primary Keyword: primary keyword, secondary keyword, tertiary keyword

SEO Title: 50–60 characters — keyword near the start; brand only if it fits naturally
Meta Description: 140–160 characters — clear benefit + keyword + soft CTA

FORMATTING RULES FOR IMPORT (required for auto-import):
- Keep every ALL CAPS section header exactly as written (WHAT IT IS, HOW IT WORKS, etc.)
- Keep field labels exactly: H1, Headline, Subheadline, CTA, Trust Line, Closing Line, Body, Badge, Job Types, Stat Number, Stat Label, FAQ Target, Callout Badge, Callout Title, Callout Text, Link Label, Link URL
- Title + body pairs: title on one line, body on the next line with ONE leading space (indent)
- Benefit cards: short title (2–4 words), indented body, then orange keyword (1 word), then ALL CAPS tagline on the next line
{$list_rule}
{$extra_rule}
- Do not rename headers or remove placeholder structure lines

LENGTH GUIDELINES:
- H1 (hero): 8–14 words — specific to this page, not generic
- Section Headlines (H2): 6–12 words each — unique across the page; never repeat the same pattern
- Subheadlines: 1–2 sentences, max ~35 words
- Card/bullet bodies: 2–4 sentences each
- FAQ answers: 2–5 sentences each
- CTAs: 2–5 words — vary across the page (do not repeat the same CTA in every section)

VOICE, SEO & CONVERSION:
- Write like a knowledgeable contractor talking to a homeowner — not a marketer or AI
- Focus keyword must appear in: the first or second sentence of the page, the first H2, and one additional H2 — spread naturally
- Include state name and abbreviation where appropriate (e.g. Indiana / IN)
- Each section must answer a DIFFERENT reader question — no repeating the same idea in new words
- Avoid AI/templated phrasing: “In today’s digital landscape”, “seamless”, “robust”, “leverage”, “comprehensive solution”, starting every H2 with the keyword
- Company name “JobCapturePro” max 2–3 times in body copy
- One image alt text should include the keyword naturally; all alt text should sound human
- Strengthen CTAs — avoid repeating the same button label throughout the page

SELF-CHECK BEFORE SUBMIT:
[ ] Read only H1, all H2s, and CTAs — each feels specific to THIS page
[ ] No two sections explain the same thing
[ ] Keyword placement feels natural when read aloud
[ ] CTAs are varied and action-oriented
[ ] Lists use the required counts above
[ ] Meta title 50–60 chars; meta description 140–160 chars
[ ] Full page read aloud — sounds like a real contractor, not a term paper

Page options (set in Page Structure after import — not written in this doc):
- Breadcrumb show/hide
- Per-section background (white, cream, brand, photo, custom)
- Show/hide subheadlines, section buttons, supporting text, icons, and CTA notes
- Add or reorder sections in the live page editor after import

↓ Write Content Below — keep headers and labels intact ↓


META;
}

/**
 * Editorial standards for writers and AI prompts (plain text).
 */
function jcp_writer_editorial_guidelines_text(): string {
	return <<<'GUIDE'
VOICE
- Sound like a real contractor explaining the service to a potential customer — confident, plain language, useful detail
- Address real customer concerns: why the service matters, what goes wrong without it, what they should expect
- Mix short and longer sentences; avoid list-of-facts or term-paper tone
- The reader should think: “This sounds like what I’m dealing with, and this is the company that can help.”

HEADLINES & STRUCTURE
- Make every H2 unique and specific — not generic enough to work on almost any service page
- Each section answers a different question; if two sections overlap, combine or rewrite one
- Quick test: read only H1, H2s, and CTAs — if they sound interchangeable with another page, add more specificity

KEYWORDS & SEO
- Spread keywords naturally throughout the page
- Hard rule: keyword in the first H2, in the first or second sentence of the page, and in one additional H2 — while meeting minimum keyword density
- Vary placement page to page; do not always put the keyword in the same spots
- Use state and state abbreviation variations when appropriate

WHAT TO AVOID
- Templated writing patterns and common AI wording
- Starting every section or heading with the keyword
- Repeating the same CTA throughout the page
- Reusing the same structure on every trade page with only find-and-replace edits

META & MEDIA
- SEO title: 50–60 characters; meta description: 140–160 characters
- Images should match the specific service; one alt text includes the keyword naturally
- All image alt text should sound natural, not stuffed

FINAL PASS
- ChatGPT/Claude is a drafting tool — refine and customize every page before submit
- Read the full page for flow, sense, and natural language before importing
GUIDE;
}

/**
 * List counts referenced in templates and AI prompts.
 *
 * @return array<string, int|string>
 */
function jcp_writer_document_list_counts(): array {
	return [
		'what_it_is_team_already' => 3,
		'what_it_is_turns_into'   => 4,
		'how_it_works_steps'      => 4,
		'check_in_features'       => 4,
		'problem_pain_points'     => 4,
		'benefits_items'          => 4,
		'who_its_for_segments'    => 4,
		'faq_questions'           => 4,
		'conversion_bullets'      => 4,
		'core_mechanic_stats'     => 3,
	];
}

/**
 * Full industry trade page body (section placeholders).
 */
function jcp_writer_get_industry_template_body(): string {
	return <<<'BODY'
HERO
H1
[H1 — 8–14 words, specific to trade/location; include keyword naturally if it fits]
Subheadline
[1–2 sentences, max ~35 words — keyword in first or second sentence of the page]
CTA
Start free trial
See how it works
Trust Line
No credit card · Free trial · Setup in under 10 minutes

WHAT IT IS
Headline
[H2 — 6–12 words; first H2 must include focus keyword naturally]
Subheadline
[1–2 sentences — new angle, not a repeat of the hero]

Most [trade] companies are already:
[team-already bullet 1]
[team-already bullet 2]
[team-already bullet 3]
But very little of that work actually shows up online consistently.
JobCapturePro fixes that.
It turns real job activity into:
[turns-into output 1]
[turns-into output 2]
[turns-into output 3]
[turns-into output 4]
automatically.

Closing Line
[Closing sentence — ties section together]

CTA
[Optional — 2–5 words; leave blank to hide section button]

CORE MECHANIC
1 photo
 Proof created instantly
4 channels
 Google, website, social, directory
0 busywork
 Nothing new for your crew

MEDIA CORE
Headline
[Optional — auto-filled from What It Is if omitted]
Subheadline
[Optional subheadline]
Body
[Optional body copy — 2–3 sentences]
CTA
See how it works
Badge
[Optional badge label, e.g. Live Demo]

HOW IT WORKS
Headline
[H2 — 6–12 words; include keyword in this or another H2 on the page]
Subheadline
Four steps. One app. Zero busywork for your crew

01 Capture
[Step one line one]
 [Step one line two — indent with leading space]
 [Step one line three]

02 Check-In
[Step two title line]
 [Step two body — indent with leading space]

03 Publish
That job becomes live proof across:
Google Business Profile
 Your website
 Social channels
 Contractor directory
[Additional publish line if needed]

04 Review
[Step four title line]
 [Step four body — indent with leading space]

CTA
See it in action

CHECK-INS
Headline
[H2 — unique, specific to check-ins/value]
Subheadline
[1–2 sentences]

Job Types
[Job type tag one]
[Job type tag two]
[Job type tag three]
[Job type tag four]
[Job type tag five]

[Feature title one]
 [Feature body — 2–4 sentences; indent with leading space]
[Feature title two]
 [Feature body]
[Feature title three]
 [Feature body]
[Feature title four]
 [Feature body]

Closing Line
[Closing paragraph — 1–2 sentences]

CTA
[Optional — 2–5 words; leave blank to hide]

MEDIA CHECK-INS
Headline
[Optional — auto-filled from Check-Ins if omitted]
Body
[Optional supporting copy]

PROBLEM
Headline
[H2 — customer pain angle, unique wording]
Subheadline
[1–2 sentences]

[Pain point title one]
 [Pain point body — 2–4 sentences]
[Pain point title two]
 [Pain point body]
[Pain point title three]
 [Pain point body]
[Pain point title four]
 [Pain point body]

Closing Line
[Closing sentence one. Closing sentence two.]

CTA
[Optional — varied CTA text]

MEDIA PROBLEM
Headline
[Optional — auto-filled from Problem if omitted]
Subheadline
[Optional]
Body
[Optional closing / supporting copy]

BENEFITS
Headline
[H2 — outcome-focused, not generic]
Subheadline
[Optional 1–2 sentence subheadline]

[Benefit title — 2–4 words]
 [Benefit body — 1–2 sentences; indent with leading space]
[Orange keyword — 1 word]
[ALL CAPS tagline — e.g. FROM REAL JOBS]

[Benefit title two]
 [Benefit body]
[Orange keyword]
[ALL CAPS tagline]

[Benefit title three]
 [Benefit body]
[Orange keyword]
[ALL CAPS tagline]

[Benefit title four]
 [Benefit body]
[Orange keyword]
[ALL CAPS tagline]

Closing Line
[Closing paragraph — 2–3 sentences tying benefits together]

CTA
[Optional primary button — 2–5 words]
[Optional secondary link — different wording from other CTAs]

DIFFERENTIATION
Headline
[H2 — why this approach is different for this trade]

[Body paragraph line one]
[Body paragraph line two]
[Body paragraph line three]

No new process
 No extra admin
 No marketing workload

CTA
[Optional — leave blank to hide]

WHO IT'S FOR
Headline
[H2 — audience-specific]
Subheadline
[Optional supporting line — 1–2 sentences]

[Audience segment one]
 [Audience body — 2–4 sentences; indent with leading space]
Badge
[For Owners / For Technicians / etc.]
Stat Number
[Optional — e.g. 100%]
Stat Label
[Optional — ALL CAPS tagline, e.g. AUTOMATED]
FAQ Target
[Optional anchor id — e.g. faq-visibility-proof]

[Audience segment two]
 [Audience body]
Badge
[For …]
Stat Number
[Optional]
Stat Label
[Optional]
FAQ Target
[Optional]

[Audience segment three]
 [Audience body]
Badge
[For …]

[Audience segment four]
 [Audience body]
Badge
[For …]

CTA
[Optional — leave blank to hide]

FAQ
Headline
Common questions from [trade] companies

[Question one ending with ?]
 [Answer — 2–5 sentences; indent optional for multi-line]
[Question two ending with ?]
 [Answer]
[Question three ending with ?]
 [Answer]
[Question four ending with ?]
 [Answer]

CTA
[Optional — leave blank to hide]

CONVERSION
Headline
[H2 — proof/social proof angle]
Subheadline
[Supporting paragraph — 2–3 sentences]
[Checklist bullet one]
[Checklist bullet two]
[Checklist bullet three]
[Checklist bullet four]
CTA
See how this works for your business

FINAL CTA
Headline
[Final headline — 6–12 words]
Subheadline
[Final subheadline — optional; hide in Page Structure if unused]
CTA Note
[Text under the button — optional]

CTA
Start free trial
See how it works
BODY;
}

/**
 * Build a ready-to-paste AI prompt for ChatGPT / Claude.
 *
 * @param string        $preset Preset slug.
 * @param WP_Post|null  $post   Optional post for page title/slug context.
 */
function jcp_writer_get_ai_prompt( string $preset = 'industry', ?WP_Post $post = null ): string {
	$preset       = sanitize_key( $preset );
	$preset_label = jcp_writer_preset_label( $preset );
	$page_title   = $post instanceof WP_Post ? get_the_title( $post ) : '[Page title]';
	$page_slug    = $post instanceof WP_Post && $post->post_name !== '' ? $post->post_name : '[url-slug]';
	$guidelines   = jcp_writer_editorial_guidelines_text();
	$counts       = jcp_writer_document_list_counts();
	$template     = jcp_writer_get_document_template( $preset );
	$count_lines  = sprintf(
		"- What It Is: %d team-already bullets + %d turns-into outputs\n- How It Works: %d steps\n- Check-Ins: %d features\n- Problem: %d pain points\n- Benefits: %d cards (each with orange keyword + ALL CAPS tagline) + closing paragraph\n- Who It's For: %d segments\n- FAQ: %d Q&As\n- Conversion: %d bullets",
		$counts['what_it_is_team_already'],
		$counts['what_it_is_turns_into'],
		$counts['how_it_works_steps'],
		$counts['check_in_features'],
		$counts['problem_pain_points'],
		$counts['benefits_items'],
		$counts['who_its_for_segments'],
		$counts['faq_questions'],
		$counts['conversion_bullets']
	);

	$intro = <<<PROMPT
You are an expert copywriter for JobCapturePro, a SaaS product for home service contractors (plumbing, HVAC, roofing, electrical, etc.). Fill in the writer document template below for ONE page.

BEFORE YOU START — replace every bracket placeholder and confirm these details:
- Page type: {$preset_label}
- Page title: {$page_title}
- URL slug: {$page_slug}
- Trade/service: [FILL IN — e.g. Plumbing]
- State/region (if applicable): [FILL IN — e.g. Indiana / IN]
- Primary focus keyword: [FILL IN]
- Secondary keywords: [FILL IN — comma-separated]
- Target word count: 1,200–1,800 words (body only)

EDITORIAL STANDARDS (non-negotiable):
{$guidelines}

OUTPUT REQUIREMENTS:
1. Return ONLY the filled writer document — no commentary, preamble, or markdown fences before or after
2. Keep every ALL CAPS section header and field label EXACTLY as in the template
3. Use these exact list counts:
{$count_lines}
4. H1: 8–14 words; each H2: 6–12 words — every H2 must be unique and specific to this trade/location
5. Keyword placement: first or second sentence of page, first H2, and one additional H2 — natural, not forced
6. Vary CTAs (2–5 words each) — do not repeat the same button label in every section
7. JobCapturePro: max 2–3 mentions in body copy
8. SEO Title: 50–60 characters; Meta Description: 140–160 characters (fill header fields)
9. Write so a real contractor would say it to a customer — not like a blog post or term paper
10. Each section answers a different reader question; cut or rewrite overlap

AFTER GENERATING — the human writer must read aloud, edit for flow, and customize so the page could NOT be reused on another trade with find-and-replace.

TEMPLATE TO FILL:
PROMPT;

	return $intro . "\n\n" . $template;
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
	$meta   = jcp_writer_document_meta_block( $preset );

	if ( $preset === 'industry' ) {
		return $meta . jcp_writer_get_industry_template_body();
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

	return $meta . jcp_writer_template_body_for_headers( $headers );
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
[H1 — 8–14 words, specific to this page]
Subheadline
[1–2 sentences — keyword in first or second sentence of the page]
CTA
Start free trial
See how it works
Trust Line
No credit card · Free trial · Setup in under 10 minutes
SNIP,
		'WHAT IT IS' => <<<'SNIP'
WHAT IT IS
Headline
[H2 — 6–12 words; first H2 includes focus keyword naturally]
Subheadline
[1–2 sentences — new angle, not a repeat of the hero]

Most [audience] are already:
[team-already bullet 1]
[team-already bullet 2]
[team-already bullet 3]
But very little of that work actually shows up online consistently.
JobCapturePro fixes that.
It turns real job activity into:
[turns-into output 1]
[turns-into output 2]
[turns-into output 3]
[turns-into output 4]
automatically.

Closing Line
[Closing sentence — ties section together]
CTA
[Optional — 2–5 words; leave blank to hide section button]
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
[H2 — 6–12 words]
Subheadline
[Short subheadline — 1–2 sentences]

01 Capture
 [Step one line one]
 [Step one line two — indent with leading space]

02 Check-In
 [Step two title line]
 [Step two body — indent with leading space]

03 Publish
That job becomes live proof across:
Google Business Profile
 Your website
 Social channels
 Contractor directory
[Additional publish line if needed]

04 Review
 [Step four title line]
 [Step four body — indent with leading space]

CTA
See it in action
SNIP,
		'BENEFITS' => <<<'SNIP'
BENEFITS
Headline
[H2 — 6–12 words]
Subheadline
[Optional subheadline — 1–2 sentences]

[Benefit title — 2–4 words]
 [Benefit body — 1–2 sentences; indent with leading space]
[Orange keyword — 1 word]
[ALL CAPS tagline — e.g. FROM REAL JOBS]

[Benefit title two]
 [Benefit body]
[Orange keyword]
[ALL CAPS tagline]

[Benefit title three]
 [Benefit body]
[Orange keyword]
[ALL CAPS tagline]

[Benefit title four]
 [Benefit body]
[Orange keyword]
[ALL CAPS tagline]

Closing Line
[Closing paragraph — 2–3 sentences]

CTA
[Optional primary — 2–5 words]
[Optional secondary link]
SNIP,
		'PROBLEM' => <<<'SNIP'
PROBLEM
Headline
[H2 — 6–12 words]
Subheadline
[Problem subheadline — 1–2 sentences]
[Problem point one]
 [Problem body — indent with leading space]
[Problem point two]
 [Problem body]
[Problem point three]
 [Problem body]
[Problem point four]
 [Problem body]

Closing Line
[Closing sentence one. Closing sentence two.]

CTA
[Optional]
SNIP,
		'DIFFERENTIATION' => <<<'SNIP'
DIFFERENTIATION
Headline
[H2 — 6–12 words]

[Body paragraph line one]
[Body paragraph line two]
[Body paragraph line three]

No new process
 No extra admin
 No marketing workload

CTA
[Optional]
SNIP,
		"WHO IT'S FOR" => <<<'SNIP'
WHO IT'S FOR
Headline
[H2 — 6–12 words]
Subheadline
[Audience subheadline — optional]

[Audience segment one]
 [Detail — 2–4 sentences; indent with leading space]
Badge
[For Owners / For Technicians / etc.]
Stat Number
[Optional — e.g. 100%]
Stat Label
[Optional — ALL CAPS tagline]
FAQ Target
[Optional anchor id]

[Audience segment two]
 [Detail]
Badge
[For …]

[Audience segment three]
 [Detail]
Badge
[For …]

[Audience segment four]
 [Detail]
Badge
[For …]

CTA
[Optional]
SNIP,
		'FAQ' => <<<'SNIP'
FAQ
Headline
Frequently asked questions

[Question one?]
 [Answer — 2–5 sentences; indent with leading space]
[Question two?]
 [Answer]
[Question three?]
 [Answer]
[Question four?]
 [Answer]

CTA
[Optional — leave blank to hide]
SNIP,
		'CONVERSION' => <<<'SNIP'
CONVERSION
Headline
[H2 — 6–12 words]
Subheadline
[Conversion subheadline — 2–3 sentences]
[Proof point one]
[Proof point two]
[Proof point three]
[Proof point four]
CTA
See how this works for your business
SNIP,
		'FINAL CTA' => <<<'SNIP'
FINAL CTA
Headline
[Final headline — 6–12 words]
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
[Optional body copy — 2–3 sentences]
CTA
See how it works
SNIP,
		'CHECK-INS' => <<<'SNIP'
CHECK-INS
Headline
[H2 — 6–12 words]
Subheadline
[Subheadline — 1–2 sentences]

Job Types
[Job type tag one]
[Job type tag two]
[Job type tag three]
[Job type tag four]

[Feature title one]
 [Feature body — indent with leading space]
[Feature title two]
 [Feature body]
[Feature title three]
 [Feature body]
[Feature title four]
 [Feature body]

Closing Line
[Closing paragraph — 1–2 sentences]

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
		'DEMO PREVIEW' => <<<'SNIP'
DEMO PREVIEW
Badge
Live Demo
Headline
[H2 — demo angle, 6–12 words]
Body
[2–3 sentences — what the interactive demo shows]
Cue
[One-line lead before the demo button]
CTA Note
No signup required • Takes 2 minutes
CTA
Launch Interactive Demo
SNIP,
		'PROOF FLOW' => <<<'SNIP'
PROOF FLOW
Headline
[H2 — proof / channel flow angle]
Subheadline
[1–2 sentences]

Google Business Profile
 Published as a real job update on Google
Website
 Automatically added as live job content
Social Media
 Shared as job proof on social channels
Reviews
 Auto review collection

Callout Badge
Verified Job Proof
Callout Title
[Callout headline — directory / proof angle]
Callout Text
[2–3 sentences supporting the callout]
Link Label
Learn more about the directory
Link URL
#directory-preview
SNIP,
		'DIRECTORY PREVIEW' => <<<'SNIP'
DIRECTORY PREVIEW
Headline
[H2 — directory value proposition]
Subheadline
[1–2 sentences]

Summit Roofing
 Austin, TX | 82 jobs | 4.9 (120)
Your Business
 Your City, ST | 64 jobs | 4.8 (98)
Heritage Fence Co.
 Houston, TX | 41 jobs | 4.7 (64)

Outro
[Closing line under the cards]
CTA
See how your listing looks in the demo
SNIP,
		'CTA BAND' => <<<'SNIP'
CTA BAND
CTA
[Primary mid-page button — 2–5 words]
SNIP,
		'COMMISSION' => <<<'SNIP'
COMMISSION
Headline
Commission Details
Subheadline
[Program tier label]
Body
[Commission terms — 1–2 sentences]
Starter | $99/mo | $19.80/mo | $237.60
Scale | $249/mo | $49.80/mo | $597.60
Enterprise | $399/mo | $79.80/mo | $957.60
Footnote
[Legal / payout disclaimer]
CTA
Join the Referral Program
SNIP,
		'PARTNERS' => <<<'SNIP'
PARTNERS
Headline
[H2 — agency / partner angle]
Body
[2–4 sentences — who qualifies and what deeper partnership means]
CTA
Apply as a Partner
SNIP,
		'SHARE' => <<<'SNIP'
SHARE
Headline
[H2 — easy ways to share]
Body
[Intro sentence before the sample message]
Quote
[Sample message writers can copy/paste to refer someone]
Note
[Optional tip — e.g. link to demo first]
CTA
Join the Referral Program
View the live demo
SNIP,
	];
}
