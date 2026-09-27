/**
 * Paddle Billing v2 Sandbox Checkout Handler
 * Wires instant checkout overlay to buttons with [data-paddle-checkout]
 */
document.addEventListener('DOMContentLoaded', function () {
	if (typeof Paddle !== 'undefined') {
		Paddle.Initialize({
			token: 'live_90e5941d603048c257b828dee44',
			eventCallback: function (data) {
				if (data && data.name === 'checkout.completed') {
					var txnId = data.data && data.data.transaction_id ? data.data.transaction_id : '';
					var redirectUrl = window.location.origin + '/diagnostic-intake/?order=success' + (txnId ? '&p_txn=' + encodeURIComponent(txnId) : '');
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
				Paddle.Checkout.open({
					items: [{ priceId: priceId, quantity: 1 }],
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
