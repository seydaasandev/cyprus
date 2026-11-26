/* Cyprus Touristic — ön yüz etkileşimleri (kütüphanesiz, saf JavaScript). */
(function () {
  'use strict';

  var doc = document;
  var root = doc.documentElement;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (sel, ctx) { return (ctx || doc).querySelector(sel); };
  var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel)); };

  /* ---------- Açılış ekranı ---------- */
  function initPreloader() {
    var el = $('#preloader');
    if (!el) { return; }
    var hide = function () { el.classList.add('is-done'); };
    window.addEventListener('load', function () { setTimeout(hide, 250); });
    setTimeout(hide, 2500);
  }

  /* ---------- Üst bilgi, kaydırma çubuğu, yukarı çık ---------- */
  function initScrollUi() {
    var header = $('#siteHeader');
    var bar = $('.scroll-progress i');
    var toTop = $('[data-to-top]');
    var ticking = false;
    function update() {
      var y = window.scrollY;
      var max = root.scrollHeight - window.innerHeight;
      if (header) { header.classList.toggle('is-scrolled', y > 40); }
      if (bar) { bar.style.transform = 'scaleX(' + (max > 0 ? Math.min(1, y / max) : 0) + ')'; }
      if (toTop) { toTop.classList.toggle('is-on', y > 700); }
      ticking = false;
    }
    window.addEventListener('scroll', function () {
      if (!ticking) { ticking = true; requestAnimationFrame(update); }
    }, { passive: true });
    update();
    if (toTop) {
      toTop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' }); });
    }
  }

  /* ---------- Mobil çekmece ---------- */
  function initDrawer() {
    var drawer = $('#drawer');
    if (!drawer) { return; }
    var openBtn = $('[data-drawer-open]');
    function set(open) {
      drawer.classList.toggle('is-open', open);
      drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
      if (openBtn) { openBtn.setAttribute('aria-expanded', open ? 'true' : 'false'); }
      doc.body.style.overflow = open ? 'hidden' : '';
    }
    if (openBtn) { openBtn.addEventListener('click', function () { set(true); }); }
    $$('[data-drawer-close]', drawer).forEach(function (b) { b.addEventListener('click', function () { set(false); }); });
    $$('[data-acc]', drawer).forEach(function (b) {
      b.addEventListener('click', function () { b.parentNode.classList.toggle('is-open'); });
    });
    doc.addEventListener('keydown', function (e) { if (e.key === 'Escape') { set(false); } });
  }

  /* ---------- Kaydırınca belirme ---------- */
  function initReveal() {
    // Uzun makalelerde paragrafları da tek tek canlandır.
    $$('[data-prose-reveal]').forEach(function (prose) {
      Array.prototype.forEach.call(prose.children, function (child) { child.setAttribute('data-reveal', ''); });
    });
    var items = $$('[data-reveal]');
    if (!('IntersectionObserver' in window) || reduceMotion) {
      items.forEach(function (el) { el.classList.add('is-in', 'is-done'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) { return; }
        var el = en.target;
        el.classList.add('is-in');
        io.unobserve(el);
        // Giriş bittikten sonra gecikmeyi kaldır ki üzerine gelme efektleri anında çalışsın.
        setTimeout(function () { el.classList.add('is-done'); }, 1500);
      });
    }, { threshold: 0.08, rootMargin: '0px 0px -5% 0px' });
    items.forEach(function (el) { io.observe(el); });
    // Güvence: çok hızlı kaydırmada gözlemcinin kaçırdığı, ekranın üstünde kalmış öğeleri de göster.
    var pending = false;
    window.addEventListener('scroll', function () {
      if (pending) { return; }
      pending = true;
      setTimeout(function () {
        pending = false;
        var limit = window.innerHeight;
        items.forEach(function (el) {
          if (!el.classList.contains('is-in') && el.getBoundingClientRect().top < limit) { el.classList.add('is-in', 'is-done'); }
        });
      }, 250);
    }, { passive: true });
  }

  /* ---------- Sayaçlar ---------- */
  function initCounters() {
    var els = $$('[data-count]');
    if (!els.length) { return; }
    function run(el) {
      var raw = el.getAttribute('data-count') || '';
      var m = raw.match(/^(\D*)(\d+(?:[.,]\d+)?)(.*)$/);
      if (!m || reduceMotion) { el.textContent = raw; return; }
      var target = parseFloat(m[2].replace(',', '.'));
      var decimals = (m[2].split(/[.,]/)[1] || '').length;
      var start = null;
      var dur = 1800;
      function frame(ts) {
        if (start === null) { start = ts; }
        var p = Math.min(1, (ts - start) / dur);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = m[1] + (target * eased).toFixed(decimals) + m[3];
        if (p < 1) { requestAnimationFrame(frame); } else { el.textContent = raw; }
      }
      requestAnimationFrame(frame);
    }
    if (!('IntersectionObserver' in window)) { els.forEach(run); return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { run(en.target); io.unobserve(en.target); } });
    }, { threshold: 0.5 });
    els.forEach(function (el) { io.observe(el); });
  }

  /* ---------- Hero slayt (video + görsel) ---------- */
  function initHero() {
    var hero = $('[data-hero]');
    if (!hero) { return; }
    var slides = $$('[data-slide]', hero);
    var dots = $$('[data-go]', hero);
    var dur = ((window.CT && window.CT.autoplay) || 7) * 1000;
    var idx = 0;
    var timer = null;
    hero.style.setProperty('--dur', (dur / 1000) + 's');

    function playVideo(slide, play) {
      var v = $('video', slide);
      if (!v) { return; }
      if (play) {
        var p = v.play();
        if (p && p.catch) { p.catch(function () {}); }
      } else {
        v.pause();
      }
    }
    function schedule() {
      clearTimeout(timer);
      if (slides.length < 2 || reduceMotion) { return; }
      timer = setTimeout(function () { go(idx + 1); }, dur);
    }
    function go(n) {
      var next = (n + slides.length) % slides.length;
      if (next === idx) { return; }
      slides[idx].classList.remove('is-active');
      playVideo(slides[idx], false);
      if (dots[idx]) { dots[idx].classList.remove('is-active'); }
      idx = next;
      slides[idx].classList.add('is-active');
      playVideo(slides[idx], true);
      if (dots[idx]) {
        // Dolum animasyonunu baştan başlat.
        void dots[idx].offsetWidth;
        dots[idx].classList.add('is-active');
      }
      schedule();
    }
    dots.forEach(function (d) { d.addEventListener('click', function () { go(parseInt(d.getAttribute('data-go'), 10)); }); });
    var prev = $('[data-prev]', hero);
    var next = $('[data-next]', hero);
    if (prev) { prev.addEventListener('click', function () { go(idx - 1); }); }
    if (next) { next.addEventListener('click', function () { go(idx + 1); }); }

    // Dokunmatik kaydırma
    var startX = null;
    hero.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
    hero.addEventListener('touchend', function (e) {
      if (startX === null) { return; }
      var dx = e.changedTouches[0].clientX - startX;
      if (Math.abs(dx) > 60) { go(idx + (dx < 0 ? 1 : -1)); }
      startX = null;
    }, { passive: true });
    hero.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft') { go(idx - 1); }
      if (e.key === 'ArrowRight') { go(idx + 1); }
    });
    doc.addEventListener('visibilitychange', function () {
      if (doc.hidden) { clearTimeout(timer); hero.classList.add('is-paused'); } else { hero.classList.remove('is-paused'); schedule(); }
    });
    playVideo(slides[0], true);
    schedule();
  }

  /* ---------- Paralaks ---------- */
  function initParallax() {
    var els = $$('[data-parallax]');
    if (!els.length || reduceMotion) { return; }
    var ticking = false;
    function update() {
      var vh = window.innerHeight;
      els.forEach(function (el) {
        var r = el.parentNode.getBoundingClientRect();
        if (r.bottom < 0 || r.top > vh) { return; }
        var speed = parseFloat(el.getAttribute('data-parallax')) || 0.2;
        var offset = (r.top + r.height / 2 - vh / 2) * -speed;
        el.style.transform = 'translate3d(0,' + offset.toFixed(1) + 'px,0)';
      });
      ticking = false;
    }
    window.addEventListener('scroll', function () {
      if (!ticking) { ticking = true; requestAnimationFrame(update); }
    }, { passive: true });
    update();
  }

  /* ---------- Karusel (sürükle + oklar) ---------- */
  function initCarousels() {
    $$('[data-carousel]').forEach(function (car) {
      var track = $('.carousel__track', car);
      if (!track) { return; }
      var down = false;
      var startX = 0;
      var startLeft = 0;
      var moved = false;
      track.addEventListener('pointerdown', function (e) {
        if (e.pointerType !== 'mouse') { return; }
        down = true; moved = false; startX = e.clientX; startLeft = track.scrollLeft;
      });
      window.addEventListener('pointermove', function (e) {
        if (!down) { return; }
        var dx = e.clientX - startX;
        if (Math.abs(dx) > 6) { moved = true; track.classList.add('is-drag'); }
        if (moved) { track.scrollLeft = startLeft - dx; }
      });
      window.addEventListener('pointerup', function () {
        if (!down) { return; }
        down = false;
        setTimeout(function () { track.classList.remove('is-drag'); }, 0);
      });
      track.addEventListener('click', function (e) { if (moved) { e.preventDefault(); } }, true);
      track.addEventListener('dragstart', function (e) { e.preventDefault(); });
    });
    function step(sel, dir) {
      var car = $(sel);
      var track = car && $('.carousel__track', car);
      var item = track && $('.carousel__item', track);
      if (!item) { return; }
      var gap = parseFloat(getComputedStyle(track).columnGap) || 24;
      track.scrollBy({ left: dir * (item.offsetWidth + gap), behavior: reduceMotion ? 'auto' : 'smooth' });
    }
    $$('[data-car-prev]').forEach(function (b) { b.addEventListener('click', function () { step(b.getAttribute('data-car-prev'), -1); }); });
    $$('[data-car-next]').forEach(function (b) { b.addEventListener('click', function () { step(b.getAttribute('data-car-next'), 1); }); });
  }

  /* ---------- Bölge panelleri ---------- */
  function initPanels() {
    $$('[data-panels]').forEach(function (wrap) {
      var panels = $$('.panel', wrap);
      function activate(p) {
        panels.forEach(function (x) { x.classList.toggle('is-active', x === p); });
      }
      panels.forEach(function (p) {
        p.addEventListener('mouseenter', function () { activate(p); });
        p.addEventListener('focus', function () { activate(p); });
        // Dokunmatik ekranda ilk dokunuş paneli açar, ikincisi sayfaya gider.
        p.addEventListener('click', function (e) {
          if (window.matchMedia('(hover: none)').matches && !p.classList.contains('is-active')) {
            e.preventDefault();
            activate(p);
          }
        });
      });
    });
  }

  /* ---------- Kart eğimi ---------- */
  function initTilt() {
    if (reduceMotion || window.matchMedia('(hover: none)').matches) { return; }
    $$('[data-tilt]').forEach(function (el) {
      el.addEventListener('mousemove', function (e) {
        if (!el.classList.contains('is-done')) { return; }
        var r = el.getBoundingClientRect();
        var x = (e.clientX - r.left) / r.width - 0.5;
        var y = (e.clientY - r.top) / r.height - 0.5;
        el.style.transition = 'transform .12s ease-out, box-shadow .5s';
        el.style.transform = 'perspective(900px) rotateX(' + (-y * 6).toFixed(2) + 'deg) rotateY(' + (x * 6).toFixed(2) + 'deg) translateY(-8px)';
      });
      el.addEventListener('mouseleave', function () {
        el.style.transition = 'transform .6s cubic-bezier(.2,.8,.2,1), box-shadow .5s';
        el.style.transform = '';
      });
    });
  }

  /* ---------- Otel süzgeci ---------- */
  function initFilters() {
    var bar = $('[data-filters]');
    var grid = $('[data-filter-grid]');
    if (!bar || !grid) { return; }
    var buttons = $$('[data-filter]', bar);
    buttons.forEach(function (b) {
      b.addEventListener('click', function () {
        var f = b.getAttribute('data-filter');
        buttons.forEach(function (x) { x.classList.toggle('is-active', x === b); });
        Array.prototype.forEach.call(grid.children, function (card) {
          card.classList.toggle('is-hidden', f !== '*' && card.getAttribute('data-region') !== f);
        });
      });
    });
  }

  /* ---------- Işık kutusu ---------- */
  function initLightbox() {
    var box = $('#lightbox');
    if (!box) { return; }
    var img = $('img', box);
    var cap = $('figcaption', box);
    var list = [];
    var pos = 0;
    // Makale içi görseller de büyütülebilsin.
    $$('.prose img').forEach(function (im) {
      if (!im.closest('a')) { im.setAttribute('data-lb', ''); }
    });
    function show(i) {
      pos = (i + list.length) % list.length;
      img.src = list[pos].src;
      cap.textContent = list[pos].caption || '';
    }
    function open(group, start) {
      list = group;
      box.classList.toggle('is-single', list.length < 2);
      show(start);
      box.classList.add('is-open');
      box.setAttribute('aria-hidden', 'false');
    }
    function close() {
      box.classList.remove('is-open');
      box.setAttribute('aria-hidden', 'true');
    }
    doc.addEventListener('click', function (e) {
      var t = e.target.closest('[data-lb]');
      if (!t) { return; }
      e.preventDefault();
      var scope = t.closest('[data-gallery], .prose') || doc;
      var nodes = $$('[data-lb]', scope);
      var group = nodes.map(function (n) {
        var inner = $('img', n);
        return { src: n.getAttribute('href') || n.getAttribute('data-full') || n.currentSrc || n.src, caption: n.getAttribute('data-caption') || n.getAttribute('alt') || (inner ? inner.alt : '') || '' };
      });
      open(group, Math.max(0, nodes.indexOf(t)));
    });
    $('[data-lb-close]', box).addEventListener('click', close);
    $('[data-lb-prev]', box).addEventListener('click', function () { show(pos - 1); });
    $('[data-lb-next]', box).addEventListener('click', function () { show(pos + 1); });
    box.addEventListener('click', function (e) { if (e.target === box) { close(); } });
    doc.addEventListener('keydown', function (e) {
      if (!box.classList.contains('is-open')) { return; }
      if (e.key === 'Escape') { close(); }
      if (e.key === 'ArrowLeft') { show(pos - 1); }
      if (e.key === 'ArrowRight') { show(pos + 1); }
    });
  }

  /* ---------- Dil seçimi (Google Çeviri, kendi menümüzle) ---------- */
  function initTranslate() {
    var langs = window.CT_LANGS;
    if (!langs || langs.length < 2) { return; }
    var source = 'tr';
    var DEFAULT_LANG = 'en'; // Varsayılan dil: İngilizce
    function readCookie() {
      var m = doc.cookie.match(/(?:^|;\s*)googtrans=([^;]+)/);
      if (!m) {
        // Çerez yok: kullanıcı kaynak dili (Türkçe) açıkça seçtiyse hatırla
        try { if (localStorage.getItem('ct_lang') === source) { return source; } } catch (e) {}
        return DEFAULT_LANG;
      }
      var v = decodeURIComponent(m[1]).split('/')[2];
      return langs.indexOf(v) > -1 ? v : DEFAULT_LANG;
    }
    function writeCookie(lang) {
      var host = location.hostname;
      var parts = host.split('.');
      var domains = ['', host, '.' + host];
      if (parts.length > 2) { domains.push('.' + parts.slice(-2).join('.'), '.' + parts.slice(-3).join('.')); }
      domains.forEach(function (d) {
        var base = 'googtrans=' + (lang === source ? '' : '/' + source + '/' + lang) + ';path=/' + (d ? ';domain=' + d : '');
        doc.cookie = base + (lang === source ? ';expires=Thu, 01 Jan 1970 00:00:00 GMT' : '');
      });
      // Kaynak dil (TR) seçimini localStorage'a kaydet/sil
      try {
        if (lang === source) { localStorage.setItem('ct_lang', source); }
        else { localStorage.removeItem('ct_lang'); }
      } catch (e) {}
    }
    var current = readCookie();
    $$('[data-lang-current]').forEach(function (el) { el.textContent = (current === 'iw' ? 'he' : current).toUpperCase().slice(0, 2); });
    $$('[data-lang-set]').forEach(function (b) {
      b.classList.toggle('is-active', b.getAttribute('data-lang-set') === current);
      b.addEventListener('click', function () {
        writeCookie(b.getAttribute('data-lang-set'));
        location.reload();
      });
    });
    $$('[data-lang]').forEach(function (wrap) {
      var btn = $('.lang__btn', wrap);
      btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var open = wrap.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
      doc.addEventListener('click', function () { wrap.classList.remove('is-open'); btn.setAttribute('aria-expanded', 'false'); });
    });
    // Çeviri betiği yalnızca başka bir dil seçildiyse yüklenir.
    if (current !== source) {
      window.googleTranslateElementInit = function () {
        /* global google */
        new google.translate.TranslateElement({ pageLanguage: source, includedLanguages: langs.join(','), autoDisplay: false }, 'google_translate_element');
      };
      var s = doc.createElement('script');
      s.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
      s.async = true;
      doc.head.appendChild(s);
      if (current === 'ar' || current === 'iw' || current === 'fa') { root.setAttribute('dir', 'rtl'); }
    }
  }

  /* ---------- Rezervasyon formu ---------- */
  function initReserve() {
    var hotel = $('select[data-room-source]');
    if (!hotel) { return; }
    var field = $('[data-room-field]');
    var room = field ? $('select', field) : null;
    var source = {};
    try { source = JSON.parse(hotel.getAttribute('data-room-source') || '{}'); } catch (err) { source = {}; }
    if (room) {
      hotel.addEventListener('change', function () {
        var list = source[hotel.value] || [];
        while (room.options.length > 1) { room.remove(1); }
        list.forEach(function (r) {
          var o = doc.createElement('option');
          o.value = r.id;
          o.textContent = r.label;
          room.appendChild(o);
        });
        field.hidden = list.length === 0;
      });
    }
    var checkin = $('input[name=checkin]');
    var checkout = $('input[name=checkout]');
    if (checkin && checkout) {
      checkin.addEventListener('change', function () {
        if (!checkin.value) { return; }
        var next = new Date(checkin.value + 'T00:00:00');
        next.setDate(next.getDate() + 1);
        var min = next.getFullYear() + '-' + ('0' + (next.getMonth() + 1)).slice(-2) + '-' + ('0' + next.getDate()).slice(-2);
        checkout.min = min;
        if (!checkout.value || checkout.value < min) { checkout.value = min; }
      });
    }
  }

  function init() {
    initPreloader();
    initReserve();
    initScrollUi();
    initDrawer();
    initReveal();
    initCounters();
    initHero();
    initParallax();
    initCarousels();
    initPanels();
    initTilt();
    initFilters();
    initLightbox();
    initTranslate();
  }

  if (doc.readyState === 'loading') { doc.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
