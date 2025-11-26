/**
 * Quick Purchase Order (Admin) — mirrors client quick-pr.js
 * One customer, multiple items, same guided selection flow.
 */
(function () {
  'use strict';

  // --- Element cache ---
  const els = {
    catSel: null,
    prodSel: null,
    sizeSel: null,
    paperSel: null,
    qtyInp: null,
    unitInp: null,
    priceInp: null,
    plySection: null,
    plySel: null,
    plyColors: null,
    purpose: null,
    payload: null,
    form: null,
    resetBtn: null,
    addBtn: null,
    itemsList: null,
  };

  // --- State ---
  const state = {
    items: [], // { type:'products', id, qty, name?, unit? }
  };

  // --- API layer (admin endpoints) ---
  const api = {
    async getCategories() {
      const res = await fetch('/admin/products/by-category', {
        headers: { Accept: 'application/json' },
      });
      const json = await res.json();
      const cats = json?.data?.categories ?? json?.data?.items ?? [];
      // normalize: support array of strings or array of objects with name
      return Array.isArray(cats) ? cats : [];
    },
    async getPaperTypes() {
      const res = await fetch('/admin/products/paper-types', {
        headers: { Accept: 'application/json' },
      });
      const json = await res.json();
      return Array.isArray(json?.data?.paper_types) ? json.data.paper_types : [];
    },
    async getProductsByCategory(categoryName) {
      const res = await fetch(
        '/admin/products/by-category/' + encodeURIComponent(categoryName),
        { headers: { Accept: 'application/json' } }
      );
      const json = await res.json();
      if (Array.isArray(json?.data?.items?.data)) return json.data.items.data;
      if (Array.isArray(json?.data?.items)) return json.data.items;
      return [];
    },
    async getSizesByProduct(productId) {
      const res = await fetch(`/admin/products/${productId}/sizes`, {
        headers: { Accept: 'application/json' },
      });
      const json = await res.json();
      return Array.isArray(json?.data?.sizes) ? json.data.sizes : [];
    },
  };

  // --- View helpers ---
  const view = {
    setDisabled(selectEl, disabled, placeholderText) {
      if (!selectEl) return;
      selectEl.disabled = !!disabled;
      if (typeof placeholderText === 'string') {
        selectEl.innerHTML = `<option value="">${placeholderText}</option>`;
      }
    },
    populateSelect(selectEl, items, getValue, getText, placeholder) {
      if (!selectEl) return;
      selectEl.innerHTML = `<option value="">${placeholder || 'Select...'}</option>`;
      items.forEach((it) => {
        const opt = document.createElement('option');
        opt.value = String(getValue(it));
        opt.textContent = String(getText(it));
        if (it.name) opt.dataset.name = it.name;
        if (typeof it.unit === 'string') opt.dataset.unit = it.unit;
        if (typeof it.price !== 'undefined') opt.dataset.price = it.price;
        selectEl.appendChild(opt);
      });
      selectEl.disabled = false;
    },
    renderPaperTypes(types) {
      this.populateSelect(
        els.paperSel,
        types,
        (t) => (typeof t === 'string' ? t : t.paper_type || t),
        (t) => (typeof t === 'string' ? t : t.paper_type || t),
        'Select a paper type...'
      );
    },
    renderProducts(products) {
      this.populateSelect(els.prodSel, products, (p) => p.id, (p) => p.name, 'Select a product...');
    },
    renderSizes(sizes) {
      this.populateSelect(
        els.sizeSel,
        sizes,
        (s) => s.id,
        (s) => s.name || `${s.width ?? ''}×${s.height ?? ''}`,
        'Select a size...'
      );
    },
    resetProductAndSize() {
      this.setDisabled(els.prodSel, true, 'Select a product...');
      this.setDisabled(els.sizeSel, true, 'Select a size...');
      if (els.unitInp) els.unitInp.value = '';
      if (els.priceInp) els.priceInp.value = '';
    },
    togglePlySection(show) {
      if (!els.plySection) return;
      if (show) {
        els.plySection.classList.remove('hidden');
      } else {
        els.plySection.classList.add('hidden');
        if (els.plySel) els.plySel.value = '';
        if (els.plyColors) {
          els.plyColors.classList.add('hidden');
          els.plyColors.innerHTML = '';
        }
      }
    },
    renderPlyColors(n) {
      if (!els.plyColors) return;
      els.plyColors.innerHTML = '';
      if (!n || n < 2) {
        els.plyColors.classList.add('hidden');
        return;
      }
      els.plyColors.classList.remove('hidden');
      const colorOptions = ['White', 'Blue', 'Pink', 'Green', 'Yellow'];
      const wrapper = document.createElement('div');
      wrapper.className = 'grid grid-cols-1 md:grid-cols-2 gap-4';
      for (let i = 1; i <= n; i++) {
        const div = document.createElement('div');
        const label = document.createElement('label');
        label.className = 'block text-xs font-medium text-slate-700';
        label.textContent = `Color for Ply ${i}`;
        const sel = document.createElement('select');
        sel.className = 'mt-1 block w-full rounded-md border-slate-300 text-sm';
        sel.required = true;
        sel.name = `ply_color_${i}`;
        sel.innerHTML = '<option value="">Select a color...</option>' + colorOptions.map((c) => `<option value="${c}">${c}</option>`).join('');
        div.appendChild(label);
        div.appendChild(sel);
        wrapper.appendChild(div);
      }
      els.plyColors.appendChild(wrapper);
    },
    renderItems(items) {
      if (!els.itemsList) return;
      if (!items.length) {
        els.itemsList.innerHTML = '<div class="p-4 text-sm text-zinc-500">No items added yet. Select options above and click Add Item.</div>';
        return;
      }
      const fmt = (n) => `₱${Number(n || 0).toFixed(2)}`;
      const rows = items
        .map((it, idx) => {
          const name = it.name || `Product #${it.id}`;
          const qty = it.qty || 0;
          const unit = it.unit || '';
          const price = Number(it.price || 0);
          const total = price * qty;
          return `
            <tr class="border-t border-zinc-100">
              <td class="px-4 py-3 text-sm text-zinc-800">${name}</td>
              <td class="px-4 py-3 text-sm text-zinc-800">${qty}</td>
              <td class="px-4 py-3 text-sm text-zinc-800">${unit}</td>
              <td class="px-4 py-3 text-sm text-zinc-800">${fmt(price)}</td>
              <td class="px-4 py-3 text-sm text-zinc-800">${fmt(total)}</td>
              <td class="px-4 py-3 text-right">
                <button type="button" data-remove-index="${idx}" class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-xs font-medium text-zinc-700 border border-zinc-300 hover:brightness-95">Remove</button>
              </td>
            </tr>
          `;
        })
        .join('');
      els.itemsList.innerHTML = `
        <table class="min-w-full">
          <thead>
            <tr>
              <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500">Product</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500">Qty</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500">Unit</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500">Price</th>
              <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500">Total</th>
              <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500">Action</th>
            </tr>
          </thead>
          <tbody>${rows}</tbody>
        </table>
      `;
    },
  };

  // --- Utilities ---
  const utils = {
    isReceiptSelected() {
      const opt = els.prodSel?.selectedOptions?.[0];
      const name = opt?.dataset?.name || '';
      return /receipt/i.test(name);
    },
    buildItemsPayload(items) {
      const payload = items.map((it) => ({ type: 'products', id: parseInt(it.id, 10), qty: parseInt(it.qty, 10) }));
      return JSON.stringify(payload);
    },
    clearSelections() {
      if (!els.catSel || !els.prodSel || !els.sizeSel || !els.paperSel || !els.qtyInp || !els.purpose || !els.payload) return;
      els.catSel.value = '';
      view.resetProductAndSize();
      els.paperSel.value = '';
      els.qtyInp.value = '1';
      els.purpose.value = '';
      els.payload.value = '';
      view.togglePlySection(false);
      state.items = [];
      view.renderItems(state.items);
    },
  };

  // --- Event handlers ---
  async function onCategoryChange() {
    const cat = els.catSel.value;
    if (!cat) {
      view.resetProductAndSize();
      return;
    }
    view.setDisabled(els.prodSel, true, 'Loading products...');
    view.setDisabled(els.sizeSel, true, 'Select a size...');
    try {
      const products = await api.getProductsByCategory(cat);
      view.renderProducts(products);
    } catch (e) {
      els.prodSel.innerHTML = '<option value="">Failed to load products</option>';
    }
  }

  async function onProductChange() {
    const pid = els.prodSel.value;
    if (!pid) {
      view.setDisabled(els.sizeSel, true, 'Select a size...');
      if (els.unitInp) els.unitInp.value = '';
      if (els.priceInp) els.priceInp.value = '';
      view.togglePlySection(false);
      return;
    }
    const opt = els.prodSel?.selectedOptions?.[0];
    const unit = opt?.dataset?.unit || '';
    const priceRaw = opt?.dataset?.price;
    const price = priceRaw != null ? parseFloat(priceRaw) : NaN;
    if (els.unitInp) els.unitInp.value = unit;
    if (els.priceInp) {
      els.priceInp.value = Number.isFinite(price) ? `₱${price.toFixed(2)}` : '';
    }
    view.setDisabled(els.sizeSel, true, 'Loading sizes...');
    try {
      const sizes = await api.getSizesByProduct(pid);
      view.renderSizes(sizes);
    } catch (e) {
      els.sizeSel.innerHTML = '<option value="">Failed to load sizes</option>';
    }
    view.togglePlySection(utils.isReceiptSelected());
  }

  function onPlyChange() {
    const n = parseInt(els.plySel.value || '0', 10);
    view.renderPlyColors(n);
  }

  function onResetClick(e) {
    e.preventDefault();
    utils.clearSelections();
  }

  function onAddItemClick(e) {
    e.preventDefault();
    const cat = els.catSel.value;
    const pid = els.prodSel.value;
    const sizeId = els.sizeSel.value;
    const paper = els.paperSel.value;
    const qty = parseInt(els.qtyInp.value || '0', 10);
    if (!cat || !pid || !sizeId || qty < 1) {
      alert('Please select category, product, size, and a valid quantity.');
      return;
    }
    const prodOpt = els.prodSel?.selectedOptions?.[0];
    const prodName = prodOpt?.dataset?.name || prodOpt?.textContent || '';
    const unit = prodOpt?.dataset?.unit || '';
    const priceRaw2 = prodOpt?.dataset?.price;
    const price2 = priceRaw2 != null ? parseFloat(priceRaw2) : 0;
    const item = { type: 'products', id: pid, qty: qty, name: prodName, unit: unit, price: Number.isFinite(price2) ? price2 : 0 };
    state.items.push(item);
    view.renderItems(state.items);
    // optional purpose enrichment
    const isReceipt = /receipt/i.test(prodName || '');
    const ply = isReceipt ? parseInt(els.plySel.value || '0', 10) : 0;
    const colors = [];
    if (isReceipt && ply >= 2) {
      for (let i = 1; i <= ply; i++) {
        const sel = els.form.querySelector(`[name="ply_color_${i}"]`);
        colors.push(sel?.value || '');
      }
    }
    const sizeText = els.sizeSel?.selectedOptions?.[0]?.textContent || sizeId;
    const note = `• ${prodName} — Size: ${sizeText}; Paper: ${paper || 'n/a'}; Qty: ${qty} ${unit || ''}${ply >= 2 ? `; Ply: ${ply}; Colors: ${colors.join(', ')}` : ''}`;
    if (!els.purpose.value) {
      els.purpose.value = note;
    } else {
      els.purpose.value = els.purpose.value + '\n' + note;
    }
    // reset selections for next add
    view.setDisabled(els.prodSel, true, 'Select a product...');
    view.setDisabled(els.sizeSel, true, 'Select a size...');
    els.qtyInp.value = '1';
    els.paperSel.value = '';
    if (els.unitInp) els.unitInp.value = '';
    if (els.priceInp) els.priceInp.value = '';
    view.togglePlySection(false);
  }

  function onFormSubmit(e) {
    if (!state.items.length) {
      e.preventDefault();
      alert('Please add at least one item to the order.');
      return;
    }
    els.payload.value = utils.buildItemsPayload(state.items);
  }

  // --- Init ---
  async function init() {
    els.catSel = document.getElementById('poCategory');
    els.prodSel = document.getElementById('poProduct');
    els.sizeSel = document.getElementById('poSize');
    els.paperSel = document.getElementById('poPaperType');
    els.qtyInp = document.getElementById('poQty');
    els.unitInp = document.getElementById('poUnit');
    els.priceInp = document.getElementById('poPrice');
    els.plySection = document.getElementById('plySection');
    els.plySel = document.getElementById('poPly');
    els.plyColors = document.getElementById('plyColors');
    els.purpose = document.getElementById('poPurpose');
    els.payload = document.getElementById('poItemsPayload');
    els.form = document.getElementById('poForm');
    els.resetBtn = document.getElementById('poReset');
    els.addBtn = document.getElementById('poAddItem');
    els.itemsList = document.getElementById('poItemsList');

    if (!els.form) return;

    utils.clearSelections();

    // Load categories from server (admin endpoint)
    try {
      const cats = await api.getCategories();
      // Items may be strings or objects; map generically
      view.populateSelect(
        els.catSel,
        cats,
        (c) => (typeof c === 'string' ? c : c.name || c),
        (c) => (typeof c === 'string' ? c : c.name || c),
        'Select a category...'
      );
    } catch (e) {
      els.catSel.innerHTML = '<option value="">Failed to load categories</option>';
    }

    try {
      const types = await api.getPaperTypes();
      view.renderPaperTypes(types);
    } catch (e) {
      view.renderPaperTypes(['Ordinary', 'Carbon', 'Newsprint']);
    }

    els.catSel?.addEventListener('change', onCategoryChange);
    els.prodSel?.addEventListener('change', onProductChange);
    els.plySel?.addEventListener('change', onPlyChange);
    els.resetBtn?.addEventListener('click', onResetClick);
    els.addBtn?.addEventListener('click', onAddItemClick);
    els.form?.addEventListener('submit', onFormSubmit);

    els.itemsList?.addEventListener('click', (ev) => {
      const btn = ev.target.closest('[data-remove-index]');
      if (!btn) return;
      const idx = parseInt(btn.getAttribute('data-remove-index'), 10);
      if (Number.isNaN(idx)) return;
      state.items.splice(idx, 1);
      view.renderItems(state.items);
    });

    const status = document.getElementById('poStatus')?.getAttribute('data-message');
    if (status) alert(status);
  }

  document.addEventListener('DOMContentLoaded', init);
  window.QuickPO = { init };
})();