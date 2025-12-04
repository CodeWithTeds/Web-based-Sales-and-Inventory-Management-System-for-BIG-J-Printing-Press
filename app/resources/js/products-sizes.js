// Handles size selection and quantities for product create/edit forms,
// and optional material quantity defaults on create.

function parseJsonAttr(el, attr) {
    try {
        const raw = el?.getAttribute(attr);
        if (!raw) return undefined;
        return JSON.parse(raw);
    } catch (_) {
        return undefined;
    }
}

function getSelectedCategoryId(select) {
    if (!select) return null;
    const opt = select.options[select.selectedIndex];
    return opt ? opt.getAttribute('data-id') : null;
}

async function fetchSizesByCategoryId(categoryId) {
    if (!categoryId) return [];
    const res = await fetch(`/sizes/by-category/${categoryId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
    });
    if (!res.ok) throw new Error('Failed to fetch sizes');
    const json = await res.json();
    return Array.isArray(json.items) ? json.items : (json.sizes || []);
}

function renderSizes(container, sizes, preselected, preselectedQty) {
    container.innerHTML = '';
    if (!sizes || sizes.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-sm">No sizes available for the selected category.</p>';
        return;
    }

    const fragment = document.createDocumentFragment();
    const selected = Array.isArray(preselected) ? preselected.map(v => String(v)) : [];
    const qtyMap = preselectedQty && typeof preselectedQty === 'object' ? preselectedQty : {};

    sizes.forEach((size) => {
        const isChecked = selected.includes(String(size.id));

        const wrapper = document.createElement('div');
        wrapper.className = 'inline-flex items-center space-x-2 px-3 py-2 border rounded-md hover:bg-gray-50';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.name = 'size_ids[]';
        checkbox.value = String(size.id);
        checkbox.checked = !!isChecked;
        checkbox.className = 'rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500';

        const label = document.createElement('span');
        label.textContent = size.name;
        label.className = 'text-sm text-gray-700';

        const qty = document.createElement('input');
        qty.type = 'number';
        qty.min = '0';
        qty.step = '1';
        qty.name = `size_quantities[${size.id}]`;
        qty.placeholder = 'Qty';
        qty.className = 'block w-20 rounded-md border-gray-300 text-sm';

        const presetQty = qtyMap[String(size.id)] ?? qtyMap[Number(size.id)] ?? '';
        qty.value = isChecked ? (presetQty !== '' ? presetQty : '0') : '';
        qty.disabled = !isChecked;

        checkbox.addEventListener('change', () => {
            qty.disabled = !checkbox.checked;
            if (checkbox.checked && !qty.value) qty.value = '0';
            if (!checkbox.checked) qty.value = '';
        });

        wrapper.appendChild(checkbox);
        wrapper.appendChild(label);
        wrapper.appendChild(qty);
        fragment.appendChild(wrapper);
    });

    container.appendChild(fragment);
}

function wireMaterialDefaultQuantities(form) {
    const materialCheckboxes = document.querySelectorAll('input[name="material_ids[]"]');
    if (!materialCheckboxes.length || !form) return;
    materialCheckboxes.forEach((checkbox) => {
        checkbox.addEventListener('change', function () {
            const materialId = this.value;
            const hiddenInputName = `quantities[${materialId}]`;
            const existing = form.querySelector(`input[name="${hiddenInputName}"]`);
            if (existing) existing.remove();
            if (this.checked) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = hiddenInputName;
                hidden.value = '1';
                form.appendChild(hidden);
            }
        });
    });
}

function initProductsSizes() {
    const form = document.querySelector('form');
    const categorySelect = document.getElementById('category');
    const sizesContainer = document.getElementById('sizes-container') || document.getElementById('sizeCheckboxesContainer');
    if (!sizesContainer || !categorySelect) return;

    // Read preselected values from data attributes (injected by Blade as JSON strings)
    const preselectedSizes = parseJsonAttr(sizesContainer, 'data-preselected-sizes') || [];
    const preselectedSizeQuantities = parseJsonAttr(sizesContainer, 'data-preselected-size-quantities') || {};

    async function loadAndRender() {
        try {
            const catId = getSelectedCategoryId(categorySelect);
            const sizes = await fetchSizesByCategoryId(catId);
            renderSizes(sizesContainer, sizes, preselectedSizes, preselectedSizeQuantities);
        } catch (e) {
            console.error(e);
            sizesContainer.innerHTML = '<p class="text-sm text-red-600">Failed to load sizes. Please try again.</p>';
        }
    }

    // Initial render
    loadAndRender();

    // React to category changes
    categorySelect.addEventListener('change', loadAndRender);

    // Wire material defaults if present (create page)
    wireMaterialDefaultQuantities(form);
}

// Activate on DOM ready
document.addEventListener('DOMContentLoaded', initProductsSizes);

