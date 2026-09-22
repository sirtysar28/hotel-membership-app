{{-- ============================================================
     PAGE LOADER GLOBAL (spinner)
     - Overlay spinner tampil saat halaman dimuat (setiap akses halaman)
     - Overlay spinner tampil saat navigasi internal (klik link / submit form)
     - Spinner inline pada tombol submit utk form ber-atribut data-loading
       (opsi: data-loading-text="Memproses…" utk mengganti label tombol)
     Catatan: include tepat setelah <body> di semua layout.
     Link/form dgn atribut data-no-loader tidak menampilkan loader.
     ============================================================ --}}
<style>
    #page-loader {
        position: fixed; inset: 0; z-index: 9999;
        display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .8rem;
        background: rgba(10, 28, 50, .55);
        -webkit-backdrop-filter: blur(3px); backdrop-filter: blur(3px);
        opacity: 1; visibility: visible;
        transition: opacity .25s ease, visibility .25s ease;
    }
    #page-loader.is-done { opacity: 0; visibility: hidden; pointer-events: none; }
    .pl-spinner {
        width: 46px; height: 46px; border-radius: 50%;
        border: 4px solid rgba(255, 255, 255, .2);
        border-top-color: #fbbf24; border-right-color: #fbbf24;
        animation: pl-spin .8s linear infinite;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .35);
    }
    .pl-text {
        color: #fff; font-size: .8rem; font-weight: 600;
        letter-spacing: .12em; text-transform: uppercase;
        text-shadow: 0 1px 3px rgba(0, 0, 0, .5);
    }
    @keyframes pl-spin { to { transform: rotate(360deg); } }
    @media (prefers-reduced-motion: reduce) { .pl-spinner { animation-duration: 1.6s; } }

    /* Spinner kecil utk tombol submit (form data-loading) */
    .btn-spinner {
        display: inline-block; width: 1em; height: 1em; border-radius: 50%;
        border: 2px solid currentColor; border-top-color: transparent;
        animation: pl-spin .7s linear infinite;
        vertical-align: -.125em; margin-right: .5rem;
    }
    .pl-btn-loading { opacity: .75 !important; cursor: not-allowed; }
</style>

<div id="page-loader" role="status" aria-live="polite" aria-label="Sedang memuat halaman">
    <div class="pl-spinner"></div>
    <div class="pl-text">Memuat…</div>
</div>

<script>
(function () {
    var loader  = document.getElementById('page-loader');
    var DELAY   = 5000; // fallback: auto-hide bila navigasi dibatalkan / lambat

    function showLoader() { loader.classList.remove('is-done'); }
    function hideLoader() { loader.classList.add('is-done'); }

    /* 1) Sembunyikan loader saat halaman selesai dirender */
    function ready() { hideLoader(); }
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        ready();
    } else {
        document.addEventListener('DOMContentLoaded', ready);
        window.addEventListener('load', ready);
    }
    setTimeout(ready, DELAY);

    /* 2) bfcache: tombol back/forward browser */
    window.addEventListener('pageshow', function (e) { if (e.persisted) hideLoader(); });

    /* 3) Overlay saat klik link internal (sebelum halaman baru dimuat) */
    function isInternalLink(a) {
        var href = (a.getAttribute('href') || '').trim();
        if (!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0 ||
            href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) return false;
        if (a.hasAttribute('download') || (a.target && a.target !== '_self')) return false;
        if (a.hostname && a.hostname !== window.location.hostname) return false;
        return true;
    }

    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 ||
            e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
        if (!a || a.hasAttribute('data-no-loader') || !isInternalLink(a)) return;
        setTimeout(function () {
            if (e.defaultPrevented) return; // dibatalkan handler lain
            showLoader();
            setTimeout(hideLoader, DELAY);
        }, 0);
    }, false);

    /* 4) Overlay + spinner tombol saat submit form */
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (e.defaultPrevented || form.hasAttribute('data-no-loader')) return;

        if (form.hasAttribute('data-loading')) {
            var btn = e.submitter || form.querySelector('button:not([type]), button[type="submit"], input[type="submit"]');
            if (btn && !btn.disabled) {
                btn.disabled = true;
                btn.classList.add('pl-btn-loading');
                var text = btn.getAttribute('data-loading-text');
                if (text && !btn.hasAttribute('data-pl-on')) {
                    btn.setAttribute('data-pl-on', '1');
                    if (btn.tagName === 'BUTTON') {
                        btn.innerHTML = '<span class="btn-spinner" aria-hidden="true"></span>' + text;
                    } else { // input[type=submit]
                        btn.value = text;
                    }
                } else if (btn.tagName === 'BUTTON' && !btn.querySelector('.btn-spinner')) {
                    btn.insertAdjacentHTML('afterbegin', '<span class="btn-spinner" aria-hidden="true"></span>');
                }
            }
        }

        showLoader();
        setTimeout(hideLoader, DELAY);
    }, false);
})();
</script>
