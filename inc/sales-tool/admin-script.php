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
 * Script content keyed by chapter id, with mode-specific say lines.
 *
 * @return list<array{id:string,label:string,goal:string,say:string,say_affiliate:string,say_partner:string,ask:string,ask_affiliate?:string,ask_partner?:string,avoid:string}>
 */
function jcp_sales_tool_call_script_chapters(): array {
	return [
		[
			'id'            => 'cover',
			'label'         => '01 · Opening',
			'goal'          => 'Set the frame for this audience — contractor buyer, affiliate referrer, or partner seller.',
			'say'           => 'Your crews already finish real jobs and take photos. Today I’m going to show how JobCapturePro turns that into local Maps visibility, geotagged website content, social, directory proof, and an on-site QR review ask. That’s the credibility Google, neighbors, and AI search answers all reward.',
			'say_affiliate' => 'You already know contractors who finish jobs and take photos. JobCapturePro turns that into local Maps visibility, geotagged website proof, and reviews — and you earn 20% recurring for 12 months when they become paid customers. I’ll show you the product story you can repeat.',
			'say_partner'   => 'Your clients already create the assets — completed jobs and photos. JobCapturePro turns that into Maps visibility, geotagged local-SEO website content, reviews, and social so you can deliver proof without becoming their content team. Partners earn 15% residual while accounts stay active.',
			'ask'           => 'Does that match the outcome you’re trying to get more of — local leads, Maps visibility, reviews, or all three?',
			'ask_affiliate' => 'Who in your network already takes job photos but barely shows up on Maps or their website?',
			'ask_partner'   => 'Which client would you put on a pilot first — and what are they paying you for visibility today?',
			'avoid'         => 'Don’t open with pricing. Don’t promise rankings.',
		],
		[
			'id'            => 'problem',
			'label'         => '02 · The problem',
			'goal'          => 'Make the missed opportunity obvious without blaming them.',
			'say'           => 'Most home-service teams already have the content. It just dies on phones. One completed job can show up in five places: Google Maps, a local-SEO website page, social, directory, and a review ask. Local search and AI answers want real work and real proof — and most shops never publish it consistently.',
			'say_affiliate' => 'The people you refer already have the content. It dies on phones. Your pitch is simple: one job can hit Maps, their website (geotagged), social, directory, and a review ask — the proof local search and AI answers reward.',
			'say_partner'   => 'Your clients already have the content. It dies on phones and in CRMs. One job can become Maps posts, geotagged website proof, social, directory, and a review ask — without you writing posts by hand.',
			'ask'           => 'Where do your job photos usually end up today — phone, CRM, CompanyCam, or nowhere?',
			'ask_affiliate' => 'When you talk to contractors, where do their job photos usually die?',
			'ask_partner'   => 'For your typical client, who owns publishing after a job — office, marketing, or nobody?',
			'avoid'         => 'Don’t say “you’re doing marketing wrong.” Keep it operational.',
		],
		[
			'id'            => 'diagnose',
			'label'         => '03 · Diagnose',
			'goal'          => 'Fill the inputs live so the next chapter feels personal.',
			'say'           => 'Let’s walk your real numbers. About how many jobs a week, how often photos get taken, and how often that work actually gets published as public proof.',
			'say_affiliate' => 'Let’s use a typical contractor you’d refer. Jobs per week, photo capture, and how often that work becomes public proof — so your pitch has a concrete gap number.',
			'say_partner'   => 'Let’s use a real or typical client. Jobs per week, photo capture, and publishing habits — so the rest of the deck is about their workflow.',
			'ask'           => 'If we looked at last month, what percent of completed jobs became something a homeowner could see online?',
			'avoid'         => 'Don’t over-index on CRM features. Stay on proof inventory.',
		],
		[
			'id'            => 'gap',
			'label'         => '04 · Proof gap',
			'goal'          => 'Quantify unused proof. Stay honest — inventory, not lead guarantees.',
			'say'           => 'Based on what you just told me, that’s roughly [X] completed jobs a month that may never become public proof. That’s not a lead forecast — it’s proof inventory sitting unused.',
			'say_affiliate' => 'Based on that example shop, that’s roughly [X] jobs a month that may never become public proof. That’s the story you tell when you refer — inventory left on the table, not a lead promise.',
			'say_partner'   => 'Based on that client example, that’s roughly [X] jobs a month that may never become public proof. That’s the delivery opportunity you can productize.',
			'ask'           => 'If even half of those jobs showed up consistently online, what would that change for how you look versus competitors?',
			'ask_affiliate' => 'Would that gap number make your referral conversation easier?',
			'ask_partner'   => 'Would that gap change how you scope a monthly marketing retainer?',
			'avoid'         => 'Never invent ROI. Never say “this will get you X leads.”',
		],
		[
			'id'            => 'engine',
			'label'         => '05 · How it works',
			'goal'          => 'Show the mechanism: capture → optimize → distribute (4 channels) → convert (QR review).',
			'say'           => 'One check-in starts it. We geotag images to the actual job location, build local SEO into the content, then publish to four channels — website, Google Maps / GBP, social, directory — and your tech shows a QR for the review before leaving. Website content is optimized for local search in a way most platforms simply don’t do.',
			'say_affiliate' => 'Same story you’ll repeat: one check-in, geotagged local-SEO website content, Maps/GBP, social, directory, then a QR review ask on site. That’s why it’s easy to recommend.',
			'say_partner'   => 'Same delivery motion for clients: one check-in, geotagged local-SEO website content, Maps/GBP, social, directory, then a QR review ask on site — tied to job completion, not spare marketing time.',
			'ask'           => 'Would your techs rather tap one check-in, or keep doing separate posts and follow-ups after the job?',
			'ask_affiliate' => 'Can you explain that in under 30 seconds to someone you refer?',
			'ask_partner'   => 'Could your team roll this out without adding a content hire?',
			'avoid'         => 'Don’t call reviews “automatic.” It’s an on-site handoff.',
		],
		[
			'id'            => 'proof',
			'label'         => '06 · Proof',
			'goal'          => 'Social proof first. Acculevel only if Maps/SEO is the buying trigger.',
			'say'           => 'Here’s what operators and agencies say after they turn job photos into a system. [Read 1–2 snippets.] If local Maps coverage matters for leads, we can also walk Acculevel’s LocalFalcon movement — measured Share of Local Voice (SoLV): how often they rank in Google’s local 3-Pack across a map-grid scan. Visibility, not hype.',
			'say_affiliate' => 'Here’s proof you can forward. [Read 1–2 snippets.] If Maps is the buying trigger for someone you refer, Acculevel’s LocalFalcon scans show measured SoLV movement — Share of Local Voice, or how often they hit the Google 3-Pack across a grid scan.',
			'say_partner'   => 'Here’s proof for a client pitch. [Read 1–2 snippets.] If Maps/SEO is the buying trigger, Acculevel LocalFalcon scans show measured SoLV (Share of Local Voice: 3-Pack presence across a geographic grid) — use only when relevant.',
			'ask'           => 'Which proof matters more in your market right now — reviews, Maps coverage that drives leads, or geotagged website content?',
			'avoid'         => 'Don’t claim Acculevel lead lift unless the verified field is filled.',
		],
		[
			'id'            => 'fit',
			'label'         => '07 · Right fit',
			'goal'          => 'Match altitude: owner, growth, or multi-location — for them, who they refer, or who they serve.',
			'say'           => 'Depending on stage, the story shifts. Owner-operators need simple capture and visibility. Growth teams need consistency across jobs. Multi-location needs control without losing local proof.',
			'say_affiliate' => 'Match the pitch to who you refer. Owner-operators want simple Maps and website proof. Growth shops need CRM automation and reviews. Multi-location needs control across markets.',
			'say_partner'   => 'Match the rollout to the client type. Owner-operators need simple capture. Growth clients need consistency. Multi-location needs org control without losing local proof.',
			'ask'           => 'Which of these sounds most like how you operate today?',
			'ask_affiliate' => 'Which stage sounds most like the contractors you send over?',
			'ask_partner'   => 'Which stage sounds most like your typical client?',
			'avoid'         => 'Don’t upsell Enterprise before they feel the workflow.',
		],
		[
			'id'            => 'plan',
			'label'         => '08 · Plan / earn',
			'goal'          => 'Match the audience: contractor plan, affiliate economics, or partner residual.',
			'say'           => 'Based on locations and how automated you want this, here’s the recommended plan from live pricing — Starter, Scale, or Enterprise. Additional locations are priced clearly on the pricing page.',
			'say_affiliate' => 'Your motion is referral: 20% recurring commission for 12 months when someone you refer becomes a paid customer. Share your link, point them at the trial or demo, and let the product close.',
			'say_partner'   => 'Your motion is sell-and-support: 15% recurring for as long as the customer stays an active paid account. That’s stronger than a 12-month affiliate cut when you’re doing the heavy lifting. Pilot one client, prove it, expand.',
			'ask'           => 'Does this plan match how you want to run the next 90 days?',
			'ask_affiliate' => 'Are you ready to join the referral program and share with one contractor this week?',
			'ask_partner'   => 'Are you referring casually, or will you sell and support this with clients?',
			'avoid'         => 'Don’t invent custom commission rates live. Point partners to apply and scope terms.',
		],
		[
			'id'            => 'objections',
			'label'         => '09 · Questions',
			'goal'          => 'Answer calmly. Mechanism over debate. Use the mode-specific objection list on the deck.',
			'say'           => 'These are the questions we hear most from contractors. Pick the one on their mind and answer with their workflow, not a feature dump.',
			'say_affiliate' => 'These are the questions affiliates hear when recommending JobCapturePro. Keep answers short: referral economics, easy pitch, honest about rankings.',
			'say_partner'   => 'These are the questions agencies hear when pitching clients. Stay on delivery leverage, residual economics, and tech adoption.',
			'ask'           => 'What’s the biggest hesitation between “this makes sense” and “let’s try it”?',
			'avoid'         => 'Don’t argue. Don’t guarantee rankings or lead volume.',
		],
		[
			'id'            => 'close',
			'label'         => '10 · Close',
			'goal'          => 'One concrete next step + leave-behind PDF with live call numbers.',
			'say'           => 'Here’s what I’d recommend: start the free trial, connect one real job workflow, and we’ll confirm the rollout. I’ll download a one-pager with your numbers you can share with anyone who wasn’t on the call.',
			'say_affiliate' => 'Next step: join the referral program and share your link with one contractor this week. I’ll download an affiliate leave-behind with the example shop numbers from this call.',
			'say_partner'   => 'Next step: apply as a strategic partner and pick one client for a pilot. I’ll download a partner leave-behind with the client example numbers from this call.',
			'ask'           => 'Who else needs to see this before you start the trial — and when can we reconnect?',
			'ask_affiliate' => 'Who’s the first contractor you’ll share this with — and when?',
			'ask_partner'   => 'Which client is the pilot, and who signs off on partner terms?',
			'avoid'         => 'Don’t leave without a date or a clear owner of next step.',
		],
	];
}

/**
 * Objection handling scripts (contractor-facing default + notes for other modes).
 *
 * @return list<array{title:string,say:string,proof:string,mode:string}>
 */
function jcp_sales_tool_call_script_objections(): array {
	return [
		[
			'mode'  => 'contractor',
			'title' => 'We already have a CRM',
			'say'   => 'Good — keep it. JobCapturePro isn’t replacing scheduling. It turns completed-job activity into Maps posts, geotagged website content, and reviews most CRMs never publish.',
			'proof' => 'Confirm their CRM, then show Capture → Distribute on the How it works chapter.',
		],
		[
			'mode'  => 'contractor',
			'title' => 'My techs won’t use it',
			'say'   => 'That’s why it’s a simple photo check-in — and the review is a QR they show before they leave, not another office task next week.',
			'proof' => 'Ask who owns photos today. Walk Convert step (QR).',
		],
		[
			'mode'  => 'contractor',
			'title' => 'Will this guarantee rankings?',
			'say'   => 'No honest platform can. What we do is give you a steady supply of what today’s search — including AI answers — rewards: real jobs, geotagged proof, reviews, and local credibility.',
			'proof' => 'If relevant, show Acculevel Maps visibility — explain SoLV briefly (3-Pack share across a Local Falcon grid), then show measured movement, not promises.',
		],
		[
			'mode'  => 'affiliate',
			'title' => 'Will contractors actually buy?',
			'say'   => 'You’re recommending something they already do — finish jobs and take photos. Point them at the free trial; you earn when they become a paid customer.',
			'proof' => 'Walk Opening + Engine in Affiliate mode. Offer demo link.',
		],
		[
			'mode'  => 'affiliate',
			'title' => 'How do I get paid?',
			'say'   => 'Join the referral program, share your link, and earn 20% recurring for 12 months on paid accounts under program terms.',
			'proof' => 'Open Plan / Earn chapter in Affiliate mode. Show commission table.',
		],
		[
			'mode'  => 'partner',
			'title' => 'How do partner economics work?',
			'say'   => 'Strategic partners who sell and support earn 15% residual while the customer stays an active paid account — stronger than a 12-month affiliate cut when you’re doing the heavy lifting.',
			'proof' => 'Open Plan / Earn in Partner mode. Walk Pilot → Prove → Expand.',
		],
		[
			'mode'  => 'partner',
			'title' => 'We don’t have bandwidth',
			'say'   => 'Start with one pilot client, confirm integrations, prove Maps/proof consistency, then expand.',
			'proof' => 'Return to Diagnose/Gap with their client example numbers.',
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
			.jcp-sales-script { max-width: 960px; }
			.jcp-sales-script h1 { margin-bottom: 8px; }
			.jcp-sales-script .lead { color: #50575e; margin: 0 0 20px; max-width: 70ch; }
			.jcp-sales-script .tip { padding: 12px 14px; border-left: 4px solid #ff5036; background: #fff7f5; margin-bottom: 24px; }
			.jcp-ss-card { background: #fff; border: 1px solid #dcdcde; border-radius: 8px; padding: 18px 20px; margin-bottom: 14px; }
			.jcp-ss-card h2 { margin: 0 0 6px; font-size: 16px; }
			.jcp-ss-card .goal { color: #646970; font-size: 13px; margin: 0 0 12px; }
			.jcp-ss-card .label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #ff5036; margin: 12px 0 4px; }
			.jcp-ss-card p { margin: 0; line-height: 1.55; }
			.jcp-ss-card .avoid { color: #8a2424; }
			.jcp-ss-modes { display: grid; gap: 10px; margin-top: 8px; }
			.jcp-ss-mode { padding: 12px 14px; border-radius: 8px; background: #f6f7f7; border: 1px solid #e2e4e7; }
			.jcp-ss-mode strong { display: block; margin-bottom: 6px; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #1d2327; }
			.jcp-ss-nav { position: sticky; top: 32px; background: #f0f0f1; padding: 10px 0 14px; z-index: 5; margin-bottom: 8px; }
			.jcp-ss-nav a { margin-right: 10px; font-size: 12px; text-decoration: none; }
			.jcp-ss-badge { display: inline-block; margin-left: 8px; padding: 2px 8px; border-radius: 999px; background: #111827; color: #fff; font-size: 10px; text-transform: uppercase; }
		</style>
		<h1><?php esc_html_e( 'Sales call script', 'jcp-core' ); ?></h1>
		<p class="lead"><?php esc_html_e( 'Open this on a second screen while you Present the sales deck. Switch the deck to Contractor, Affiliate, or Partner — then read the matching Say line below. Download PDF on the close chapter includes live gap numbers from Diagnose.', 'jcp-core' ); ?></p>
		<div class="tip"><?php esc_html_e( 'Presentation tip: hit Present before screenshare. On the last step, Present keeps the summary full-width. Fill Diagnose live so the leave-behind PDF has real jobs/month and underused-proof numbers.', 'jcp-core' ); ?></div>

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
				<div class="jcp-ss-modes">
					<div class="jcp-ss-mode">
						<strong><?php esc_html_e( 'Contractor — Say', 'jcp-core' ); ?></strong>
						<p><?php echo esc_html( $ch['say'] ); ?></p>
						<span class="label"><?php esc_html_e( 'Ask', 'jcp-core' ); ?></span>
						<p><?php echo esc_html( $ch['ask'] ); ?></p>
					</div>
					<div class="jcp-ss-mode">
						<strong><?php esc_html_e( 'Affiliate — Say', 'jcp-core' ); ?></strong>
						<p><?php echo esc_html( $ch['say_affiliate'] ); ?></p>
						<span class="label"><?php esc_html_e( 'Ask', 'jcp-core' ); ?></span>
						<p><?php echo esc_html( $ch['ask_affiliate'] ?? $ch['ask'] ); ?></p>
					</div>
					<div class="jcp-ss-mode">
						<strong><?php esc_html_e( 'Partner — Say', 'jcp-core' ); ?></strong>
						<p><?php echo esc_html( $ch['say_partner'] ); ?></p>
						<span class="label"><?php esc_html_e( 'Ask', 'jcp-core' ); ?></span>
						<p><?php echo esc_html( $ch['ask_partner'] ?? $ch['ask'] ); ?></p>
					</div>
				</div>
				<span class="label"><?php esc_html_e( 'Avoid', 'jcp-core' ); ?></span>
				<p class="avoid"><?php echo esc_html( $ch['avoid'] ); ?></p>
			</article>
		<?php endforeach; ?>

		<h2 id="script-objections"><?php esc_html_e( 'Objection handling', 'jcp-core' ); ?></h2>
		<?php foreach ( $objections as $i => $item ) : ?>
			<article class="jcp-ss-card" id="obj-<?php echo esc_attr( (string) $i ); ?>">
				<h2>
					<?php echo esc_html( $item['title'] ); ?>
					<span class="jcp-ss-badge"><?php echo esc_html( $item['mode'] ); ?></span>
				</h2>
				<span class="label"><?php esc_html_e( 'Say', 'jcp-core' ); ?></span>
				<p><?php echo esc_html( $item['say'] ); ?></p>
				<span class="label"><?php esc_html_e( 'Proof move', 'jcp-core' ); ?></span>
				<p><?php echo esc_html( $item['proof'] ); ?></p>
			</article>
		<?php endforeach; ?>
	</div>
	<?php
}
