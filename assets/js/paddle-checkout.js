/**
 * Paddle Billing v2 Sandbox Checkout Handler
 * Wires instant checkout overlay to buttons with [data-paddle-checkout]
 */
document.addEventListener('DOMContentLoaded', function () {
	if (typeof Paddle !== 'undefined') {
		Paddle.Environment.set('sandbox');
		Paddle.Initialize({
			token: 'test_7c33e93ac99fb3196514f9c58aa'
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
