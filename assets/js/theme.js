/**
 * Rebecca Mercier Theme - Interactive Effects
 * Parallax, scroll reveals, and header state management
 */

(function() {
    'use strict';

    // Parallax effect for hero background
    function initParallax() {
        const parallaxElements = document.querySelectorAll('[data-parallax]');

        if (parallaxElements.length === 0) return;

        function updateParallax() {
            const scrolled = window.pageYOffset;

            parallaxElements.forEach(el => {
                const speed = parseFloat(el.getAttribute('data-parallax')) || -0.05;
                const yPos = scrolled * speed;
                el.style.transform = `translate3d(0, ${yPos}px, 0)`;
            });
        }

        // Throttle scroll events for performance
        let ticking = false;
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    updateParallax();
                    ticking = false;
                });
                ticking = true;
            }
        });

        // Initial call
        updateParallax();
    }

    // Scroll reveal animations
    function initScrollReveal() {
        const revealElements = document.querySelectorAll('.reveal-on-scroll');

        if (revealElements.length === 0) return;

        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        revealElements.forEach(el => observer.observe(el));
    }

    // Header scroll state
    function initHeaderScroll() {
        const header = document.querySelector('.site-header');
        if (!header) return;

        let lastScroll = 0;

        function updateHeader() {
            const currentScroll = window.pageYOffset;

            if (currentScroll > 100) {
                header.setAttribute('data-scrolled', 'true');
            } else {
                header.setAttribute('data-scrolled', 'false');
            }

            lastScroll = currentScroll;
        }

        let ticking = false;
        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    updateHeader();
                    ticking = false;
                });
                ticking = true;
            }
        });

        // Initial call
        updateHeader();
    }

    // Smooth scroll for anchor links
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');

                // Skip if it's just "#" or empty
                if (!href || href === '#' || href === '#!') return;

                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    const headerOffset = 80;
                    const elementPosition = target.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }

    // Initialize everything when DOM is ready
    function init() {
        initParallax();
        initScrollReveal();
        initHeaderScroll();
        initSmoothScroll();
    }

    // Run on DOMContentLoaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Also run on window load to catch any late-loaded content
    window.addEventListener('load', function() {
        // Re-trigger scroll reveal for any elements that might have loaded late
        const event = new Event('scroll');
        window.dispatchEvent(event);
    });

})();
