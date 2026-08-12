<?php
/**
 * Sales call script — WP admin companion for side-by-side presenting.
 *
 * @package JCP_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register admin submenu.
 */
function jcp_sales_tool_register_script_page(): void {
	add_submenu_page(
		'edit.php?post_type=jcp_sales_deck',
		__( 'Call Script', 'jcp-core' ),
		__( 'Call Script', 'jcp-core' ),
		'edit_pages',
		'jcp-sales-call-script',
		'jcp_sales_tool_render_script_page'
	);
}
add_action( 'admin_menu', 'jcp_sales_tool_register_script_page' );

/**
 * Script content keyed by chapter id.
 *
 * @return list<array{id:string,label:string,goal:string,say:string,ask:string,avoid:string}>
 */
function jcp_sales_tool_call_script_chapters(): array {
	return [
		[
			'id'    => 'cover',
			'label' => '01 · Opening',
			'goal'  => 'Set the frame: their completed work should market itself.',
			'say'   => 'Your crews already finish real jobs and take photos. Today I’m going to show how JobCapturePro turns that activity into proof on your website, Google, social, and directory — and how your tech asks for a review on site with a QR code before they leave.',
			'ask'   => 'Does that match the outcome you’re trying to get more of — visibility, reviews, or both?',
			'avoid' => 'Don’t open with pricing. Don’t promise rankings.',
		],
		[
			'id'    => 'problem',
			'label' => '02 · The problem',
			'goal'  => 'Make the missed opportunity obvious without blaming them.',
			'say'   => 'Most home-service teams already have the content. It just dies on phones. One completed job can show up in five places: Google, website, social, directory, and a review ask. Right now, most of those never happen consistently.',
			'ask'   => 'Where do your job photos usually end up today — phone, CRM, CompanyCam, or nowhere?',
			'avoid' => 'Don’t say “you’re doing marketing wrong.” Keep it operational.',
		],
		[
			'id'    => 'diagnose',
			'label' => '03 · Diagnose',
			'goal'  => 'Fill the inputs live so the next chapter feels personal.',
			'say'   => 'Let’s walk your real numbers. About how many jobs a week, how often photos get taken, and how often that work actually gets published as public proof.',
			'ask'   => 'If we looked at last month, what percent of completed jobs became something a homeowner could see online?',
			'avoid' => 'Don’t over-index on CRM features. Stay on proof inventory.',
		],
		[
			'id'    => 'gap',
			'label' => '04 · Proof gap',
			'goal'  => 'Quantify unused proof. Stay honest — inventory, not lead guarantees.',
			'say'   => 'Based on what you just told me, that’s roughly [X] completed jobs a month that may never become public proof. That’s not a lead forecast — it’s proof inventory sitting unused.',
			'ask'   => 'If even half of those jobs showed up consistently online, what would that change for how you look versus competitors?',
			'avoid' => 'Never invent ROI. Never say “this will get you X leads.”',
		],
		[
			'id'    => 'engine',
			'label' => '05 · How it works',
			'goal'  => 'Show the mechanism: capture → optimize → distribute (4 channels) → convert (QR review).',
			'say'   => 'One check-in starts it. We optimize the job for search, publish to four channels — website, Google, social, directory — then your tech shows a QR code for the review before leaving. If you’re on Housecall Pro, Jobber, ServiceTitan, or CompanyCam, we plug into the tools you already use.',
			'ask'   => 'Would your techs rather tap one check-in, or keep doing separate posts and follow-ups after the job?',
			'avoid' => 'Don’t call reviews “automatic.” It’s an on-site handoff.',
		],
		[
			'id'    => 'proof',
			'label' => '06 · Proof',
			'goal'  => 'Social proof first. Acculevel only if Maps/SEO is the buying trigger.',
			'say'   => 'Here’s what operators and agencies say after they turn job photos into a system. [Read 1–2 snippets.] If local Maps coverage matters for you, we can also walk Acculevel’s LocalFalcon movement — measured visibility, not hype.',
			'ask'   => 'Which proof matters more in your market right now — reviews, Maps coverage, or consistent website content?',
			'avoid' => 'Don’t claim Acculevel lead lift unless the verified field is filled.',
		],
		[
			'id'    => 'fit',
			'label' => '07 · Right fit',
			'goal'  => 'Match altitude: owner, growth, or multi-location.',
			'say'   => 'Depending on stage, the story shifts. Owner-operators need simple capture and visibility. Growth teams need consistency across jobs. Multi-location needs control without losing local proof.',
			'ask'   => 'Which of these sounds most like how you operate today?',
			'avoid' => 'Don’t upsell Enterprise before they feel the workflow.',
		],
		[
			'id'    => 'plan',
			'label' => '08 · Plan / earn',
			'goal'  => 'Match the audience: contractor plan, affiliate economics, or partner residual.',
			'say'   => 'Contractor: recommend Starter/Scale/Enterprise from live pricing. Affiliate: 20% recurring for 12 months when someone you refer becomes a paid customer. Partner: for agencies doing real selling and support — 15% recurring for as long as that customer stays active. That’s stronger than a 12-month cut without promising 20% forever.',
			'ask'   => 'Are you referring casually, or will you sell and support this with clients?',
			'avoid' => 'Don’t invent custom commission rates live. Point partners to apply and scope terms.',
		],
		[
			'id'    => 'objections',
			'label' => '09 · Questions',
			'goal'  => 'Answer calmly. Mechanism over debate.',
			'say'   => 'These are the questions we hear most. Pick the one on their mind and answer with their workflow, not a feature dump.',
			'ask'   => 'What’s the biggest hesitation between “this makes sense” and “let’s try it”?',
			'avoid' => 'Don’t argue. Don’t guarantee rankings or lead volume.',
		],
		[
			'id'    => 'close',
			'label' => '10 · Close',
			'goal'  => 'One concrete next step + leave-behind PDF.',
			'say'   => 'Here’s what I’d recommend: start the free trial, connect one real job workflow, and we’ll confirm the rollout. I’ll download a one-pager you can share with anyone who wasn’t on the call.',
			'ask'   => 'Who else needs to see this before you start the trial — and when can we reconnect?',
			'avoid' => 'Don’t leave without a date or a clear owner of next step.',
		],
	];
}

/**
 * Objection handling scripts.
 *
 * @return list<array{title:string,say:string,proof:string}>
 */
function jcp_sales_tool_call_script_objections(): array {
	return [
		[
			'title' => 'We already have a CRM',
			'say'   => 'Good — keep it. JobCapturePro isn’t replacing scheduling. It turns completed-job activity into proof and visibility most CRMs never publish. We integrate with Housecall Pro, Jobber, ServiceTitan, CompanyCam, and more.',
			'proof' => 'Confirm their CRM, then show Capture → Distribute on the How it works chapter.',
		],
		[
			'title' => 'My techs won’t use it',
			'say'   => 'That’s why it’s a simple photo check-in — and the review is a QR they show before they leave, not another office task next week.',
			'proof' => 'Ask who owns photos today. Walk Convert step (QR).',
		],
		[
			'title' => 'We already post on social',
			'say'   => 'Helpful — social is one of four publish channels. The bigger gap is usually website proof, Google updates, directory presence, and the on-site review ask from the same job.',
			'proof' => 'Ask how often one job hits all four channels plus a review.',
		],
		[
			'title' => 'Will this guarantee rankings?',
			'say'   => 'No honest platform can. What we do is give you a steady supply of real, location-based job proof and publish it well. Results still depend on market, site, and execution.',
			'proof' => 'If relevant, show Acculevel Maps visibility — measured SoLV, not promises.',
		],
		[
			'title' => 'It feels expensive',
			'say'   => 'Compare it to the manual work it replaces and the unused proof inventory we just quantified. Pricing is on the live pricing page; trial is free for 14 days.',
			'proof' => 'Return to Proof gap numbers. Offer trial, not a discount debate.',
		],
		[
			'title' => 'We don’t have time',
			'say'   => 'That’s the problem this solves. Marketing from completed work fails when it needs spare time. Capture ties to the job; the QR review takes seconds on site.',
			'proof' => 'Confirm photo habits and who would own the first week of rollout.',
		],
	];
}

/**
 * Render admin script page.
 */
function jcp_sales_tool_render_script_page(): void {
	if ( ! current_user_can( 'edit_pages' ) ) {
		return;
	}
	$chapters   = jcp_sales_tool_call_script_chapters();
	$objections = jcp_sales_tool_call_script_objections();
	?>
	<div class="wrap jcp-sales-script">
		<style>
			.jcp-sales-script { max-width: 920px; }
			.jcp-sales-script h1 { margin-bottom: 8px; }
			.jcp-sales-script .lead { color: #50575e; margin: 0 0 20px; max-width: 62ch; }
			.jcp-sales-script .tip { padding: 12px 14px; border-left: 4px solid #ff5036; background: #fff7f5; margin-bottom: 24px; }
			.jcp-ss-card { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; padding: 18px 20px; margin-bottom: 14px; }
			.jcp-ss-card h2 { margin: 0 0 6px; font-size: 16px; }
			.jcp-ss-card .goal { color: #646970; font-size: 13px; margin: 0 0 12px; }
			.jcp-ss-card .label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #ff5036; margin: 12px 0 4px; }
			.jcp-ss-card p { margin: 0; line-height: 1.55; }
			.jcp-ss-card .avoid { color: #8a2424; }
			.jcp-ss-nav { position: sticky; top: 32px; background: #f0f0f1; padding: 10px 0 14px; z-index: 5; margin-bottom: 8px; }
			.jcp-ss-nav a { margin-right: 10px; font-size: 12px; text-decoration: none; }
		</style>
		<h1><?php esc_html_e( 'Sales call script', 'jcp-core' ); ?></h1>
		<p class="lead"><?php esc_html_e( 'Open this on a second screen while you Present the sales deck. Read the “Say” lines to the prospect. Use Ask to keep them talking. Avoid is for you only.', 'jcp-core' ); ?></p>
		<div class="tip"><?php esc_html_e( 'Presentation tip: hit Present on the deck before screenshare. Fill Diagnose inputs live — the Proof gap updates as you talk.', 'jcp-core' ); ?></div>

		<div class="jcp-ss-nav">
			<?php foreach ( $chapters as $ch ) : ?>
				<a href="#script-<?php echo esc_attr( $ch['id'] ); ?>"><?php echo esc_html( $ch['label'] ); ?></a>
			<?php endforeach; ?>
			<a href="#script-objections"><strong><?php esc_html_e( 'Objections', 'jcp-core' ); ?></strong></a>
		</div>

		<?php foreach ( $chapters as $ch ) : ?>
			<article class="jcp-ss-card" id="script-<?php echo esc_attr( $ch['id'] ); ?>">
				<h2><?php echo esc_html( $ch['label'] ); ?></h2>
				<p class="goal"><strong><?php esc_html_e( 'Goal:', 'jcp-core' ); ?></strong> <?php echo esc_html( $ch['goal'] ); ?></p>
				<span class="label"><?php esc_html_e( 'Say', 'jcp-core' ); ?></span>
				<p><?php echo esc_html( $ch['say'] ); ?></p>
				<span class="label"><?php esc_html_e( 'Ask', 'jcp-core' ); ?></span>
				<p><?php echo esc_html( $ch['ask'] ); ?></p>
				<span class="label"><?php esc_html_e( 'Avoid', 'jcp-core' ); ?></span>
				<p class="avoid"><?php echo esc_html( $ch['avoid'] ); ?></p>
			</article>
		<?php endforeach; ?>

		<h2 id="script-objections"><?php esc_html_e( 'Objection handling', 'jcp-core' ); ?></h2>
		<?php foreach ( $objections as $i => $item ) : ?>
			<article class="jcp-ss-card" id="obj-<?php echo esc_attr( (string) $i ); ?>">
				<h2><?php echo esc_html( $item['title'] ); ?></h2>
				<span class="label"><?php esc_html_e( 'Say', 'jcp-core' ); ?></span>
				<p><?php echo esc_html( $item['say'] ); ?></p>
				<span class="label"><?php esc_html_e( 'Proof move', 'jcp-core' ); ?></span>
				<p><?php echo esc_html( $item['proof'] ); ?></p>
			</article>
		<?php endforeach; ?>
	</div>
	<?php
}
