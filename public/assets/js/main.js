/**
 * Polinela Agro Digital - Main Client Scripts
 */
document.addEventListener('DOMContentLoaded', function () {
    // 1. Inisialisasi format angka input jika ada
    document.querySelectorAll('.currency-input').forEach(input => {
        input.addEventListener('input', function () {
            let val = this.value.replace(/[^0-9]/g, '');
            this.value = val ? parseInt(val, 10).toLocaleString('id-ID') : '';
        });
    });

    // 2. Auto-dismiss alert setelah 5 detik
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert-dismissible');
        alerts.forEach(a => {
            const bsAlert = bootstrap.Alert.getInstance(a);
            if (bsAlert) bsAlert.close();
        });
    }, 5000);
});
