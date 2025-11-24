// Purchase Requests page scripts (Step 1/2/3)
// Keeps logic scoped per page by checking for specific DOM hooks.

document.addEventListener('DOMContentLoaded', () => {
  // --- Step 2: Select Products form behaviors ---
  const form = document.getElementById('prForm');
  if (form) {
    const itemsPayload = document.getElementById('itemsPayload');
    const resetBtn = document.getElementById('resetBtn');

    function clearSelections() {
      document
        .querySelectorAll('input[type="checkbox"][data-item-type]')
        .forEach((cb) => {
          cb.checked = false;
        });
      document
        .querySelectorAll('input.qty-input[data-item-type]')
        .forEach((q) => {
          q.value = '';
        });
    }

    function collectItems() {
      const rows = [];
      document
        .querySelectorAll('input[type="checkbox"][data-item-type]')
        .forEach((cb) => {
          if (!cb.checked) return;
          const id = cb.getAttribute('data-item-id');
          const type = cb.getAttribute('data-item-type');
          const qtyInput = document.querySelector(
            'input.qty-input[data-item-type="' + type + '"][data-item-id="' + id + '"]'
          );
          const qty = qtyInput && qtyInput.value ? parseInt(qtyInput.value, 10) : 0;
          rows.push({ type, id: parseInt(id, 10), qty });
        });
      return rows;
    }

    if (resetBtn) {
      resetBtn.addEventListener('click', (e) => {
        e.preventDefault();
        clearSelections();
      });
    }

    form.addEventListener('submit', (e) => {
      const items = collectItems();
      if (!items.length) {
        e.preventDefault();
        window.alert('Please select at least one product and enter quantity.');
        return;
      }
      const invalid = items.find((it) => !it.qty || it.qty < 1);
      if (invalid) {
        e.preventDefault();
        window.alert('All selected items must have quantity of at least 1.');
        return;
      }
      if (itemsPayload) {
        itemsPayload.value = JSON.stringify(items);
      }
    });

    // Show first server-side validation error (if any) using native alert
    const errNode = document.getElementById('prFormErrors');
    if (errNode && errNode.dataset.message) {
      window.alert(errNode.dataset.message);
    }

    // Initialize state on load
    clearSelections();
  }

  // --- Step 3: Waiting for Admin Approval (SweetAlert) ---
  const statusNode = document.getElementById('prStatus');
  if (statusNode && statusNode.dataset.message) {
    const showSwal = () => {
      if (!window.Swal) return;
      window.Swal.fire({
        title: 'Step 3: Waiting for Admin Approval',
        text: statusNode.dataset.message,
        icon: 'info',
        confirmButtonText: 'OK',
      });
    };

    // Load SweetAlert2 on demand if not present
    if (window.Swal) {
      showSwal();
    } else {
      const script = document.createElement('script');
      script.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
      script.onload = showSwal;
      document.head.appendChild(script);
    }
  }
});