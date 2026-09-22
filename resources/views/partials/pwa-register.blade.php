{{-- Registrasi Service Worker — include sebelum </body> semua layout --}}
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
        navigator.serviceWorker.register('{{ asset('sw.js') }}').catch(function (e) {
            console.warn('SW registration failed:', e);
        });
    });
}
</script>
