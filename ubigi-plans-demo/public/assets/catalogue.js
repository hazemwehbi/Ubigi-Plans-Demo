/* global wp */
(() => {
 'use strict';
 const { __, sprintf } = wp.i18n;
 document.querySelectorAll('.upd-app[data-api]').forEach(app => {
  const form = app.querySelector('form'), grid = app.querySelector('.upd-grid'), status = app.querySelector('.upd-status');
  const prev = app.querySelector('[data-prev]'), next = app.querySelector('[data-next]'), indicator = app.querySelector('[data-page]');
  let page = 1, controller;
  const initial = new URLSearchParams(location.search).get('destination');
  if (initial) form.elements.country.value = initial;
  const element = (tag, text, cls) => { const el = document.createElement(tag); if (text !== undefined) el.textContent = text; if (cls) el.className = cls; return el; };
  async function load() {
   if (controller) controller.abort();
   controller = new AbortController(); const active = controller;
   status.textContent = __('Loading plans…', 'ubigi-plans-demo'); grid.setAttribute('aria-busy', 'true'); prev.disabled = next.disabled = true;
   const url = new URL(app.dataset.api + 'plans');
   for (const [key, val] of new FormData(form)) if (val !== '') url.searchParams.set(key, val);
   url.searchParams.set('page', page); url.searchParams.set('per_page', app.dataset.size);
   try {
    const response = await fetch(url, { signal: active.signal });
    const data = await response.json();
    if (!response.ok) throw new Error(data.message || __('Unable to load plans.', 'ubigi-plans-demo'));
    grid.replaceChildren();
    for (const p of data.items) {
     const card = element('article', undefined, 'upd-card');
     card.append(element('span', p.type.toUpperCase(), 'upd-eyebrow'), element('h3', p.name), element('p', p.destinations.map(t => t.name).join(' · ')));
     card.append(element('div', p.gb + ' GB', 'upd-data'), element('p', sprintf(__('%d days validity', 'ubigi-plans-demo'), p.days)));
     let price = p.price + ' ' + p.currency;
     try { price = new Intl.NumberFormat(document.documentElement.lang || 'en', { style: 'currency', currency: p.currency }).format(p.price); } catch (_) { /* ISO fallback */ }
     card.append(element('div', price, 'upd-price'));
     const link = element('a', __('View plan', 'ubigi-plans-demo'), 'upd-link');
     const target = new URL(p.url, location.href); if (['https:', 'http:'].includes(target.protocol)) link.href = target.href;
     card.append(link); grid.append(card);
    }
    status.textContent = data.total ? sprintf(__('%d demo plans found. Prices are in each plan’s listed currency.', 'ubigi-plans-demo'), data.total) : __('No matching plans. Try changing your filters.', 'ubigi-plans-demo');
    indicator.textContent = sprintf(__('Page %1$d of %2$d', 'ubigi-plans-demo'), page, Math.max(1, data.pages));
    prev.disabled = page <= 1; next.disabled = page >= data.pages;
   } catch (error) {
    if (error.name === 'AbortError') return;
    grid.replaceChildren(); status.textContent = __('Request failed. Check your connection and try again.', 'ubigi-plans-demo') + ' ' + error.message;
    const retry = element('button', __('Retry', 'ubigi-plans-demo')); retry.type = 'button'; retry.addEventListener('click', load); grid.append(retry); indicator.textContent = '';
   } finally { if (active === controller) grid.setAttribute('aria-busy', 'false'); }
  }
  form.addEventListener('submit', e => { e.preventDefault(); page = 1; load(); });
  form.addEventListener('reset', () => { page = 1; setTimeout(load, 0); });
  prev.addEventListener('click', () => { page--; load(); }); next.addEventListener('click', () => { page++; load(); });
  load();
 });
})();
