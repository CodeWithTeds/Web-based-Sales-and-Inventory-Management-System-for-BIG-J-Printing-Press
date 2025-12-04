document.addEventListener('DOMContentLoaded', () => {
  // Accept: SweetAlert confirmation then submit
  document.querySelectorAll('.js-accept-form').forEach((form) => {
    form.addEventListener('submit', (e) => {
      e.preventDefault();
      Swal.fire({
        title: 'Accept Quotation?',
        text: 'Once accepted, preparation will begin.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#16a34a',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, accept',
      }).then((result) => {
        if (result.isConfirmed) {
          // Optional success toast before submit
          Swal.fire({
            title: 'Accepted!',
            text: 'We will start preparing your order.',
            icon: 'success',
            timer: 1200,
            showConfirmButton: false,
          });
          // Submit after a tiny delay so the toast is visible
          setTimeout(() => form.submit(), 300);
        }
      });
    });
  });

  // Cancel: open modal and set action URL
  document.querySelectorAll('.js-cancel-button').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const action = btn.getAttribute('data-action-url');
      const form = document.getElementById('cancelForm');
      const modal = document.getElementById('cancelModal');
      if (form) form.setAttribute('action', action);
      if (modal) modal.classList.remove('hidden');
    });
  });
});

// Modal close helper for backdrop button
function closeCancelModal() {
  const modal = document.getElementById('cancelModal');
  if (modal) modal.classList.add('hidden');
}
