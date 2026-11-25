/*!
 * page-transition.js — Hiệu ứng chuyển trang + thanh tiến trình (admin + cửa hàng)
 * Nạp KHÔNG defer trong <head> để kịp bắt sự kiện pagereveal trước lần vẽ đầu tiên.
 * Đi kèm assets/css/page-transition.css. Nút có class .gn-back-btn hoặc thuộc tính
 * data-nav-back sẽ làm hiệu ứng trượt ngược (quay lại).
 */
(function () {
    'use strict';
    var root = document.documentElement;

    // Trình duyệt chưa hỗ trợ View Transitions giữa các trang -> dùng hiệu ứng hiện dần dự phòng
    if (!('onpagereveal' in window)) root.classList.add('gn-anim-fallback');

    // Chỉ đặt view-transition-name TRONG LÚC chuyển trang. Đặt cố định sẽ biến vùng đó thành
    // stacking context riêng -> popup / bảng trượt bên trong bị thanh tab, header đè lên.
    function nameRegions(on) {
        document.querySelectorAll('[data-vt]').forEach(function (el) {
            el.style.viewTransitionName = on ? 'vt-' + el.getAttribute('data-vt') : '';
        });
    }
    // Trang cũ (sắp rời đi)
    window.addEventListener('pageswap', function (e) {
        if (e.viewTransition) nameRegions(true);
    });
    // Quay lại bằng bộ nhớ đệm (bfcache) -> gỡ tên còn sót
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) nameRegions(false);
    });

    // Trang mới: đặt tên để chụp, xong hiệu ứng thì gỡ.
    // Chiều chuyển trang: bấm nút quay lại hoặc Back/Forward của trình duyệt -> trượt ngược
    window.addEventListener('pagereveal', function (e) {
        if (!e.viewTransition) return;
        nameRegions(true);
        e.viewTransition.finished.finally(function () { nameRegions(false); });
        var dir = null;
        try {
            dir = sessionStorage.getItem('gn-nav-dir');
            sessionStorage.removeItem('gn-nav-dir');
        } catch (err) { }
        var nav = window.navigation && navigation.activation;
        if (dir === 'back' || (nav && nav.navigationType === 'traverse')) {
            e.viewTransition.types.add('back');
        }
    });
    document.addEventListener('click', function (e) {
        if (e.target.closest && e.target.closest('.gn-back-btn, [data-nav-back]')) {
            try { sessionStorage.setItem('gn-nav-dir', 'back'); } catch (err) { }
        }
    }, true);

    // ---------- Thanh tiến trình khi đang chuyển trang ----------
    function setProgress(on) {
        var bar = document.getElementById('gnProgress');
        if (bar) bar.classList.toggle('is-loading', on);
    }

    if (window.navigation) {
        // Navigation API: bắt mọi kiểu chuyển trang (link, form, location.href sau khi xác nhận...)
        navigation.addEventListener('navigate', function (e) {
            if (e.destination.sameDocument || (e.downloadRequest !== null && e.downloadRequest !== undefined)) return;
            setProgress(true);
        });
        // Chuyển trang bị hủy / lỗi mạng -> tắt thanh tiến trình
        navigation.addEventListener('navigateerror', function () { setProgress(false); });
    } else {
        // Dự phòng: link thường và form (bỏ qua khi bị chặn, vd: mở popup form)
        document.addEventListener('click', function (e) {
            var a = e.target.closest && e.target.closest('a[href]');
            if (!a || a.target === '_blank' || e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
            var href = a.getAttribute('href');
            if (!href || href.charAt(0) === '#' || /^(mailto|tel|javascript):/i.test(href)) return;
            setTimeout(function () { if (!e.defaultPrevented) setProgress(true); }, 0);
        });
        document.addEventListener('submit', function (e) {
            setTimeout(function () { if (!e.defaultPrevented) setProgress(true); }, 0);
        });
    }
    // Quay lại bằng bộ nhớ đệm (bfcache) -> tắt thanh tiến trình còn treo
    window.addEventListener('pageshow', function () { setProgress(false); });
})();
