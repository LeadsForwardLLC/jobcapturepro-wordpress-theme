<?php
/**
 * Sitewide marketing promo bar (dismiss persists in localStorage until cleared).
 *
 * @package JCP_Core
 */

if ( empty( $GLOBALS['jcp_show_promo_bar'] ) ) {
	return;
}

$cta_url = add_query_arg( 'coupon', 'earlybird', home_url( '/early-access' ) );
?>
<div class="jcp-promo-bar" id="jcpPromoBar" role="region" aria-label="<?php esc_attr_e( 'Limited promotion', 'jcp-core' ); ?>">
	<div class="jcp-promo-bar-inner jcp-container">
		<p class="jcp-promo-bar-text">
			<span class="jcp-promo-bar-badge"><?php esc_html_e( 'Early bird', 'jcp-core' ); ?></span>
			<?php
			echo wp_kses(
				sprintf(
					/* translators: 1: price early bird 2: price regular */
					__( 'Enterprise for <strong>%1$s/month</strong> (normally %2$s) when you subscribe with code %3$s.', 'jcp-core' ),
					'$125',
					'$399',
					'<code class="jcp-promo-bar-code">earlybird</code>'
				),
				[
					'strong' => [],
					'code'   => [ 'class' => [] ],
				]
			);
			?>
		</p>
		<div class="jcp-promo-bar-actions">
			<a href="<?php echo esc_url( $cta_url ); ?>" class="btn btn-primary jcp-promo-bar-cta"><?php esc_html_e( 'Secure this rate', 'jcp-core' ); ?></a>
			<button type="button" class="jcp-promo-bar-dismiss" id="jcpPromoDismiss" aria-label="<?php esc_attr_e( 'Dismiss promotion', 'jcp-core' ); ?>">
				&times;
			</button>
		</div>
	</div>
</div>
<script>
(function () {
	var bar = document.getElementById('jcpPromoBar');
	if (!bar) return;
	var key = 'jcp_promo_earlybird_v2';
	function hide() {
		bar.classList.add('is-dismissed');
		document.body.classList.remove('jcp-has-promo-bar');
		try {
			localStorage.setItem(key, '1');
		} catch (e) {}
		window.dispatchEvent(new CustomEvent('jcp-promo-dismiss'));
	}
	try {
		if (localStorage.getItem(key) === '1') hide();
	} catch (e) {}
	var btn = document.getElementById('jcpPromoDismiss');
	if (btn) btn.addEventListener('click', hide);
})();
</script>
