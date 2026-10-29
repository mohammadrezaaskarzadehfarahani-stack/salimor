/* Salimor theme script: progressive enhancement for server-rendered pages (no jQuery). */
(() => {
  'use strict';
  const S = window.SALIMOR || {};
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const RM = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const fa = n => String(n).replace(/\d/g, d => '۰۱۲۳۴۵۶۷۸۹'[d]);
  const store = { get(k, d) { try { const v = localStorage.getItem('slm-' + k); return v === null ? d : JSON.parse(v); } catch (e) { return d; } }, set(k, v) { try { localStorage.setItem('slm-' + k, JSON.stringify(v)); } catch (e) {} } };

  /* ---------- toast ---------- */
  let tt;
  const toast = msg => {
    const t = $('#toast'); if (!t) return;
    t.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true" style="width:16px;stroke:var(--saffron);fill:none;stroke-width:2.4"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>' + msg;
    t.classList.add('on'); clearTimeout(tt); tt = setTimeout(() => t.classList.remove('on'), 3500);
  };

  /* ---------- header shadow ---------- */
  const top = $('#top');
  if (top) addEventListener('scroll', () => top.classList.toggle('scrolled', scrollY > 6), { passive: true });

  /* ---------- tapping a link to the current page scrolls to the top ---------- */
  document.addEventListener('click', e => {
    const a = e.target.closest('a[href]');
    if (!a || e.defaultPrevented || a.target === '_blank' || e.metaKey || e.ctrlKey) return;
    const u = new URL(a.href, location.href);
    if (u.origin === location.origin && u.pathname === location.pathname && u.search === location.search && !u.hash) {
      e.preventDefault(); scrollTo({ top: 0, behavior: RM ? 'auto' : 'smooth' });
    }
  });

  /* ---------- footer link groups: open on desktop, collapsible on phones ---------- */
  const fmq = matchMedia('(min-width:760px)'), syncF = () => $$('.f-acc').forEach(d => { d.open = fmq.matches; });
  syncF(); fmq.addEventListener('change', syncF);

  /* ---------- in-page smooth scroll (hero → sale shelf) ---------- */
  document.addEventListener('click', e => {
    const b = e.target.closest('[data-scroll]'); if (!b) return;
    const t = document.getElementById(b.dataset.scroll); if (!t) return;
    e.preventDefault(); t.scrollIntoView({ behavior: RM ? 'auto' : 'smooth', block: 'start' });
  });

  /* ---------- segmented controls: sliding pill ---------- */
  const placePill = root => $$('.seg', root).forEach(s => {
    const b = $('[aria-pressed="true"],[aria-current="page"]', s), p = $('.pill', s); if (!b || !p) return;
    p.style.left = b.offsetLeft + 'px'; p.style.width = b.offsetWidth + 'px';
  });
  addEventListener('resize', () => placePill(document));
  requestAnimationFrame(() => placePill(document));
  document.fonts && document.fonts.ready.then(() => placePill(document));

  /* ---------- home: new-arrivals category filter ---------- */
  const hg = $('#homeGrid');
  $$('[data-hseg]').forEach(b => b.addEventListener('click', () => {
    $$('[data-hseg]').forEach(x => x.setAttribute('aria-pressed', String(x === b))); placePill(b.closest('.seg').parentElement);
    let shown = 0;
    $$('.pc', hg).forEach(c => { const on = !b.dataset.hseg || c.dataset.cat === b.dataset.hseg; c.hidden = !on; shown += on; });
    const em = $('#homeGridEmpty'); if (em) em.hidden = !!shown;
  }));

  /* ---------- home: shelf navigation (scroll-spy) ---------- */
  (() => {
    const btns = $$('[data-shelf]'); if (!btns.length) return;
    const nav = $('.shelf-nav'), secs = btns.map(b => document.getElementById(b.dataset.shelf));
    let cur = null, lock = null, lockT;
    const set = id => { if (id === cur) return; cur = id; btns.forEach(b => b.setAttribute('aria-pressed', String(b.dataset.shelf === id))); placePill(nav); };
    const pick = () => {
      if (lock) return set(lock);
      const line = nav.getBoundingClientRect().bottom + Math.min(160, innerHeight * .22);
      let id = secs[0].id; for (const s of secs) if (s.getBoundingClientRect().top <= line) id = s.id; set(id);
    };
    btns.forEach(b => b.addEventListener('click', () => {
      lock = b.dataset.shelf; set(lock); clearTimeout(lockT);
      document.getElementById(lock).scrollIntoView({ behavior: RM ? 'auto' : 'smooth', block: 'start' });
      const release = () => { lock = null; pick(); };
      if ('onscrollend' in window) addEventListener('scrollend', release, { once: true });
      lockT = setTimeout(release, 1200);
    }));
    let tick = false;
    addEventListener('scroll', () => { if (tick) return; tick = true; requestAnimationFrame(() => { tick = false; pick(); }); }, { passive: true });
    pick();
  })();

  /* ---------- horizontal rails: side arrows on desktop ---------- */
  $$('.rail').forEach(r => {
    const sh = document.createElement('div'); sh.className = 'rail-shell'; r.before(sh); sh.appendChild(r);
    sh.insertAdjacentHTML('beforeend', '<button class="rail-btn prev" type="button" aria-label="محصولات قبلی" data-dir="1"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button><button class="rail-btn next" type="button" aria-label="محصولات بعدی" data-dir="-1"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></button>');
    const [pv, nx] = $$('.rail-btn', sh);
    const upd = () => { const x = Math.abs(r.scrollLeft); pv.disabled = x < 4; nx.disabled = x + r.clientWidth >= r.scrollWidth - 4; };
    $$('.rail-btn', sh).forEach(b => b.addEventListener('click', () => r.scrollBy({ left: +b.dataset.dir * r.clientWidth * .8, behavior: RM ? 'auto' : 'smooth' })));
    r.addEventListener('scroll', upd, { passive: true }); requestAnimationFrame(upd);
  });

  /* ---------- sale countdown (only when a real end date exists) ---------- */
  $$('.cd-box[data-end]').forEach(el => {
    const end = +el.dataset.end * 1000;
    const tick = () => {
      let t = Math.max(0, end - Date.now()) / 1000;
      const d = Math.floor(t / 86400); t %= 86400; const h = Math.floor(t / 3600); t %= 3600; const m = Math.floor(t / 60), s = Math.floor(t % 60);
      el.innerHTML = [[d, 'روز'], [h, 'ساعت'], [m, 'دقیقه'], [s, 'ثانیه']].map(([v, l]) => `<span><b>${fa(String(v).padStart(2, '0'))}</b><small>${l}</small></span>`).join('');
      if (end <= Date.now()) el.closest('.cd').hidden = true;
    };
    tick(); setInterval(tick, 1000);
  });

  /* ---------- scroll-reveal hooks ---------- */
  $$('.sh2,.pc,.perk,.catx>*,.lead-post,.pitem,.sale-head').forEach(e => e.classList.add('rv'));

  /* ---------- AJAX helpers ---------- */
  const wc = (endpoint, body) => fetch(S.wcAjax.replace('%%endpoint%%', endpoint), { method: 'POST', credentials: 'same-origin', body }).then(r => r.json());
  const form = obj => { const f = new FormData(); Object.entries(obj).forEach(([k, v]) => f.append(k, v)); return f; };

  /* ---------- cart drawer ---------- */
  const sheet = $('#sheet'), scrim = $('#scrim');
  let lastFocus = null;
  const openers = () => $$('[data-cart-open]');
  const setCount = n => {
    $$('[data-count]').forEach(b => { const was = b.textContent; b.textContent = fa(n); b.hidden = !n; if (n && was !== fa(n) && !RM) b.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.4)' }, { transform: 'scale(1)' }], { duration: 400 }); });
    openers().forEach(o => o.setAttribute('aria-label', n ? S.i18n.cartN.replace('%s', fa(n)) : S.i18n.cart));
  };
  const applyFragments = data => {
    if (!data || !data.fragments) return;
    if (data.fragments.salimor_drawer !== undefined) $('#sheetInner').innerHTML = data.fragments.salimor_drawer;
    if (data.fragments.salimor_count !== undefined) setCount(+data.fragments.salimor_count);
    document.dispatchEvent(new CustomEvent('salimor:cart', { detail: data }));
  };
  const openSheet = () => {
    if (!sheet) return; lastFocus = document.activeElement;
    sheet.classList.add('on'); sheet.setAttribute('aria-hidden', 'false'); scrim.classList.add('on');
    openers().forEach(o => o.setAttribute('aria-expanded', 'true')); document.body.style.overflow = 'hidden';
    setTimeout(() => { const f = $('button,a', sheet); f && f.focus(); }, 80);
  };
  const closeSheet = silent => {
    if (!sheet || !sheet.classList.contains('on')) return;
    sheet.classList.remove('on'); sheet.setAttribute('aria-hidden', 'true'); scrim.classList.remove('on');
    openers().forEach(o => o.setAttribute('aria-expanded', 'false')); document.body.style.overflow = '';
    if (!silent && lastFocus && document.contains(lastFocus)) lastFocus.focus();
  };
  window.salimorOpenCart = openSheet;
  openers().forEach(o => o.addEventListener('click', openSheet));
  scrim && scrim.addEventListener('click', () => closeSheet());
  document.addEventListener('click', e => { if (e.target.closest('#sheet [data-close]')) closeSheet(); });
  document.addEventListener('keydown', e => {
    if (!sheet || !sheet.classList.contains('on')) return;
    if (e.key === 'Escape') closeSheet();
    if (e.key === 'Tab') {
      const f = $$('a[href],button:not([disabled])', sheet).filter(x => x.offsetParent !== null); if (!f.length) return;
      const a = f[0], z = f[f.length - 1];
      if (e.shiftKey && document.activeElement === a) { e.preventDefault(); z.focus(); } else if (!e.shiftKey && document.activeElement === z) { e.preventDefault(); a.focus(); }
    }
  });
  /* swipe down to close (phones) */
  if (sheet) {
    let y0 = null, dy = 0;
    sheet.addEventListener('touchstart', e => { const b = $('.sh-b', sheet); if (b && b.scrollTop > 0 && !e.target.closest('.grab,.sh-h')) return; y0 = e.touches[0].clientY; dy = 0; sheet.style.transition = 'none'; }, { passive: true });
    sheet.addEventListener('touchmove', e => { if (y0 === null) return; dy = Math.max(0, e.touches[0].clientY - y0); sheet.style.transform = `translateY(${dy}px)`; }, { passive: true });
    sheet.addEventListener('touchend', () => { if (y0 === null) return; sheet.style.transition = ''; sheet.style.transform = ''; if (dy > 110) closeSheet(); y0 = null; });
    sheet.addEventListener('click', e => {
      const q = e.target.closest('[data-q]'), r = e.target.closest('[data-rm]'); if (!q && !r) return;
      const key = (q || r).dataset.q || r.dataset.rm;
      const cur = q ? parseInt(String(q.parentElement.querySelector('.num').textContent).replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d)), 10) : 0;
      const qty = q ? Math.max(0, cur + (+q.dataset.d)) : 0;
      sheet.setAttribute('aria-busy', 'true');
      wc('salimor_update_qty', form({ key, qty, nonce: S.nonce })).then(d => { applyFragments(d); if (!qty) toast(S.i18n.removed); }).catch(() => toast(S.i18n.error)).finally(() => sheet.removeAttribute('aria-busy'));
    });
  }
  /* back/forward cache can show an old count: refresh it */
  addEventListener('pageshow', e => { if (e.persisted && S.wcAjax) wc('get_refreshed_fragments', form({})).then(applyFragments).catch(() => {}); });

  /* ---------- add to cart (cards) ---------- */
  const fly = from => {
    if (RM || !from) return;
    const to = openers().find(x => x.offsetParent !== null); if (!to) return;
    const a = from.getBoundingClientRect(), b = to.getBoundingClientRect(), d = document.createElement('div');
    d.className = 'flyer'; document.body.appendChild(d);
    const x0 = a.left + a.width / 2 - 8, y0 = a.top + a.height / 2 - 8, x1 = b.left + b.width / 2 - 8, y1 = b.top + b.height / 2 - 8;
    d.animate([{ transform: `translate(${x0}px,${y0}px)` }, { transform: `translate(${(x0 + x1) / 2}px,${Math.min(y0, y1) - 120}px) scale(1.2)`, offset: .5 }, { transform: `translate(${x1}px,${y1}px) scale(.4)`, opacity: .4 }], { duration: 750, easing: 'cubic-bezier(.5,0,.3,1)' }).onfinish = () => d.remove();
  };
  const addToCart = (id, qty, btn) => {
    btn && btn.setAttribute('aria-busy', 'true');
    return wc('add_to_cart', form({ product_id: id, quantity: qty || 1 })).then(d => {
      if (!d || d.error) { if (d && d.product_url) location.href = d.product_url; else toast(S.i18n.error); return false; }
      fly(btn); setTimeout(() => applyFragments(d), RM ? 0 : 600);
      const name = btn && btn.closest('.pc') ? $('h3', btn.closest('.pc')).textContent.trim() : '';
      toast((name ? name + ' ' : '') + S.i18n.added); return true;
    }).catch(() => { toast(S.i18n.error); return false; }).finally(() => btn && btn.removeAttribute('aria-busy'));
  };
  window.salimorAddToCart = addToCart;
  document.addEventListener('click', e => {
    const b = e.target.closest('button[data-add]'); if (!b || !S.wcAjax) return;
    e.preventDefault();
    addToCart(b.dataset.add, 1, b).then(ok => { if (!ok) return; const html = b.innerHTML; b.classList.add('ok'); b.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>'; setTimeout(() => { b.classList.remove('ok'); b.innerHTML = html; }, 1400); });
  });

  /* ---------- wishlist ---------- */
  document.addEventListener('click', e => {
    const b = e.target.closest('[data-wish]'); if (!b || !S.wcAjax) return;
    e.preventDefault();
    const id = b.dataset.wish, on = b.getAttribute('aria-pressed') !== 'true';
    $$(`[data-wish="${id}"]`).forEach(x => x.setAttribute('aria-pressed', String(on)));
    wc('salimor_wish', form({ product_id: id, nonce: S.nonce })).then(d => { if (!d || !d.success) throw 0; toast(d.data.on ? S.i18n.wishOn : S.i18n.wishOff); })
      .catch(() => { $$(`[data-wish="${id}"]`).forEach(x => x.setAttribute('aria-pressed', String(!on))); toast(S.i18n.error); });
  });

  /* ---------- shop: filters, sort, chips and paging without full reloads ---------- */
  (() => {
    const root = $('#shopRoot'), panel = $('#fpanel'); if (!root || !panel) return;
    const scrim = $('#fscrim'), mq = matchMedia('(max-width:999px)');
    let ctl = null, lastOpener = null;
    const urlFrom = () => {
      const u = new URL(panel.getAttribute('action'), location.href), g = {};
      for (const [k, v] of new FormData(panel)) { const key = k.replace(/\[\]$/, ''); (g[key] = g[key] || []).push(v); }
      Object.entries(g).forEach(([k, v]) => { if (k === 'orderby' && v[0] === S.defaultSort) return; u.searchParams.set(k, v.join(',')); });
      return u.toString();
    };
    const load = (url, opt = {}) => {
      if (ctl) ctl.abort(); ctl = new AbortController();
      const main = $('#shopMain'); main.setAttribute('aria-busy', 'true');
      const act = document.activeElement, keep = act && act.closest && act.closest('#fpb') && act.name ? [act.name, act.value] : null;
      return fetch(url, { credentials: 'same-origin', signal: ctl.signal }).then(r => r.text()).then(html => {
        const doc = new DOMParser().parseFromString(html, 'text/html'), nm = doc.getElementById('shopMain'), nb = doc.getElementById('fpb');
        if (!nm || !nb) { location.href = url; return; }
        $('#fpb').innerHTML = nb.innerHTML;
        main.innerHTML = nm.innerHTML;
        $('#fapply').textContent = doc.getElementById('fapply').textContent;
        const fc = $('#fclear'), nfc = doc.getElementById('fclear'); fc.href = nfc.href; nfc.hasAttribute('aria-disabled') ? fc.setAttribute('aria-disabled', 'true') : fc.removeAttribute('aria-disabled');
        const fo = $('#fopen'); if (fo) fo.setAttribute('aria-expanded', String(panel.classList.contains('on')));
        $$('.pc', main).forEach(e => e.classList.add('rv'));
        history[opt.push ? 'pushState' : 'replaceState']({ shop: 1 }, '', url);
        if (keep) { const el = $$('#fpb input').find(i => i.name === keep[0] && i.value === keep[1]); el && el.focus({ preventScroll: true }); }
        if (opt.scroll) { const y = $('.shop-lay').getBoundingClientRect().top + scrollY - 140; if (scrollY > y) scrollTo({ top: Math.max(0, y), behavior: RM ? 'auto' : 'smooth' }); }
        if (opt.focusMain) { const f = $('#fchips a', main) || $('#fopen') || $('#srt'); f && f.focus({ preventScroll: true }); }
      }).catch(e => { if (e.name !== 'AbortError') location.href = url; }).finally(() => main.setAttribute('aria-busy', 'false'));
    };
    panel.addEventListener('change', () => load(urlFrom()));
    document.addEventListener('change', e => { if (e.target.id === 'srt') load(urlFrom()); });
    panel.addEventListener('submit', e => { e.preventDefault(); if (panel.classList.contains('on')) closeP(); else load(urlFrom()); });
    document.addEventListener('click', e => {
      const a = e.target.closest('#shopMain .fchip, #shopMain [data-rall], #fclear, #shopMain .woocommerce-pagination a'); if (!a || e.metaKey || e.ctrlKey) return;
      e.preventDefault(); const paging = !!a.closest('.woocommerce-pagination');
      load(a.href, { push: paging, scroll: paging, focusMain: !paging });
    });
    addEventListener('popstate', e => { if (e.state && e.state.shop) load(location.href); });
    history.replaceState({ shop: 1 }, '', location.href);
    /* phones and tablets: the panel is a bottom sheet */
    const openP = () => {
      lastOpener = document.activeElement; panel.classList.add('on'); scrim.classList.add('on'); panel.setAttribute('role', 'dialog'); panel.setAttribute('aria-modal', 'true');
      const fo = $('#fopen'); fo && fo.setAttribute('aria-expanded', 'true'); document.body.style.overflow = 'hidden';
      setTimeout(() => { const i = $('input', panel); i && i.focus({ preventScroll: true }); }, 60);
    };
    const closeP = () => {
      if (!panel.classList.contains('on')) return;
      panel.classList.remove('on'); scrim.classList.remove('on'); panel.removeAttribute('role'); panel.removeAttribute('aria-modal');
      const fo = $('#fopen'); fo && fo.setAttribute('aria-expanded', 'false'); document.body.style.overflow = '';
      (fo || lastOpener) && (fo || lastOpener).focus({ preventScroll: true });
    };
    document.addEventListener('click', e => { if (e.target.closest('#fopen')) openP(); });
    scrim.addEventListener('click', closeP); $('#fclose').addEventListener('click', closeP);
    mq.addEventListener('change', () => { if (!mq.matches) closeP(); });
    panel.addEventListener('keydown', e => {
      if (!panel.classList.contains('on')) return;
      if (e.key === 'Escape') closeP();
      if (e.key === 'Tab') {
        const f = $$('input,button:not(:disabled),a[href]:not([aria-disabled])', panel).filter(x => x.offsetParent !== null), a = f[0], z = f[f.length - 1];
        if (e.shiftKey && document.activeElement === a) { e.preventDefault(); z.focus(); } else if (!e.shiftKey && document.activeElement === z) { e.preventDefault(); a.focus(); }
      }
    });
    /* swipe the sheet down to close */
    let y0 = null, dy = 0; const fb = $('#fpb');
    panel.addEventListener('touchstart', e => { if (!panel.classList.contains('on') || (fb.scrollTop > 0 && !e.target.closest('.grab,.fp-h'))) return; y0 = e.touches[0].clientY; dy = 0; panel.style.transition = 'none'; }, { passive: true });
    panel.addEventListener('touchmove', e => { if (y0 === null) return; dy = Math.max(0, e.touches[0].clientY - y0); panel.style.transform = `translateY(${dy}px)`; }, { passive: true });
    panel.addEventListener('touchend', () => { if (y0 === null) return; panel.style.transition = ''; panel.style.transform = ''; if (dy > 110) closeP(); y0 = null; });
  })();

  /* ---------- product page: gallery, options, buy bar ---------- */
  (() => {
    const dataEl = $('#pdpData'), gm = $('#gm'); if (!gm) return;
    const D = dataEl ? JSON.parse(dataEl.textContent) : null;
    /* gallery: arrows, dots, keyboard, swipe (swipe left shows the image on the right, as in RTL reading order) */
    const imgs = $$('.gi', gm), dots = $$('.dots [data-go]'); let gi = 0;
    const warm = i => { const im = imgs[(i + imgs.length) % imgs.length]; if (im && im.loading === 'lazy') im.loading = 'eager'; };
    const show = (i, anim = true) => {
      if (imgs.length < 2) return;
      gi = (i + imgs.length) % imgs.length;
      imgs.forEach((im, k) => { im.classList.toggle('on', k === gi); im.setAttribute('aria-hidden', String(k !== gi)); });
      dots.forEach((d, k) => d.setAttribute('aria-pressed', String(k === gi)));
      const c = $('#gcount'); if (c) c.textContent = `${fa(gi + 1)} از ${fa(imgs.length)}`;
      warm(gi); warm(gi + 1); warm(gi - 1);
      if (anim && !RM) imgs[gi].animate([{ opacity: 0, filter: 'blur(4px)' }, { opacity: 1, filter: 'blur(0)' }], { duration: 380, easing: 'ease-out' });
    };
    if (imgs.length > 1) {
      warm(1);
      dots.forEach(d => d.addEventListener('click', () => show(+d.dataset.go)));
      $('#gprev').addEventListener('click', () => show(gi - 1)); $('#gnext').addEventListener('click', () => show(gi + 1));
      gm.addEventListener('keydown', e => { if (e.key === 'ArrowLeft') show(gi + 1); if (e.key === 'ArrowRight') show(gi - 1); });
      let sx = null, sy = 0, dx = 0, horiz = null;
      gm.addEventListener('touchstart', e => { if (e.target.closest('button,a')) return; sx = e.touches[0].clientX; sy = e.touches[0].clientY; dx = 0; horiz = null; }, { passive: true });
      gm.addEventListener('touchmove', e => {
        if (sx === null) return; dx = e.touches[0].clientX - sx; const dy = e.touches[0].clientY - sy;
        if (horiz === null && (Math.abs(dx) > 8 || Math.abs(dy) > 8)) horiz = Math.abs(dx) > Math.abs(dy);
        if (horiz) { e.preventDefault(); const g = imgs[gi]; g.style.translate = `${dx * .6}px 0`; g.style.opacity = String(1 - Math.min(.5, Math.abs(dx) / 400)); }
      }, { passive: false });
      gm.addEventListener('touchend', () => { if (sx === null) return; const g = imgs[gi]; g.style.translate = ''; g.style.opacity = ''; if (horiz && Math.abs(dx) > 45) show(dx < 0 ? gi - 1 : gi + 1); sx = null; }, { passive: true });
    }
    if (!D) return;

    /* options (variable products) */
    const opts = $$('.opt[data-attr]'), sel = {};
    const pPrice = $('#pPrice'), bPrice = $('#bPrice'), baseHTML = pPrice ? pPrice.innerHTML : '';
    const current = () => {
      if (!D.variable) return { id: D.id, stock: true, max: D.max };
      if (opts.some(o => !sel[o.dataset.attr])) return null;
      return D.variations.find(v => Object.entries(v.attrs).every(([k, val]) => !val || val === sel[k])) || false;
    };
    const possible = (attr, val) => D.variations.some(v => v.stock && Object.entries(v.attrs).every(([k, x]) => !x || (k === attr ? x === val : (!sel[k] || x === sel[k]))));
    const paintOpts = () => opts.forEach(o => {
      const a = o.dataset.attr;
      $$('[data-val]', o).forEach(b => { const on = sel[a] === b.dataset.val; b.setAttribute('aria-pressed', String(on)); b.classList.toggle('na', !possible(a, b.dataset.val)); });
      const n = $('.sel-name', o), chosen = $(`[data-val="${CSS.escape(sel[a] || '')}"]`, o); if (n) n.textContent = chosen ? '؛ ' + chosen.dataset.label : '';
    });
    opts.forEach(o => {
      const b0 = $('[aria-pressed="true"]', o); sel[o.dataset.attr] = b0 ? b0.dataset.val : '';
      o.addEventListener('click', e => { const b = e.target.closest('[data-val]'); if (!b) return; sel[o.dataset.attr] = b.dataset.val; update(true); });
      o.addEventListener('keydown', e => {
        if (!['ArrowLeft', 'ArrowRight'].includes(e.key)) return; const bs = $$('[data-val]', o), i = bs.indexOf(document.activeElement); if (i < 0) return;
        e.preventDefault(); const nb = bs[(i + (e.key === 'ArrowLeft' ? 1 : -1) + bs.length) % bs.length]; nb.focus(); nb.click();
      });
    });

    /* buy bar */
    const add = $('#add'), ic = $('#incart'), bar = $('#buybar'), qtyBox = bar && $('.qty.d-only', bar), note = $('#bpnote'), qv = $('#qv');
    let lines = D.lines || {}, q = 1;
    const maxQ = () => { const c = current(); return c && c.max ? c.max : D.max; };
    const setQ = n => { q = Math.max(1, Math.min(maxQ(), n)); if (qv) qv.textContent = fa(q); };
    const sync = () => {
      if (!add) return;
      const c = current(), line = c && lines[c.id], soldOut = c === false || (c && !c.stock);
      ic.hidden = !line; add.hidden = !!line; if (qtyBox) qtyBox.style.display = line ? 'none' : '';
      bar.classList.toggle('in', !!line); if (line) $('#pqn').textContent = fa(line[1]);
      add.disabled = soldOut; add.textContent = soldOut ? 'این ترکیب موجود نیست' : 'افزودن به سبد';
      note.textContent = line ? 'در سبد خرید شماست' : soldOut ? 'این ترکیب موجود نیست' : 'ارسال از فردا';
    };
    function update(fromUser) {
      paintOpts();
      const c = current();
      const html = c && c.html ? c.html : baseHTML; if (pPrice) pPrice.innerHTML = html; if (bPrice) bPrice.innerHTML = html;
      if (fromUser && c && c.img) { const k = imgs.findIndex(im => +im.dataset.id === c.img); if (k >= 0 && k !== gi) show(k); }
      setQ(q); sync();
    }
    update(false);
    $('#qm') && $('#qm').addEventListener('click', () => setQ(q - 1));
    $('#qp') && $('#qp').addEventListener('click', () => setQ(q + 1));
    add && add.addEventListener('click', () => {
      if (add.dataset.state) return;
      const c = current();
      if (c === null) {
        const o = opts.find(x => !sel[x.dataset.attr]), label = $('.lb span', o).firstChild.textContent.trim();
        toast(`لطفاً ${label} را انتخاب کنید`);
        if (!RM) o.animate([{ transform: 'translateX(0)' }, { transform: 'translateX(-8px)' }, { transform: 'translateX(8px)' }, { transform: 'translateX(0)' }], { duration: 320 });
        o.scrollIntoView({ behavior: RM ? 'auto' : 'smooth', block: 'center' }); $('[data-val]', o).focus({ preventScroll: true }); return;
      }
      if (!c || !c.stock) return;
      add.dataset.state = 'ld'; add.setAttribute('aria-busy', 'true'); add.innerHTML = '<span class="ld"></span>در حال افزودن';
      const fd = form({ product_id: D.id, variation_id: D.variable ? c.id : 0, quantity: q });
      Object.entries(sel).forEach(([k, v]) => fd.append(`attributes[${k}]`, v));
      wc('salimor_add', fd).then(d => {
        if (!d || d.error) { toast((d && d.message) || S.i18n.error); return; }
        fly(add); setTimeout(() => applyFragments(d), RM ? 0 : 600);
        toast(add.dataset.name + ' ' + S.i18n.added); setQ(1);
      }).catch(() => toast(S.i18n.error)).finally(() => {
        delete add.dataset.state; add.removeAttribute('aria-busy'); add.textContent = 'افزودن به سبد';
        setTimeout(() => { sync(); const vc = $('#viewCart'); if (!ic.hidden && vc) vc.focus({ preventScroll: true }); }, RM ? 0 : 620);
      });
    });
    ic && ic.addEventListener('click', e => {
      const b = e.target.closest('[data-pq]'); const c = current(), line = c && lines[c.id]; if (!b || !line) return;
      const qty = Math.max(0, line[1] + (+b.dataset.pq)); if (qty > maxQ()) return toast('بیشتر از این تعداد موجود نیست');
      ic.setAttribute('aria-busy', 'true');
      wc('salimor_update_qty', form({ key: line[0], qty, nonce: S.nonce })).then(d => { applyFragments(d); if (!qty) { toast(S.i18n.removed); add.focus(); } })
        .catch(() => toast(S.i18n.error)).finally(() => ic.removeAttribute('aria-busy'));
    });
    $('#viewCart') && $('#viewCart').addEventListener('click', () => window.salimorOpenCart && window.salimorOpenCart());
    document.addEventListener('salimor:cart', e => { const f = e.detail && e.detail.fragments; if (f && f.salimor_lines !== undefined) { lines = f.salimor_lines || {}; sync(); } });
  })();

  /* ---------- support menu ---------- */
  (() => {
    const fab = $('#chatFab'), menu = $('#supMenu'), ms = $('#supScrim'); if (!fab || !menu) return;
    const show = on => {
      menu.hidden = !on; ms.hidden = !on; fab.setAttribute('aria-expanded', String(on)); fab.classList.toggle('x', on);
      if (on) requestAnimationFrame(() => { menu.classList.add('on'); const f = $('.sup-i', menu); f && f.focus(); }); else menu.classList.remove('on');
    };
    fab.addEventListener('click', () => show(menu.hidden)); ms.addEventListener('click', () => show(false));
    menu.addEventListener('keydown', e => { if (e.key === 'Escape') { show(false); fab.focus(); } });
    menu.addEventListener('click', e => {
      const i = e.target.closest('.sup-i'); if (!i) return; show(false);
      if (i.dataset.sup !== 'chat') return;
      /* open the installed live-chat provider, whichever it is */
      const w = window;
      try {
        if (w.Goftino && w.Goftino.open) return w.Goftino.open();
        if (w.Goftino && w.Goftino.toggle) return w.Goftino.toggle();
        if (w.Raychat && w.Raychat.open) return w.Raychat.open();
        if (w.$crisp) return w.$crisp.push(['do', 'chat:open']);
        if (w.Tawk_API && w.Tawk_API.maximize) return w.Tawk_API.maximize();
      } catch (err) {}
      toast('پنجره‌ی گفتگو در گوشه‌ی صفحه باز است.');
    });
  })();

  /* ---------- install as an app (PWA) ---------- */
  (() => {
    const hint = $('#installHint'); if (!hint) return;
    const standalone = matchMedia('(display-mode: standalone)').matches || navigator.standalone;
    if (standalone || innerWidth >= 760) return;
    const views = store.get('views', 0) + 1; store.set('views', views);
    if (views < 2 || Date.now() - store.get('ih-dismissed', 0) < 30 * 864e5) return;
    const ios = /iphone|ipad|ipod/i.test(navigator.userAgent) && /safari/i.test(navigator.userAgent) && !/crios|fxios/i.test(navigator.userAgent);
    let deferred = null;
    const close = () => { hint.hidden = true; store.set('ih-dismissed', Date.now()); };
    $('#ihX').addEventListener('click', close);
    addEventListener('beforeinstallprompt', e => { e.preventDefault(); deferred = e; hint.hidden = false; });
    $('#ihBtn').addEventListener('click', async () => {
      if (deferred) { deferred.prompt(); await deferred.userChoice.catch(() => {}); deferred = null; close(); }
      else close();
    });
    if (ios) {
      $('#ihText').innerHTML = 'دکمه‌ی اشتراک‌گذاری <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 15V4M8 8l4-4 4 4M6 12v7a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1v-7"/></svg> و سپس «Add to Home Screen» را بزنید.';
      $('#ihBtn').textContent = 'متوجه شدم'; hint.hidden = false;
    }
    addEventListener('appinstalled', close);
  })();
})();
