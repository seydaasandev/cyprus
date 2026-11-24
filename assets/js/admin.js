/* Cyprus Touristic — yönetim paneli etkileşimleri. */
(function () {
  'use strict';

  var doc = document;
  var ADM = window.ADM || {};
  var $ = function (sel, ctx) { return (ctx || doc).querySelector(sel); };
  var $$ = function (sel, ctx) { return Array.prototype.slice.call((ctx || doc).querySelectorAll(sel)); };

  /** CSRF belirteçli POST isteği; JSON yanıt döndürür. */
  function post(url, body) {
    var data = body instanceof FormData ? body : new FormData();
    if (!(body instanceof FormData) && body) {
      Object.keys(body).forEach(function (k) {
        if (Array.isArray(body[k])) { body[k].forEach(function (v) { data.append(k + '[]', v); }); } else { data.append(k, body[k]); }
      });
    }
    data.append('_csrf', ADM.csrf);
    return fetch(url, { method: 'POST', body: data, credentials: 'same-origin', headers: { 'X-Requested-With': 'fetch' } })
      .then(function (res) {
        return res.json().catch(function () { return { ok: false, error: 'Sunucu yanıtı okunamadı (' + res.status + ').' }; });
      });
  }

  function toast(message, isError) {
    var el = doc.createElement('div');
    el.className = 'adm-alert adm-alert--' + (isError ? 'err' : 'ok');
    el.style.cssText = 'position:fixed;right:20px;top:76px;z-index:200;box-shadow:0 14px 30px -14px rgba(6,36,58,.5);max-width:360px';
    el.textContent = message;
    doc.body.appendChild(el);
    setTimeout(function () { el.remove(); }, isError ? 6000 : 2200);
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
  }

  function previewHtml(item) {
    if (!item) { return ''; }
    if (item.kind === 'video') { return '<video src="' + escapeHtml(item.url) + '" muted preload="metadata"></video>'; }
    if (item.kind === 'file') { return '<span class="adm-file">PDF<br>' + escapeHtml(item.name) + '</span>'; }
    return '<img src="' + escapeHtml(item.thumb || item.url) + '" alt="">';
  }

  /* ---------- Genel ---------- */
  function initBasics() {
    var side = $('#admSide');
    var toggle = $('[data-side-toggle]');
    if (side && toggle) {
      toggle.addEventListener('click', function (e) { e.stopPropagation(); side.classList.toggle('is-open'); });
      doc.addEventListener('click', function (e) { if (!side.contains(e.target)) { side.classList.remove('is-open'); } });
    }
    $$('[data-flash]').forEach(function (el) { setTimeout(function () { el.style.transition = 'opacity .5s'; el.style.opacity = '0'; setTimeout(function () { el.remove(); }, 500); }, 4000); });
    doc.addEventListener('submit', function (e) {
      var msg = e.target.getAttribute('data-confirm');
      if (msg && !window.confirm(msg)) { e.preventDefault(); }
    });
    $$('[data-confirm-click]').forEach(function (b) {
      b.addEventListener('click', function (e) { if (!window.confirm(b.getAttribute('data-confirm-click'))) { e.preventDefault(); } });
    });
    // Kaydedilmemiş değişiklik uyarısı
    $$('form[data-dirty]').forEach(function (form) {
      var dirty = false;
      form.addEventListener('input', function () { dirty = true; });
      form.addEventListener('submit', function () { dirty = false; });
      window.addEventListener('beforeunload', function (e) { if (dirty) { e.preventDefault(); e.returnValue = ''; } });
    });
  }

  /* ---------- Liste: sıralama ve anahtarlar ---------- */
  function initLists() {
    $$('[data-sortable]').forEach(function (tbody) {
      if (!window.Sortable) { return; }
      window.Sortable.create(tbody, {
        handle: '.adm-grip', animation: 180, ghostClass: 'sortable-ghost', chosenClass: 'sortable-chosen',
        onEnd: function () {
          var ids = $$('tr[data-id]', tbody).map(function (tr) { return tr.getAttribute('data-id'); });
          post(tbody.getAttribute('data-sortable'), { ids: ids }).then(function (r) {
            toast(r.ok ? 'Sıralama kaydedildi.' : (r.error || 'Sıralama kaydedilemedi.'), !r.ok);
          });
        }
      });
    });
    $$('[data-toggle]').forEach(function (box) {
      box.addEventListener('change', function () {
        post(box.getAttribute('data-toggle')).then(function (r) {
          if (!r.ok) { box.checked = !box.checked; toast(r.error || 'Güncellenemedi.', true); return; }
          toast(r.value ? 'Yayına alındı.' : 'Yayından kaldırıldı.');
        });
      });
    });
  }

  /* ---------- Basit alanlar ---------- */
  function initFields() {
    $$('[data-range]').forEach(function (wrap) {
      var input = $('input', wrap);
      var out = $('output', wrap);
      input.addEventListener('input', function () { out.textContent = input.value; });
    });
    $$('[data-color]').forEach(function (wrap) {
      var picker = $('input[type=color]', wrap);
      var text = $('input[type=text]', wrap);
      var reset = $('[data-color-reset]', wrap);
      picker.addEventListener('input', function () { text.value = picker.value.toUpperCase(); text.dispatchEvent(new Event('input', { bubbles: true })); });
      text.addEventListener('input', function () { if (/^#[0-9a-f]{6}$/i.test(text.value)) { picker.value = text.value; } });
      if (reset) {
        reset.addEventListener('click', function () { text.value = reset.getAttribute('data-color-reset'); picker.value = text.value.slice(0, 7); text.dispatchEvent(new Event('input', { bubbles: true })); });
      }
    });
    $$('[data-icon-pick]').forEach(function (wrap) {
      var input = $('input', wrap);
      $$('[data-icon]', wrap).forEach(function (b) {
        b.addEventListener('click', function () {
          $$('[data-icon]', wrap).forEach(function (x) { x.classList.remove('is-active'); });
          b.classList.add('is-active');
          input.value = b.getAttribute('data-icon');
          input.dispatchEvent(new Event('input', { bubbles: true }));
        });
      });
      var active = $('.is-active', wrap);
      if (active) { active.scrollIntoView({ block: 'nearest' }); }
    });
  }

  /* ---------- Tema canlı önizleme ---------- */
  function initThemePreview() {
    var box = $('[data-theme-preview] .adm-preview__box');
    if (!box) { return; }
    var map = { c_primary: '--p', c_secondary: '--s', c_accent: '--ac', c_dark: '--d' };
    function val(name) { var el = doc.querySelector('[name="' + name + '"]:not([type=hidden])') || doc.querySelector('[name="' + name + '"]'); return el ? el.value : ''; }
    function update() {
      Object.keys(map).forEach(function (k) { if (val(k)) { box.style.setProperty(map[k], val(k)); } });
      box.style.setProperty('--r', (val('radius') || 16) + 'px');
      box.style.setProperty('--br', { square: '0px', rounded: '10px', pill: '999px' }[val('btn_shape')] || '999px');
      var title = $('.adm-preview__title', box);
      title.style.fontFamily = "'" + val('font_heading') + "', serif";
      title.style.fontWeight = val('heading_weight') || 600;
      box.style.fontFamily = "'" + val('font_body') + "', sans-serif";
      var upper = doc.querySelector('[name="btn_upper"][type=checkbox]');
      $$('.adm-preview__btn', box).forEach(function (b) { b.style.textTransform = upper && upper.checked ? 'uppercase' : 'none'; });
      var style = val('btn_style');
      var first = $('.adm-preview__btn', box);
      first.style.background = style === 'solid' ? 'var(--p)' : (style === 'outline' ? 'transparent' : '');
    }
    doc.addEventListener('input', update);
    doc.addEventListener('change', update);
    update();
  }

  /* ---------- Medya seçici ---------- */
  var mediaModal = null;
  var mediaCallback = null;
  var mediaKinds = 'image';

  function uploadFiles(files, kinds, statusEl) {
    var list = Array.prototype.slice.call(files);
    var done = [];
    var chain = Promise.resolve();
    list.forEach(function (file, i) {
      chain = chain.then(function () {
        if (statusEl) { statusEl.textContent = 'Yükleniyor (' + (i + 1) + '/' + list.length + '): ' + file.name; }
        var fd = new FormData();
        fd.append('file', file);
        fd.append('kinds', kinds);
        return post(ADM.uploadUrl, fd).then(function (r) {
          if (r.ok) { done.push(r.item); } else { toast(file.name + ': ' + (r.error || 'yüklenemedi'), true); }
        });
      });
    });
    return chain.then(function () {
      if (statusEl) { statusEl.textContent = done.length ? done.length + ' dosya yüklendi.' : ''; }
      return done;
    });
  }

  function renderMediaGrid(items) {
    var grid = $('[data-media-grid]', mediaModal);
    grid.innerHTML = items.length ? '' : '<p class="adm-empty">Bu türde dosya yok. "Dosya Yükle" ile ekleyin.</p>';
    items.forEach(function (item) {
      var b = doc.createElement('button');
      b.type = 'button';
      b.title = item.name;
      b.innerHTML = previewHtml(item);
      b.addEventListener('click', function () {
        if (mediaCallback) { mediaCallback(item); }
        closeMedia();
      });
      grid.appendChild(b);
    });
  }

  function loadMedia() {
    var status = $('[data-media-status]', mediaModal);
    status.textContent = 'Yükleniyor…';
    fetch(ADM.mediaUrl + '&kinds=' + encodeURIComponent(mediaKinds), { credentials: 'same-origin', headers: { 'X-Requested-With': 'fetch' } })
      .then(function (r) { return r.json(); })
      .then(function (r) { status.textContent = ''; renderMediaGrid(r.items || []); })
      .catch(function () { status.textContent = 'Dosya listesi alınamadı.'; });
  }

  function openMedia(kinds, callback) {
    mediaKinds = kinds || 'image';
    mediaCallback = callback;
    mediaModal.classList.add('is-open');
    mediaModal.setAttribute('aria-hidden', 'false');
    loadMedia();
  }

  function closeMedia() {
    mediaModal.classList.remove('is-open');
    mediaModal.setAttribute('aria-hidden', 'true');
    mediaCallback = null;
  }

  function initMedia() {
    mediaModal = $('#mediaModal');
    if (!mediaModal) { return; }
    $('[data-media-close]', mediaModal).addEventListener('click', closeMedia);
    mediaModal.addEventListener('click', function (e) { if (e.target === mediaModal) { closeMedia(); } });
    doc.addEventListener('keydown', function (e) { if (e.key === 'Escape') { closeMedia(); } });

    $$('[data-media-upload]').forEach(function (input) {
      input.addEventListener('change', function () {
        var inModal = mediaModal.contains(input);
        var status = inModal ? $('[data-media-status]', mediaModal) : (input.parentNode.querySelector('[data-media-status]') || null);
        uploadFiles(input.files, inModal ? mediaKinds : 'image,video,file', status).then(function (done) {
          input.value = '';
          if (input.getAttribute('data-reload') && done.length) { location.reload(); return; }
          if (inModal) { loadMedia(); }
        });
      });
    });
    $$('[data-drop]').forEach(function (zone) {
      ['dragenter', 'dragover'].forEach(function (ev) { zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add('is-over'); }); });
      ['dragleave', 'drop'].forEach(function (ev) { zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.remove('is-over'); }); });
      zone.addEventListener('drop', function (e) {
        uploadFiles(e.dataTransfer.files, 'image,video,file', $('[data-media-status]', zone)).then(function (done) { if (done.length) { location.reload(); } });
      });
    });

    $$('[data-media-field]').forEach(function (field) {
      var input = $('input[type=hidden]', field);
      var preview = $('[data-media-preview]', field);
      $('[data-media-pick]', field).addEventListener('click', function () {
        openMedia(field.getAttribute('data-kinds'), function (item) {
          input.value = item.path;
          preview.innerHTML = previewHtml(item);
          input.dispatchEvent(new Event('input', { bubbles: true }));
        });
      });
      $('[data-media-clear]', field).addEventListener('click', function () {
        input.value = '';
        preview.innerHTML = '';
        input.dispatchEvent(new Event('input', { bubbles: true }));
      });
    });

    $$('[data-gallery]').forEach(function (gal) {
      var items = $('[data-gallery-items]', gal);
      function add(item) {
        var d = doc.createElement('div');
        d.className = 'adm-gallery__item';
        d.innerHTML = '<input type="hidden" name="gallery[]" value="' + escapeHtml(item.path) + '">' + previewHtml(item) + '<button type="button" data-gallery-remove title="Kaldır">×</button>';
        items.appendChild(d);
        items.dispatchEvent(new Event('input', { bubbles: true }));
      }
      $('[data-gallery-add]', gal).addEventListener('click', function () { openMedia('image', add); });
      items.addEventListener('click', function (e) {
        var b = e.target.closest('[data-gallery-remove]');
        if (b) { b.parentNode.remove(); items.dispatchEvent(new Event('input', { bubbles: true })); }
      });
      if (window.Sortable) { window.Sortable.create(items, { animation: 160, ghostClass: 'sortable-ghost' }); }
    });
  }

  /* ---------- Zengin metin düzenleyici ---------- */
  var RICH_TOOLS = [
    ['bold', '<b>K</b>', 'Kalın'], ['italic', '<i>İ</i>', 'İtalik'], ['underline', '<u>A</u>', 'Altı çizili'], ['|'],
    ['h2', 'B1', 'Büyük başlık'], ['h3', 'B2', 'Küçük başlık'], ['p', '¶', 'Paragraf'], ['blockquote', '❝', 'Alıntı'], ['|'],
    ['insertUnorderedList', '• Liste', 'Madde işaretli liste'], ['insertOrderedList', '1. Liste', 'Numaralı liste'], ['|'],
    ['link', 'Bağlantı', 'Bağlantı ekle'], ['unlink', 'Bağ. kaldır', 'Bağlantıyı kaldır'], ['image', 'Görsel', 'Görsel ekle'], ['|'],
    ['removeFormat', 'Temizle', 'Biçimi temizle'], ['source', '&lt;/&gt;', 'HTML kaynağı']
  ];

  function initRich() {
    $$('textarea[data-rich]').forEach(function (ta) {
      var wrap = doc.createElement('div');
      wrap.className = 'adm-rich';
      var bar = doc.createElement('div');
      bar.className = 'adm-rich__bar';
      var area = doc.createElement('div');
      area.className = 'adm-rich__area';
      area.contentEditable = 'true';
      area.innerHTML = ta.value;
      ta.parentNode.insertBefore(wrap, ta);
      wrap.appendChild(bar);
      wrap.appendChild(area);
      wrap.appendChild(ta);
      ta.style.display = 'none';
      var sourceMode = false;
      var savedRange = null;

      function sync() { if (!sourceMode) { ta.value = area.innerHTML; } }
      function saveRange() {
        var sel = window.getSelection();
        if (sel.rangeCount && area.contains(sel.anchorNode)) { savedRange = sel.getRangeAt(0).cloneRange(); }
      }
      function restoreRange() {
        area.focus();
        if (savedRange) { var sel = window.getSelection(); sel.removeAllRanges(); sel.addRange(savedRange); }
      }
      function exec(cmd, value) { restoreRange(); doc.execCommand(cmd, false, value || null); sync(); saveRange(); }

      RICH_TOOLS.forEach(function (t) {
        if (t[0] === '|') { bar.appendChild(doc.createElement('i')); return; }
        var b = doc.createElement('button');
        b.type = 'button';
        b.innerHTML = t[1];
        b.title = t[2];
        b.addEventListener('mousedown', function (e) { e.preventDefault(); });
        b.addEventListener('click', function () {
          var cmd = t[0];
          if (cmd === 'source') {
            sourceMode = !sourceMode;
            b.classList.toggle('is-on', sourceMode);
            if (sourceMode) { ta.value = area.innerHTML; ta.style.display = ''; area.style.display = 'none'; } else { area.innerHTML = ta.value; ta.style.display = 'none'; area.style.display = ''; }
            return;
          }
          if (sourceMode) { return; }
          if (cmd === 'h2' || cmd === 'h3' || cmd === 'p' || cmd === 'blockquote') { exec('formatBlock', '<' + cmd + '>'); return; }
          if (cmd === 'link') {
            saveRange();
            var href = window.prompt('Bağlantı adresi (https://… ya da site içi yol):', 'https://');
            if (href && !/^\s*javascript:/i.test(href)) { exec('createLink', href.trim()); }
            return;
          }
          if (cmd === 'image') {
            saveRange();
            openMedia('image', function (item) { exec('insertHTML', '<p><img src="' + escapeHtml(item.url) + '" alt=""></p>'); });
            return;
          }
          exec(cmd);
        });
        bar.appendChild(b);
      });

      area.addEventListener('input', sync);
      area.addEventListener('keyup', saveRange);
      area.addEventListener('mouseup', saveRange);
      // Word vb. kaynaklardan yapıştırırken biçim çöpünü alma: düz metin olarak yapıştır.
      area.addEventListener('paste', function (e) {
        var text = (e.clipboardData || window.clipboardData).getData('text/plain');
        if (!text) { return; }
        e.preventDefault();
        var html = text.split(/\n{2,}/).map(function (p) { return '<p>' + escapeHtml(p).replace(/\n/g, '<br>') + '</p>'; }).join('');
        doc.execCommand('insertHTML', false, html);
        sync();
      });
      if (ta.form) { ta.form.addEventListener('submit', function () { if (!sourceMode) { ta.value = area.innerHTML; } }); }
      try { doc.execCommand('defaultParagraphSeparator', false, 'p'); } catch (err) { /* eski tarayıcılar */ }
    });
  }

  function init() {
    initBasics();
    initLists();
    initFields();
    initThemePreview();
    initMedia();
    initRich();
  }
  if (doc.readyState === 'loading') { doc.addEventListener('DOMContentLoaded', init); } else { init(); }
})();
