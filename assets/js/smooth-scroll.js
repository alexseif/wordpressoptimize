/**
 * Smooth Scroll for Anchor Links
 */
(function() {
	'use strict';
	
	// Smooth scroll for all anchor links
	document.querySelectorAll('a[href^="#"]').forEach(anchor => {
		anchor.addEventListener('click', function (e) {
			// Do not scroll if this element triggers Paddle checkout
			if (this.hasAttribute('data-paddle-checkout')) {
				return;
			}

			const href = this.getAttribute('href');
			
			// Skip if it's just "#"
			if (href === '#' || href === '') {
				return;
			}
			
			const target = document.querySelector(href);
			
			if (target) {
				e.preventDefault();
				
				// Calculate offset for sticky header
				const headerOffset = 100;
				const elementPosition = target.getBoundingClientRect().top;
				const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
				
				window.scrollTo({
					top: offsetPosition,
					behavior: 'smooth'
				});
			}
		});
	});
})();

