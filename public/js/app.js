/**
 * FanzaGate - Main JavaScript
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        setupMobileMenu();
        setupFAQ();
        setupScrollAnimations();
        setupSampleModal();
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

    // ===== Sample Preview Modal =====
    function setupSampleModal() {
        var modalOverlay = document.getElementById('sampleModal');
        if (!modalOverlay) return;

        var iframe    = document.getElementById('sampleModalIframe');
        var titleEl   = document.getElementById('sampleModalTitle');
        var actressEl = document.getElementById('sampleModalActress');
        var priceEl   = document.getElementById('sampleModalPrice');
        var linkEl    = document.getElementById('sampleModalLink');
        var closeBtn  = document.getElementById('sampleModalClose');
        var affiliateId = modalOverlay.dataset.affiliateId || '';

        function openModal(card) {
            var cid     = card.dataset.contentId;
            var title   = card.dataset.title || '';
            var actress   = card.dataset.actress || '';
            var actressId = card.dataset.actressId || '';
            var url       = card.dataset.url || '#';
            var price   = card.dataset.price || '';

            titleEl.textContent   = title;
            if (actress) {
                if (actressId) {
                    actressEl.innerHTML = '出演: <a href="/actress/' + actressId + '" class="item-actress-link">'+actress+'</a>';
                } else {
                    actressEl.textContent = '出演: ' + actress;
                }
                actressEl.style.display = '';
            } else {
                actressEl.textContent = '';
                actressEl.style.display = 'none';
            }
            priceEl.textContent   = price ? price.replace(/~$/, '円〜') + (price.endsWith('~') ? '' : '円') : '';
            linkEl.href           = url;

            iframe.src = 'https://www.dmm.co.jp/litevideo/-/part/=/affi_id=' + affiliateId + '/cid=' + cid + '/size=1280_720/';

            modalOverlay.classList.add('active');
            modalOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            modalOverlay.classList.remove('active');
            modalOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            // Stop video playback by clearing src
            iframe.src = '';
        }

        // Open on clickable cards
        document.addEventListener('click', function (e) {
            var card = e.target.closest('.item-card-clickable');
            if (card) {
                e.preventDefault();
                openModal(card);
                return;
            }
        });

        // Keyboard support for clickable cards
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                var card = e.target.closest('.item-card-clickable');
                if (card) {
                    e.preventDefault();
                    openModal(card);
                }
            }
            if (e.key === 'Escape' && modalOverlay.classList.contains('active')) {
                closeModal();
            }
        });

        closeBtn.addEventListener('click', closeModal);

        // Close when clicking backdrop
        modalOverlay.addEventListener('click', function (e) {
            if (e.target === modalOverlay) closeModal();
        });
    }
})();
