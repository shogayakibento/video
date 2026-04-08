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
        setupHoverPreview();
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
                    actressEl.innerHTML = '出演: <a href="' + (window.appUrl || '') + '/actress/' + actressId + '" class="item-actress-link">'+actress+'</a>';
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

        // Navigate to detail page on clickable cards
        document.addEventListener('click', function (e) {
            var card = e.target.closest('.item-card-clickable');
            if (card) {
                var detailUrl = card.dataset.detailUrl;
                if (detailUrl) {
                    window.location.href = detailUrl;
                } else {
                    openModal(card);
                }
                return;
            }
        });

        // Keyboard support for clickable cards
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                var card = e.target.closest('.item-card-clickable');
                if (card) {
                    e.preventDefault();
                    var detailUrl = card.dataset.detailUrl;
                    if (detailUrl) {
                        window.location.href = detailUrl;
                    } else {
                        openModal(card);
                    }
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

    // ===== Hover Video Preview =====
    function setupHoverPreview() {
        var activeEl = null;
        var activeCard = null;
        var activeSlideTimer = null;

        // card要素 → { video, seeked(bool) }
        var preloadCache = new WeakMap();

        function clearActive() {
            if (activeEl) {
                var w = activeEl.parentNode;
                if (activeEl.pause) activeEl.pause();
                activeEl.remove();
                activeEl = null;
                if (w) w.classList.remove('visible');
            }
            if (activeSlideTimer) {
                clearInterval(activeSlideTimer);
                activeSlideTimer = null;
            }
            // キャッシュをリセットして次のホバーで新しい位置から再生
            if (activeCard) {
                preloadCache.delete(activeCard);
                if (ioPreload) {
                    ioPreload.unobserve(activeCard);
                    ioPreload.observe(activeCard);
                }
            }
            activeCard = null;
        }

        function startSlideshow(wrap, images) {
            var img = document.createElement('img');
            img.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;';
            var idx = Math.floor(Math.random() * images.length);
            img.src = images[idx];
            wrap.appendChild(img);
            wrap.classList.add('visible');
            activeEl = img;
            var preloadImg = new Image();
            preloadImg.src = images[(idx + 1) % images.length];
            activeSlideTimer = setInterval(function() {
                idx = (idx + 1) % images.length;
                img.src = images[idx];
                preloadImg.src = images[(idx + 1) % images.length];
            }, 800);
        }

        function buildPreloadVideo(card, sampleUrl) {
            if (preloadCache.has(card)) return;
            var entry = { video: null, seeked: false };
            preloadCache.set(card, entry);

            var video = document.createElement('video');
            video.muted = true;
            video.controls = false;
            video.loop = true;
            video.playsInline = true;
            video.preload = 'metadata';
            video.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;';

            video.addEventListener('loadedmetadata', function() {
                var pct = 0.60 + Math.random() * 0.25;
                video.currentTime = video.duration * pct;
            });
            video.addEventListener('seeked', function() {
                entry.seeked = true;
                if (activeCard === card) {
                    var wrap = card.querySelector('.hover-video-wrap');
                    if (wrap && !wrap.contains(video)) {
                        wrap.appendChild(video);
                        activeEl = video;
                    }
                    wrap && wrap.classList.add('visible');
                    video.play().catch(function() {});
                }
            });
            video.addEventListener('error', function() {
                preloadCache.delete(card);
            });

            entry.video = video;
            video.src = sampleUrl;
        }

        // IntersectionObserver でビューポートに入ったらプリロード開始
        var ioPreload = null;
        if ('IntersectionObserver' in window) {
            ioPreload = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var card = entry.target;
                        var url = card.dataset.sampleUrl;
                        if (url) buildPreloadVideo(card, url);
                    }
                });
            }, { rootMargin: '200px' });
        }

        document.querySelectorAll('.hover-video-wrap').forEach(function(wrap) {
            var card = wrap.closest('[data-sample-url], [data-sample-images]');
            if (!card) return;

            var sampleUrl = card.dataset.sampleUrl;
            var sampleImages = card.dataset.sampleImages ? JSON.parse(card.dataset.sampleImages) : null;

            if (ioPreload && sampleUrl) {
                ioPreload.observe(card);
            }

            card.addEventListener('mouseenter', function() {
                clearActive();
                activeCard = card;

                if (sampleUrl) {
                    var cached = preloadCache.get(card);
                    if (cached && cached.video) {
                        var video = cached.video;
                        if (cached.seeked) {
                            wrap.appendChild(video);
                            activeEl = video;
                            wrap.classList.add('visible');
                            video.play().catch(function() {});
                        } else {
                            // シーク中 → seeked完了時に自動表示
                            activeEl = video;
                        }
                    } else {
                        // プリロードなし → 通常フロー
                        var video = document.createElement('video');
                        video.muted = true;
                        video.controls = false;
                        video.loop = true;
                        video.playsInline = true;
                        video.preload = 'metadata';
                        video.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;';

                        video.addEventListener('loadedmetadata', function() {
                            var pct = 0.60 + Math.random() * 0.25;
                            video.currentTime = video.duration * pct;
                        });
                        video.addEventListener('seeked', function() {
                            if (activeCard !== card) return;
                            wrap.classList.add('visible');
                            video.play().catch(function() {});
                        });
                        video.addEventListener('error', function() {
                            video.remove();
                            activeEl = null;
                            if (sampleImages && sampleImages.length && activeCard === card) {
                                startSlideshow(wrap, sampleImages);
                            }
                        });

                        wrap.appendChild(video);
                        activeEl = video;
                        video.src = sampleUrl;
                    }
                } else if (sampleImages && sampleImages.length) {
                    startSlideshow(wrap, sampleImages);
                }
            });

            card.addEventListener('mouseleave', function() {
                if (activeCard === card) clearActive();
            });
        });
    }
})();
