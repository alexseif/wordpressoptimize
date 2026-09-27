/**
 * Paddle Billing v2 Checkout Handler
 * Dual-environment auto-detection: Sandbox on local development, Live in production.
 * Wires instant checkout overlay to buttons with [data-paddle-checkout]
 */
document.addEventListener('DOMContentLoaded', function () {
	var isLocal =
		window.location.hostname === 'wordpressoptimize.local' ||
		window.location.hostname === 'localhost' ||
		window.location.hostname === '127.0.0.1';

	// Map live price IDs to sandbox price IDs for seamless local testing
	var SANDBOX_PRICE_MAP = {
		'pri_01m3h9pxkwa3mc2beb3tvtahsm': 'pri_01m2jrdk7fpdctzjvcz74sadjb', // Performance Diagnostic (€120)
		'pri_01m3h9sge94pfe42v0c686w8ad': 'pri_01m2jreheqht86475x6asb7dnh', // Performance Care Retainer (€240/mo)
		'pri_01m3h9t7f0v8y8qcr2s3t5z2n9': 'pri_01m2jrejg5drrnddxwa3bczn93', // Performance Build (€1,400)
		'pri_01m3h9v0856scavhsw396m413b': 'pri_01m2jrekgtv6ysckzkannzdkm7'  // E-Commerce Speed Suite (€2,100)
	};

	if (typeof Paddle !== 'undefined') {
		Paddle.Environment.set(isLocal ? 'sandbox' : 'production');
		Paddle.Initialize({
			token: isLocal ? 'test_7c33e93ac99fb3196514f9c58aa' : 'live_90e5941d603048c257b828dee44',
			eventCallback: function (data) {
				if (data && data.name === 'checkout.completed') {
					var txnId = data.data && data.data.transaction_id ? data.data.transaction_id : '';
					var redirectUrl =
						window.location.origin +
						'/diagnostic-intake/?order=success' +
						(txnId ? '&p_txn=' + encodeURIComponent(txnId) : '');
					window.location.href = redirectUrl;
				}
			}
		});
	}

	var checkoutButtons = document.querySelectorAll('[data-paddle-checkout]');
	checkoutButtons.forEach(function (button) {
		button.addEventListener('click', function (e) {
			var priceId = button.getAttribute('data-paddle-checkout');
			if (typeof Paddle !== 'undefined' && priceId) {
				e.preventDefault();
				e.stopPropagation();

				// In local development, map to corresponding Sandbox price ID
				var effectivePriceId = (isLocal && SANDBOX_PRICE_MAP[priceId]) ? SANDBOX_PRICE_MAP[priceId] : priceId;

				Paddle.Checkout.open({
					items: [{ priceId: effectivePriceId, quantity: 1 }],
					settings: {
						displayMode: 'overlay',
						theme: 'dark',
						locale: 'en',
						successUrl: window.location.origin + '/diagnostic-intake/?order=success'
					}
				});
			}
		});
	});
});
