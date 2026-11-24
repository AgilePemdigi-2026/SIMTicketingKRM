<script>
(() => {
    // Cegah permintaan pembayaran terkirim dua kali karena klik berulang.
    document.querySelectorAll('[data-payment-form]').forEach(form => {
        form.addEventListener('submit', event => {
            if (form.dataset.submitting === 'true') {
                event.preventDefault();
                return;
            }

            form.dataset.submitting = 'true';
            form.setAttribute('aria-busy', 'true');

            const button = event.submitter || form.querySelector('button[type="submit"]');
            if (button) {
                button.dataset.originalLabel = button.textContent;
                button.disabled = true;
                button.textContent = 'Memproses…';
            }
        });
    });

    // Metode baru dikirim setelah wisatawan menyetujui pop-up.
    // Sembunyikan tombol simpan lama saat JavaScript aktif.
    // Tombol tetap tersedia sebagai fallback jika JavaScript nonaktif.
    const methodForm = document.getElementById('payment-method-form')
        || document.querySelector('.qr-method-tabs input[name="method"]')?.form;

    if (methodForm) {
        methodForm.querySelector('.qr-method-submit')?.remove();
    }

    // Sesudah metode QRIS tersimpan, buat QRIS otomatis satu kali per
    // pesanan per sesi tab. Form ini hanya ada jika QRIS belum dibuat.
    const qrisForm = Array.from(document.querySelectorAll('form[data-payment-form]'))
        .find(form => new URL(form.action, window.location.href).pathname.endsWith('/qris'));

    if (qrisForm) {
        const attemptKey = `krm-auto-qris:${window.location.pathname}`;
        let canAutoSubmit = false;

        try {
            if (window.sessionStorage.getItem(attemptKey) !== '1') {
                window.sessionStorage.setItem(attemptKey, '1');
                canAutoSubmit = true;
            }
        } catch {
            // Jika penyimpanan sesi diblokir, gunakan tombol manual yang sudah ada.
        }

        if (canAutoSubmit) {
            const button = qrisForm.querySelector('button[type="submit"]');
            if (button) button.style.display = 'none';
            qrisForm.requestSubmit();
        }
    }

    // Pulihkan kondisi tombol ketika pengguna kembali dengan tombol Back browser.
    window.addEventListener('pageshow', () => {
        document.querySelectorAll('[data-payment-form]').forEach(form => {
            form.removeAttribute('aria-busy');
            delete form.dataset.submitting;
        });

        document.querySelectorAll('[data-original-label]').forEach(button => {
            button.disabled = false;
            button.textContent = button.dataset.originalLabel;
        });
    });
})();
</script>
