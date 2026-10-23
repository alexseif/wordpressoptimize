/**
 * Fluent Dual-State Flyout Navigation Scroll Controller
 * Microsoft Edge Explore style in-page floating navigation bar.
 * Lightweight zero-dependency IntersectionObserver (0ms CWV penalty).
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const hero = document.getElementById('main-content');
        const flyout = document.getElementById('fluent-flyout-nav');

        if (!flyout) {
            return;
        }

        // If no hero exists (e.g. on subpages without a hero id), show flyout after 300px scroll
        if (!hero) {
            window.addEventListener('scroll', function () {
                if (window.scrollY > 300) {
                    flyout.classList.add('fluent-flyout-nav--active');
                    flyout.setAttribute('aria-hidden', 'false');
                } else {
                    flyout.classList.remove('fluent-flyout-nav--active');
                    flyout.setAttribute('aria-hidden', 'true');
                }
            }, { passive: true });
            return;
        }

        // Primary observer: observes when the hero section leaves the viewport
        const observer = new IntersectionObserver(
            function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        flyout.classList.add('fluent-flyout-nav--active');
                        flyout.setAttribute('aria-hidden', 'false');
                    } else {
                        flyout.classList.remove('fluent-flyout-nav--active');
                        flyout.setAttribute('aria-hidden', 'true');
                    }
                });
            },
            {
                root: null,
                threshold: 0.05
            }
        );

        observer.observe(hero);

        // Highlight active link based on section intersection
        const sections = document.querySelectorAll('section[id], div[id].why-section, div[id].pricing-section, div[id].case-studies-section, div[id].intake-form-section');
        const flyoutLinks = flyout.querySelectorAll('.fluent-flyout-nav__links a');

        if (sections.length && flyoutLinks.length) {
            const sectionObserver = new IntersectionObserver(
                function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            const id = entry.target.getAttribute('id');
                            flyoutLinks.forEach(function (link) {
                                if (link.getAttribute('href') === '#' + id) {
                                    link.classList.add('active');
                                } else {
                                    link.classList.remove('active');
                                }
                            });
                        }
                    });
                },
                {
                    rootMargin: '-20% 0px -70% 0px'
                }
            );

            sections.forEach(function (sec) {
                sectionObserver.observe(sec);
            });
        }
    });
})();
