/**
 * FanzaGate - Main JavaScript
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        setupMobileMenu();
        setupFAQ();
        setupScrollAnimations();
    });

    // ===== Mobile Menu =====
    function setupMobileMenu() {
        var toggle = document.getElementById('menuToggle');
        var overlay = document.getElementById('mobileOverlay');
        var nav = document.getElementById('mobileNav');

        if (!toggle || !overlay || !nav) return;

        toggle.addEventListener('click', function () {
            overlay.classList.toggle('active');
            nav.classList.toggle('active');
        });

        overlay.addEventListener('click', function () {
            overlay.classList.remove('active');
            nav.classList.remove('active');
        });
    }

    // ===== FAQ Accordion =====
    function setupFAQ() {
        var questions = document.querySelectorAll('.faq-question');
        questions.forEach(function (q) {
            q.addEventListener('click', function () {
                var item = q.closest('.faq-item');
                var wasOpen = item.classList.contains('open');

                document.querySelectorAll('.faq-item').forEach(function (fi) {
                    fi.classList.remove('open');
                });

                if (!wasOpen) {
                    item.classList.add('open');
                }
            });
        });
    }

    // ===== Scroll Animations =====
    function setupScrollAnimations() {
        if (!('IntersectionObserver' in window)) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.animate-on-scroll').forEach(function (el) {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            observer.observe(el);
        });
    }
})();
