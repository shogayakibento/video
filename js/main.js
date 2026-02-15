/**
 * CinemaFind - DMM Affiliate Site
 * メインJavaScript
 */

(function () {
  'use strict';

  // ===== アフィリエイトリンク生成 =====
  const CONFIG = window.SITE_CONFIG || {
    affiliateId: 'YOUR_AFFILIATE_ID',
    siteUrl: 'https://example.com'
  };

  function buildAffiliateLink(path) {
    const baseUrl = 'https://al.dmm.co.jp/';
    const targetUrl = 'https://www.dmm.com' + path;
    return baseUrl + '?lurl=' + encodeURIComponent(targetUrl) +
      '&af_id=' + encodeURIComponent(CONFIG.affiliateId) +
      '&ch=link_tool&ch_id=link';
  }

  function buildPremiumLink() {
    return buildAffiliateLink('/premium/');
  }

  // ===== サンプルデータ =====
  const FEATURED_DATA = [
    {
      id: 1, title: '劇場版 呪術廻戦', genre: 'アニメ',
      category: 'anime', rating: '4.8', badge: '人気',
      icon: '&#127912;', path: '/digital/videomarket/anime/'
    },
    {
      id: 2, title: 'ゴジラ-1.0', genre: '映画',
      category: 'movie', rating: '4.7', badge: '話題',
      icon: '&#127916;', path: '/digital/videomarket/movie/'
    },
    {
      id: 3, title: 'SPY×FAMILY Season 3', genre: 'アニメ',
      category: 'anime', rating: '4.9', badge: 'NEW', badgeClass: 'new',
      icon: '&#127912;', path: '/digital/videomarket/anime/'
    },
    {
      id: 4, title: 'VIVANT 特別編', genre: 'ドラマ',
      category: 'drama', rating: '4.6', badge: '注目',
      icon: '&#127917;', path: '/digital/videomarket/drama/'
    },
    {
      id: 5, title: 'キングダム 大将軍の帰還', genre: '映画',
      category: 'movie', rating: '4.5', badge: '人気',
      icon: '&#127916;', path: '/digital/videomarket/movie/'
    },
    {
      id: 6, title: '推しの子 第2期', genre: 'アニメ',
      category: 'anime', rating: '4.8', badge: '話題',
      icon: '&#127912;', path: '/digital/videomarket/anime/'
    },
    {
      id: 7, title: 'ミステリと言う勿れ 映画版', genre: '映画',
      category: 'movie', rating: '4.4', badge: 'NEW', badgeClass: 'new',
      icon: '&#127916;', path: '/digital/videomarket/movie/'
    },
    {
      id: 8, title: '海のはじまり', genre: 'ドラマ',
      category: 'drama', rating: '4.3', badge: '注目',
      icon: '&#127917;', path: '/digital/videomarket/drama/'
    }
  ];

  const RANKING_DATA = {
    movie: [
      { title: 'ゴジラ-1.0', meta: 'アクション / SF', score: '4.7' },
      { title: 'キングダム 大将軍の帰還', meta: 'アクション / 歴史', score: '4.5' },
      { title: 'ミステリと言う勿れ', meta: 'ミステリー', score: '4.4' },
      { title: 'あの花が咲く丘で', meta: 'ドラマ / 恋愛', score: '4.3' },
      { title: '変な家', meta: 'ホラー / ミステリー', score: '4.2' }
    ],
    anime: [
      { title: 'SPY×FAMILY', meta: 'アクション / コメディ', score: '4.9' },
      { title: '呪術廻戦', meta: 'アクション / ファンタジー', score: '4.8' },
      { title: '推しの子', meta: 'ドラマ / サスペンス', score: '4.8' },
      { title: '葬送のフリーレン', meta: 'ファンタジー / 冒険', score: '4.7' },
      { title: '薬屋のひとりごと', meta: 'ミステリー / 歴史', score: '4.6' }
    ],
    drama: [
      { title: 'VIVANT', meta: 'サスペンス / アクション', score: '4.6' },
      { title: '海のはじまり', meta: 'ヒューマンドラマ', score: '4.3' },
      { title: 'ブラッシュアップライフ', meta: 'コメディ / SF', score: '4.5' },
      { title: '最高の教師', meta: 'ヒューマンドラマ', score: '4.2' },
      { title: 'あなたがしてくれなくても', meta: '恋愛 / ドラマ', score: '4.1' }
    ]
  };

  const NEW_RELEASES = [
    { title: 'ダンダダン', genre: 'アニメ', icon: '&#127912;', path: '/digital/videomarket/anime/' },
    { title: 'ラストマイル', genre: '映画', icon: '&#127916;', path: '/digital/videomarket/movie/' },
    { title: 'チ。地球の運動について', genre: 'アニメ', icon: '&#127912;', path: '/digital/videomarket/anime/' },
    { title: '放課後カルテ', genre: 'ドラマ', icon: '&#127917;', path: '/digital/videomarket/drama/' },
    { title: '怪獣8号', genre: 'アニメ', icon: '&#127912;', path: '/digital/videomarket/anime/' },
    { title: '四月になれば彼女は', genre: '映画', icon: '&#127916;', path: '/digital/videomarket/movie/' },
    { title: '嘘解きレトリック', genre: 'ドラマ', icon: '&#127917;', path: '/digital/videomarket/drama/' },
    { title: 'ブルーロック VS. U-20 Japan', genre: 'アニメ', icon: '&#127912;', path: '/digital/videomarket/anime/' },
    { title: '正体', genre: '映画', icon: '&#127916;', path: '/digital/videomarket/movie/' },
    { title: '宙わたる教室', genre: 'ドラマ', icon: '&#127917;', path: '/digital/videomarket/drama/' }
  ];

  // ===== DOM Ready =====
  document.addEventListener('DOMContentLoaded', function () {
    renderFeatured('all');
    renderRankings();
    renderNewReleases();
    setupTabs();
    setupFAQ();
    setupMobileMenu();
    setupSearch();
    setupCTA();
    setupScrollAnimations();
    setupTags();
  });

  // ===== Featured Section =====
  function renderFeatured(filter) {
    var grid = document.getElementById('featuredGrid');
    if (!grid) return;

    var items = FEATURED_DATA;
    if (filter !== 'all') {
      items = items.filter(function (item) { return item.category === filter; });
    }

    grid.innerHTML = items.map(function (item) {
      var badgeClass = item.badgeClass ? ' ' + item.badgeClass : '';
      return '<a href="' + buildAffiliateLink(item.path) + '" class="featured-card animate-in" target="_blank" rel="noopener noreferrer">' +
        '<div class="featured-thumb">' +
        '<div class="featured-thumb-inner">' + item.icon + '</div>' +
        '<span class="badge' + badgeClass + '">' + item.badge + '</span>' +
        '<span class="rating">★ ' + item.rating + '</span>' +
        '</div>' +
        '<div class="featured-info">' +
        '<h3>' + escapeHtml(item.title) + '</h3>' +
        '<div class="featured-meta">' +
        '<span class="featured-genre">' + escapeHtml(item.genre) + '</span>' +
        '</div>' +
        '</div>' +
        '</a>';
    }).join('');
  }

  // ===== Tabs =====
  function setupTabs() {
    var buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(function (btn) {
      btn.addEventListener('click', function () {
        buttons.forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        renderFeatured(btn.getAttribute('data-tab'));
      });
    });
  }

  // ===== Rankings =====
  function renderRankings() {
    renderRankingList('movieRanking', RANKING_DATA.movie, '/digital/videomarket/movie/');
    renderRankingList('animeRanking', RANKING_DATA.anime, '/digital/videomarket/anime/');
    renderRankingList('dramaRanking', RANKING_DATA.drama, '/digital/videomarket/drama/');
  }

  function renderRankingList(elementId, data, path) {
    var list = document.getElementById(elementId);
    if (!list) return;

    list.innerHTML = data.map(function (item) {
      return '<li class="ranking-item" onclick="window.open(\'' + buildAffiliateLink(path) + '\', \'_blank\')">' +
        '<div class="ranking-item-info">' +
        '<div class="ranking-item-title">' + escapeHtml(item.title) + '</div>' +
        '<div class="ranking-item-meta">' + escapeHtml(item.meta) + '</div>' +
        '</div>' +
        '<div class="ranking-item-score">★ ' + item.score + '</div>' +
        '</li>';
    }).join('');
  }

  // ===== New Releases =====
  function renderNewReleases() {
    var slider = document.getElementById('releasesSlider');
    if (!slider) return;

    slider.innerHTML = NEW_RELEASES.map(function (item) {
      return '<a href="' + buildAffiliateLink(item.path) + '" class="release-card" target="_blank" rel="noopener noreferrer">' +
        '<div class="release-thumb">' +
        item.icon +
        '<span class="release-new-badge">NEW</span>' +
        '</div>' +
        '<div class="release-info">' +
        '<h3>' + escapeHtml(item.title) + '</h3>' +
        '<p>' + escapeHtml(item.genre) + '</p>' +
        '</div>' +
        '</a>';
    }).join('');
  }

  // ===== FAQ =====
  function setupFAQ() {
    var questions = document.querySelectorAll('.faq-question');
    questions.forEach(function (q) {
      q.addEventListener('click', function () {
        var item = q.closest('.faq-item');
        var wasOpen = item.classList.contains('open');
        // Close all
        document.querySelectorAll('.faq-item').forEach(function (fi) {
          fi.classList.remove('open');
        });
        // Toggle current
        if (!wasOpen) {
          item.classList.add('open');
        }
      });
    });
  }

  // ===== Mobile Menu =====
  function setupMobileMenu() {
    var toggle = document.querySelector('.menu-toggle');
    if (!toggle) return;

    // Create mobile nav
    var overlay = document.createElement('div');
    overlay.className = 'mobile-nav-overlay';

    var nav = document.createElement('div');
    nav.className = 'mobile-nav';
    nav.innerHTML = '<a href="index.html">ホーム</a>' +
      '<a href="category/movie.html">映画</a>' +
      '<a href="category/anime.html">アニメ</a>' +
      '<a href="category/drama.html">ドラマ</a>' +
      '<a href="category/game.html">ゲーム</a>';

    document.body.appendChild(overlay);
    document.body.appendChild(nav);

    toggle.addEventListener('click', function () {
      overlay.classList.toggle('active');
      nav.classList.toggle('active');
    });

    overlay.addEventListener('click', function () {
      overlay.classList.remove('active');
      nav.classList.remove('active');
    });
  }

  // ===== Search =====
  function setupSearch() {
    var input = document.getElementById('searchInput');
    var btn = document.getElementById('searchBtn');
    if (!input || !btn) return;

    function doSearch() {
      var query = input.value.trim();
      if (query) {
        var searchUrl = buildAffiliateLink('/search/?searchstr=' + encodeURIComponent(query));
        window.open(searchUrl, '_blank');
      }
    }

    btn.addEventListener('click', doSearch);
    input.addEventListener('keypress', function (e) {
      if (e.key === 'Enter') doSearch();
    });
  }

  // ===== Tags =====
  function setupTags() {
    var tags = document.querySelectorAll('.tag[data-search]');
    tags.forEach(function (tag) {
      tag.addEventListener('click', function () {
        var query = tag.getAttribute('data-search');
        var searchUrl = buildAffiliateLink('/search/?searchstr=' + encodeURIComponent(query));
        window.open(searchUrl, '_blank');
      });
    });
  }

  // ===== CTA Button =====
  function setupCTA() {
    var cta = document.getElementById('mainCta');
    if (cta) {
      cta.href = buildPremiumLink();
      cta.target = '_blank';
      cta.rel = 'noopener noreferrer';
    }
  }

  // ===== Scroll Animations =====
  function setupScrollAnimations() {
    if (!('IntersectionObserver' in window)) return;

    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-in');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1 });

    document.querySelectorAll('.category-card, .benefit-card').forEach(function (el) {
      observer.observe(el);
    });
  }

  // ===== Utility =====
  function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  // ===== Category page content rendering =====
  window.renderCategoryContent = function (category) {
    var grid = document.getElementById('categoryGrid');
    if (!grid) return;

    var allItems = FEATURED_DATA.concat(
      NEW_RELEASES.map(function (r, i) {
        return {
          id: 100 + i, title: r.title, genre: r.genre,
          category: r.genre === 'アニメ' ? 'anime' : r.genre === '映画' ? 'movie' : r.genre === 'ドラマ' ? 'drama' : 'game',
          rating: (4 + Math.random() * 0.9).toFixed(1),
          badge: 'NEW', badgeClass: 'new',
          icon: r.icon, path: r.path
        };
      })
    );

    var items = category === 'all' ? allItems :
      allItems.filter(function (item) { return item.category === category; });

    grid.innerHTML = items.map(function (item) {
      var badgeClass = item.badgeClass ? ' ' + item.badgeClass : '';
      return '<a href="' + buildAffiliateLink(item.path) + '" class="featured-card animate-in" target="_blank" rel="noopener noreferrer">' +
        '<div class="featured-thumb">' +
        '<div class="featured-thumb-inner">' + item.icon + '</div>' +
        '<span class="badge' + badgeClass + '">' + (item.badge || '') + '</span>' +
        '<span class="rating">★ ' + item.rating + '</span>' +
        '</div>' +
        '<div class="featured-info">' +
        '<h3>' + escapeHtml(item.title) + '</h3>' +
        '<div class="featured-meta">' +
        '<span class="featured-genre">' + escapeHtml(item.genre) + '</span>' +
        '</div>' +
        '</div>' +
        '</a>';
    }).join('');
  };

})();
