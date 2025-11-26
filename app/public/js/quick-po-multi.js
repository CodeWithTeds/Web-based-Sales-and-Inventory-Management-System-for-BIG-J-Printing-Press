/**
 * Quick PO (Multi-Order) — Admin can add multiple sub-orders into one PO.
 */
(function () {
  'use strict';

  const els = {
    ordersWrap: null,
    addOrderBtn: null,
    payload: null,
    form: null,
    errors: null,
  };

  const api = {
    async getProductsByCategory(category) {
      const url = '/admin/products/by-category/' + encodeURIComponent(category);
      const res = await fetch(url, { headers: { Accept: 'application/json' } });
      const json = await res.json();
      if (Array.isArray(json?.data?.items?.data)) return json.data.items.data;
      if (Array.isArray(json?.data?.items)) return json.data.items;
      return [];
    },
    async getCategories() {
      const res = await fetch('/admin/products/by-category', { headers: { Accept: 'application/json' } });
      const json = await res.json();
      return Array.isArray(json?.data?.categories) ? json.data.categories : [];
    },
  };

  function buildOrderGroup(index, categories) {
    const catOptions = ['<option value="">Select a category...</option>']
      .concat(categories.map((c) => `<option value="${c.name || c}">${c.name || c}</option>`))
      .join('');
    const group = document.createElement('div');
    group.className = 'rounded-lg border border-slate-200 bg-white p-4 shadow-sm';
    group.dataset.orderIndex = String(index);
    group.innerHTML = `
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-medium text-slate-700">Customer Name</label>
          <input type="text" class="mt-1 block w-full rounded-md border-slate-300 text-sm" placeholder="optional" data-field="customer_name">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-700">Customer Email</label>
          <input type="email" class="mt-1 block w-full rounded-md border-slate-300 text-sm" placeholder="optional" data-field="customer_email">
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-700">Category</label>
          <select class="mt-1 block w-full rounded-md border-slate-300 text-sm" data-field="category">${catOptions}</select>
        </div>
      </div>

      <div data-list class="mt-4 space-y-2">
        <div class="text-sm text-slate-600">Choose a category to load products.</div>
      </div>

      <div class="mt-4 flex items-center justify-end gap-2">
        <button type="button" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-xs font-medium text-zinc-700 border border-zinc-300 hover:brightness-95" data-action="remove">Remove Order</button>
      </div>
    `;
    return group;
  }

  async function onCategoryChange(ev) {
    const sel = ev.target;
    if (sel.getAttribute('data-field') !== 'category') return;
    const group = sel.closest('[data-order-index]');
    const list = group.querySelector('[data-list]');
    const cat = sel.value;
    if (!cat) {
      list.innerHTML = '<div class="text-sm text-slate-600">Choose a category to load products.</div>';
      return;
    }
    list.innerHTML = '<div class="text-sm text-slate-600">Loading products...</div>';
    try {
      const products = await api.getProductsByCategory(cat);
      const cards = products.map((p) => {
        const thumb = p.image_path ? p.image_path : '/images/logo.png';
        return `
          <div class="flex items-center gap-3 rounded border border-slate-200 p-2">
            <input type="checkbox" class="rounded" data-item-type="products" data-item-id="${p.id}">
            <div class="h-10 w-10 shrink-0 overflow-hidden rounded border border-slate-200 bg-slate-50">
              <img src="${thumb}" alt="${p.name}" class="h-full w-full object-cover">
            </div>
            <div class="flex-1">
              <div class="text-sm font-medium">${p.name}</div>
              <div class="text-xs text-slate-500">Category: ${p.category ?? '—'}</div>
            </div>
            <div class="w-24">
              <input type="number" min="1" class="qty-input mt-1 block w-full rounded-md border-slate-300 text-sm" placeholder="Qty" data-item-type="products" data-item-id="${p.id}" value="1">
            </div>
          </div>
        `;
      }).join('');
      list.innerHTML = `<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">${cards}</div>`;
    } catch (e) {
      list.innerHTML = '<div class="text-sm text-red-600">Failed to load products.</div>';
    }
  }

  function collectOrdersPayload() {
    const groups = els.ordersWrap.querySelectorAll('[data-order-index]');
    const payload = [];
    groups.forEach((group) => {
      const idx = parseInt(group.dataset.orderIndex || '0', 10);
      const custName = group.querySelector('[data-field="customer_name"]')?.value || '';
      const custEmail = group.querySelector('[data-field="customer_email"]')?.value || '';
      const items = [];
      const checks = group.querySelectorAll('input[type="checkbox"][data-item-type="products"]');
      checks.forEach((chk) => {
        if (!chk.checked) return;
        const id = parseInt(chk.getAttribute('data-item-id'), 10);
        const qtyInput = group.querySelector(`input.qty-input[data-item-type="products"][data-item-id="${id}"]`);
        const qty = parseInt(qtyInput?.value || '0', 10);
        if (Number.isInteger(id) && id > 0 && Number.isInteger(qty) && qty > 0) {
          items.push({ id, qty });
        }
      });
      if (items.length) {
        payload.push({ customer_name: custName, customer_email: custEmail, items });
      }
    });
    return JSON.stringify(payload);
  }

  async function addOrderGroup() {
    try {
      const categories = await api.getCategories();
      const index = els.ordersWrap.querySelectorAll('[data-order-index]').length + 1;
      const group = buildOrderGroup(index, categories);
      els.ordersWrap.appendChild(group);
    } catch (e) {
      alert('Failed to load categories for new order group.');
    }
  }

  function onRemoveClick(ev) {
    const btn = ev.target.closest('[data-action="remove"]');
    if (!btn) return;
    const group = btn.closest('[data-order-index]');
    group?.remove();
  }

  function onFormSubmit(ev) {
    const payload = collectOrdersPayload();
    try {
      const arr = JSON.parse(payload);
      if (!Array.isArray(arr) || arr.length === 0) {
        ev.preventDefault();
        alert('Please add at least one order and select items.');
        return;
      }
      els.payload.value = payload;
    } catch (_) {
      ev.preventDefault();
      alert('Invalid orders selection.');
    }
  }

  async function init() {
    els.ordersWrap = document.getElementById('poOrders');
    els.addOrderBtn = document.getElementById('poAddOrder');
    els.payload = document.getElementById('poOrdersPayload');
    els.form = document.getElementById('poForm');
    els.errors = document.getElementById('poFormErrors');

    if (!els.form) return;

    // Start with one order group
    await addOrderGroup();

    // Wire events
    els.addOrderBtn?.addEventListener('click', addOrderGroup);
    els.ordersWrap?.addEventListener('change', onCategoryChange);
    els.ordersWrap?.addEventListener('click', onRemoveClick);
    els.form?.addEventListener('submit', onFormSubmit);

    const msg = els.errors?.getAttribute('data-message');
    if (msg) alert(msg);
  }

  document.addEventListener('DOMContentLoaded', init);
})();