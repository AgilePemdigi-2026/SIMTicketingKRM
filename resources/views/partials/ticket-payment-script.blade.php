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

    // Langsung simpan metode begitu tab QRIS atau Cash diklik.
    // Tetap menggunakan POST Laravel + CSRF, bukan mengubah data lewat GET.
    const radio = document.querySelector('.qr-method-tabs input[name="method"]');
    const methodForm = radio?.form;

    if (methodForm) {
        methodForm.querySelectorAll('input[name="method"]').forEach(input => {
            input.addEventListener('change', () => {
                if (input.checked && methodForm.dataset.submitting !== 'true') {
                    methodForm.requestSubmit();
                }
            });
        });

        // Jika JavaScript aktif, tombol penyimpanan ekstra tidak diperlukan.
        // Jika JavaScript nonaktif, tombol asli tetap tersedia sebagai fallback.
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
