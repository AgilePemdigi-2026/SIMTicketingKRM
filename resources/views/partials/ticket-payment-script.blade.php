<script>
document.querySelectorAll('[data-payment-form]').forEach(form => {
    form.addEventListener('submit', event => {
        if (form.dataset.submitting === 'true') { event.preventDefault(); return; }
        form.dataset.submitting = 'true';
        form.setAttribute('aria-busy', 'true');
        const button = event.submitter || form.querySelector('button[type="submit"]');
        if (button) { button.dataset.originalLabel = button.textContent; button.disabled = true; button.textContent = 'Menyimpan…'; }
    });
});
window.addEventListener('pageshow', () => {
    document.querySelectorAll('[data-payment-form]').forEach(form => { form.removeAttribute('aria-busy'); delete form.dataset.submitting; });
    document.querySelectorAll('[data-original-label]').forEach(button => { button.disabled = false; button.textContent = button.dataset.originalLabel; });
});
</script>
