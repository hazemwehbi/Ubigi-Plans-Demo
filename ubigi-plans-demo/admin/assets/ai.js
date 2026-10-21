/* global wp */
(() => {
 'use strict'; const form = document.querySelector('#upd-ai'); if (!form) return;
 const { __ } = wp.i18n;
 form.addEventListener('submit', async e => {
  e.preventDefault(); const button = form.querySelector('button'), status = form.querySelector('[role=status]'), output = form.querySelector('textarea');
  button.disabled = true; output.value = ''; status.textContent = __('Generating draft…', 'ubigi-plans-demo');
  try {
   const data = Object.fromEntries(new FormData(form)); data.id = Number(data.id);
   const response = await fetch(form.dataset.endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': form.dataset.nonce }, body: JSON.stringify(data) });
   const result = await response.json(); if (!response.ok) throw new Error(result.message);
   output.value = result.draft; status.textContent = __('Draft ready for review. Nothing has been published.', 'ubigi-plans-demo');
  } catch (error) { status.textContent = error.message || __('Request failed.', 'ubigi-plans-demo'); }
  finally { button.disabled = false; }
 });
})();
