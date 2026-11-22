/*!
 * popup.js — Popup dùng chung cho Góc Nhỏ Có Hoa
 * Một file duy nhất (JS + CSS tự chèn), không phụ thuộc Bootstrap / Font Awesome.
 *
 * ------------------------------------------------------------------
 * 1) HỘP XÁC NHẬN — cách khai báo (không cần viết JS):
 *
 *    <a href="...delete..." data-confirm="Dữ liệu sẽ không thể khôi phục."
 *       data-confirm-title="Xóa sản phẩm?"
 *       data-confirm-variant="danger"        // danger | warning | primary | success
 *       data-confirm-ok="Xóa"                // chữ nút đồng ý   (tùy chọn)
 *       data-confirm-cancel="Không">         // chữ nút hủy      (tùy chọn)
 *
 *    Dùng được cho <a>, <button type="submit">, <input type="submit"> và <form data-confirm>.
 *
 *    Gọi bằng JS:
 *    GNPopup.confirm({ title, message, variant, okText, cancelText }).then(ok => { ... });
 *
 * 2) TOAST THÔNG BÁO (góc trên bên phải, tự tắt):
 *
 *    GNPopup.toast('Đã lưu sản phẩm');                       // success
 *    GNPopup.toast('Lưu thất bại', 'error');                // success | error | warning | info
 *    GNPopup.toast('Nội dung', 'info', { title: 'Tiêu đề', duration: 6000 });
 *
 *    Từ PHP: MessageHelper::$useToast = true; rồi MessageHelper::logMessage()
 *    sẽ in ra toast thay vì khung alert.
 * ------------------------------------------------------------------
 */
(function (window, document) {
    'use strict';

    if (window.GNPopup) return; // tránh nạp 2 lần

    // ================= CSS =================
    var CSS = `
.gnp-backdrop{position:fixed;inset:0;z-index:2000;display:flex;align-items:center;justify-content:center;padding:16px;
  background:rgba(15,23,42,.45);opacity:0;transition:opacity .18s ease}
.gnp-backdrop.gnp-in{opacity:1}
.gnp-dialog{width:100%;max-width:420px;background:#fff;border-radius:18px;box-shadow:0 24px 60px rgba(15,23,42,.25);
  padding:24px 24px 20px;font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:#1f2937;
  transform:translateY(8px) scale(.97);transition:transform .18s ease}
.gnp-backdrop.gnp-in .gnp-dialog{transform:none}
.gnp-icon{width:48px;height:48px;border-radius:50%;display:grid;place-items:center;margin-bottom:14px}
.gnp-icon svg{width:24px;height:24px}
.gnp-title{font-size:1.1rem;font-weight:700;margin:0 0 6px;line-height:1.35}
.gnp-message{font-size:.92rem;color:#475569;margin:0;line-height:1.55;white-space:pre-line}
.gnp-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:22px}
.gnp-btn{appearance:none;border:1px solid transparent;border-radius:10px;padding:9px 18px;font:inherit;font-size:.9rem;
  font-weight:600;cursor:pointer;transition:background .15s ease,border-color .15s ease,box-shadow .15s ease;min-width:88px}
.gnp-btn:focus-visible{outline:none;box-shadow:0 0 0 3px rgba(59,130,246,.35)}
.gnp-btn-cancel{background:#fff;border-color:#e2e8f0;color:#334155}
.gnp-btn-cancel:hover{background:#f8fafc;border-color:#cbd5e1}
.gnp-btn-ok{color:#fff}
.gnp-v-danger .gnp-icon{background:#fee2e2;color:#dc2626}
.gnp-v-danger .gnp-btn-ok{background:#dc2626}.gnp-v-danger .gnp-btn-ok:hover{background:#b91c1c}
.gnp-v-warning .gnp-icon{background:#fef3c7;color:#d97706}
.gnp-v-warning .gnp-btn-ok{background:#d97706}.gnp-v-warning .gnp-btn-ok:hover{background:#b45309}
.gnp-v-primary .gnp-icon{background:#dbeafe;color:#2563eb}
.gnp-v-primary .gnp-btn-ok{background:#2563eb}.gnp-v-primary .gnp-btn-ok:hover{background:#1d4ed8}
.gnp-v-success .gnp-icon{background:#dcfce7;color:#16a34a}
.gnp-v-success .gnp-btn-ok{background:#16a34a}.gnp-v-success .gnp-btn-ok:hover{background:#15803d}
@media (max-width:575.98px){
  .gnp-backdrop{align-items:flex-end;padding:0}
  .gnp-dialog{max-width:none;border-radius:20px 20px 0 0;padding:22px 20px calc(18px + env(safe-area-inset-bottom));
    transform:translateY(24px)}
  .gnp-actions{flex-direction:column-reverse}
  .gnp-btn{width:100%;padding:12px}
}

.gnp-toasts{position:fixed;top:16px;right:16px;z-index:2100;display:flex;flex-direction:column;gap:10px;
  width:min(360px,calc(100vw - 32px));pointer-events:none}
.gnp-toast{pointer-events:auto;display:flex;gap:12px;align-items:flex-start;background:#fff;border:1px solid #e2e8f0;
  border-left:4px solid var(--gnp-c);border-radius:12px;padding:12px 12px 12px 14px;
  box-shadow:0 10px 30px rgba(15,23,42,.12);font-family:Inter,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
  color:#1f2937;position:relative;overflow:hidden;opacity:0;transform:translateX(16px);transition:opacity .2s ease,transform .2s ease}
.gnp-toast.gnp-in{opacity:1;transform:none}
.gnp-toast-icon{color:var(--gnp-c);flex-shrink:0;margin-top:1px}
.gnp-toast-icon svg{width:20px;height:20px;display:block}
.gnp-toast-body{flex:1;min-width:0;font-size:.875rem;line-height:1.45}
.gnp-toast-title{font-weight:700;margin-bottom:2px}
.gnp-toast-msg{color:#475569;word-wrap:break-word}
.gnp-toast-close{appearance:none;border:0;background:transparent;color:#94a3b8;cursor:pointer;padding:2px;border-radius:6px;
  line-height:0;flex-shrink:0}
.gnp-toast-close:hover{color:#334155;background:#f1f5f9}
.gnp-toast-close svg{width:16px;height:16px}
.gnp-toast-bar{position:absolute;left:0;bottom:0;height:3px;background:var(--gnp-c);opacity:.35;width:100%;
  transform-origin:left;animation:gnp-bar linear forwards}
.gnp-toast:hover .gnp-toast-bar{animation-play-state:paused}
@keyframes gnp-bar{from{transform:scaleX(1)}to{transform:scaleX(0)}}
.gnp-t-success{--gnp-c:#16a34a}.gnp-t-error{--gnp-c:#dc2626}.gnp-t-warning{--gnp-c:#d97706}.gnp-t-info{--gnp-c:#2563eb}
@media (max-width:575.98px){
  .gnp-toasts{top:calc(12px + env(safe-area-inset-top));right:12px;left:12px;width:auto}
  .gnp-toast{transform:translateY(-12px)}
}
@media (prefers-reduced-motion:reduce){
  .gnp-backdrop,.gnp-dialog,.gnp-toast{transition:none}
  .gnp-toast-bar{animation:none;display:none}
}`;

    // ================= ICON (SVG nội tuyến) =================
    var svg = function (path) {
        return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" ' +
            'stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' + path + '</svg>';
    };
    var ICONS = {
        danger: svg('<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/>'),
        warning: svg('<path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/><path d="M12 9v4M12 17h.01"/>'),
        primary: svg('<circle cx="12" cy="12" r="10"/><path d="M9.1 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3M12 17h.01"/>'),
        success: svg('<circle cx="12" cy="12" r="10"/><path d="m8 12 3 3 5-6"/>'),
        error: svg('<circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/>'),
        info: svg('<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>'),
        close: svg('<path d="M18 6 6 18M6 6l12 12"/>')
    };

    var TOAST_TITLES = { success: 'Thành công', error: 'Có lỗi xảy ra', warning: 'Lưu ý', info: 'Thông báo' };
    var VARIANTS = ['danger', 'warning', 'primary', 'success'];

    function injectStyle() {
        if (document.getElementById('gnp-style')) return;
        var style = document.createElement('style');
        style.id = 'gnp-style';
        style.textContent = CSS;
        (document.head || document.documentElement).appendChild(style);
    }

    function whenBodyReady(fn) {
        if (document.body) fn();
        else document.addEventListener('DOMContentLoaded', fn, { once: true });
    }

    function el(tag, className, text) {
        var node = document.createElement(tag);
        if (className) node.className = className;
        if (text != null) node.textContent = text;
        return node;
    }

    // ================= HỘP XÁC NHẬN =================
    var dialogOpen = false;

    function confirmDialog(options) {
        if (typeof options === 'string') options = { message: options };
        options = options || {};

        var variant = VARIANTS.indexOf(options.variant) >= 0 ? options.variant : 'primary';
        var title = options.title || 'Bạn có chắc chắn?';
        var message = options.message || '';
        var okText = options.okText || 'Đồng ý';
        var cancelText = options.cancelText || 'Hủy';

        return new Promise(function (resolve) {
            whenBodyReady(function () {
                if (dialogOpen) return resolve(false);
                dialogOpen = true;
                injectStyle();

                var lastFocus = document.activeElement;
                var backdrop = el('div', 'gnp-backdrop gnp-v-' + variant);
                var dialog = el('div', 'gnp-dialog');
                var titleId = 'gnp-title-' + Date.now();
                dialog.setAttribute('role', 'alertdialog');
                dialog.setAttribute('aria-modal', 'true');
                dialog.setAttribute('aria-labelledby', titleId);

                var icon = el('div', 'gnp-icon');
                icon.innerHTML = ICONS[variant];
                var h = el('h2', 'gnp-title', title);
                h.id = titleId;
                dialog.appendChild(icon);
                dialog.appendChild(h);
                if (message) {
                    var p = el('p', 'gnp-message', message);
                    p.id = titleId + '-msg';
                    dialog.setAttribute('aria-describedby', p.id);
                    dialog.appendChild(p);
                }

                var actions = el('div', 'gnp-actions');
                var btnCancel = el('button', 'gnp-btn gnp-btn-cancel', cancelText);
                var btnOk = el('button', 'gnp-btn gnp-btn-ok', okText);
                btnCancel.type = btnOk.type = 'button';
                actions.appendChild(btnCancel);
                actions.appendChild(btnOk);
                dialog.appendChild(actions);
                backdrop.appendChild(dialog);
                // Đang mở popup / bảng trượt của Bootstrap -> gắn vào trong đó để không bị bẫy focus của Bootstrap chặn
                (document.querySelector('.modal.show, .offcanvas.show') || document.body).appendChild(backdrop);

                var prevOverflow = document.body.style.overflow;
                document.body.style.overflow = 'hidden';
                requestAnimationFrame(function () { backdrop.classList.add('gnp-in'); });

                // Thao tác nguy hiểm: focus nút Hủy để Enter không vô tình xóa
                (variant === 'danger' ? btnCancel : btnOk).focus();

                function close(result) {
                    document.removeEventListener('keydown', onKey, true);
                    backdrop.classList.remove('gnp-in');
                    document.body.style.overflow = prevOverflow;
                    setTimeout(function () { backdrop.remove(); }, 180);
                    dialogOpen = false;
                    if (lastFocus && lastFocus.focus) lastFocus.focus();
                    resolve(result);
                }

                function onKey(e) {
                    if (e.key === 'Escape') {
                        e.preventDefault();
                        close(false);
                    } else if (e.key === 'Tab') {
                        // giữ focus trong hộp thoại
                        var first = btnCancel, last = btnOk;
                        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
                    }
                }

                btnOk.addEventListener('click', function () { close(true); });
                btnCancel.addEventListener('click', function () { close(false); });
                backdrop.addEventListener('click', function (e) { if (e.target === backdrop) close(false); });
                document.addEventListener('keydown', onKey, true);
            });
        });
    }

    // ================= TOAST =================
    var toastBox = null;

    function toast(message, type, options) {
        if (message && typeof message === 'object') {
            options = message;
            message = options.message;
            type = options.type;
        }
        options = options || {};
        type = TOAST_TITLES[type] ? type : 'success';
        var duration = options.duration != null ? options.duration : (type === 'error' ? 6000 : 4000);

        whenBodyReady(function () {
            injectStyle();
            if (!toastBox || !toastBox.isConnected) {
                toastBox = el('div', 'gnp-toasts');
                document.body.appendChild(toastBox);
            }

            var item = el('div', 'gnp-toast gnp-t-' + type);
            item.setAttribute('role', type === 'error' ? 'alert' : 'status');
            item.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');

            var icon = el('div', 'gnp-toast-icon');
            icon.innerHTML = ICONS[type === 'success' ? 'success' : type];
            var body = el('div', 'gnp-toast-body');
            body.appendChild(el('div', 'gnp-toast-title', options.title || TOAST_TITLES[type]));
            if (message) body.appendChild(el('div', 'gnp-toast-msg', String(message)));
            var btnClose = el('button', 'gnp-toast-close');
            btnClose.type = 'button';
            btnClose.setAttribute('aria-label', 'Đóng thông báo');
            btnClose.innerHTML = ICONS.close;

            item.appendChild(icon);
            item.appendChild(body);
            item.appendChild(btnClose);

            var timer = null, remaining = duration, startedAt = 0;
            if (duration > 0) {
                var bar = el('div', 'gnp-toast-bar');
                bar.style.animationDuration = duration + 'ms';
                item.appendChild(bar);
            }

            toastBox.appendChild(item);
            requestAnimationFrame(function () { item.classList.add('gnp-in'); });

            function dismiss() {
                clearTimeout(timer);
                item.classList.remove('gnp-in');
                setTimeout(function () { item.remove(); }, 200);
            }

            function start() {
                if (duration <= 0) return;
                startedAt = Date.now();
                timer = setTimeout(dismiss, remaining);
            }

            // Rê chuột vào thì dừng đếm ngược
            item.addEventListener('mouseenter', function () {
                clearTimeout(timer);
                remaining -= Date.now() - startedAt;
            });
            item.addEventListener('mouseleave', start);
            btnClose.addEventListener('click', dismiss);
            start();
        });
    }

    // ================= KHAI BÁO data-confirm =================
    function optionsFrom(node) {
        var d = node.dataset;
        return {
            title: d.confirmTitle,
            message: d.confirm,
            variant: d.confirmVariant,
            okText: d.confirmOk,
            cancelText: d.confirmCancel
        };
    }

    // Link / nút submit có data-confirm
    document.addEventListener('click', function (e) {
        var trigger = e.target.closest && e.target.closest('[data-confirm]');
        if (!trigger || trigger.tagName === 'FORM') return;
        // Kể cả Ctrl+click: luôn bắt xác nhận để tránh xóa nhầm
        e.preventDefault();

        confirmDialog(optionsFrom(trigger)).then(function (ok) {
            if (!ok) return;
            if (trigger.tagName === 'A') {
                var href = trigger.getAttribute('href');
                if (!href || href === '#') return;
                if (trigger.target === '_blank') window.open(trigger.href, '_blank', 'noopener');
                else window.location.href = trigger.href;
            } else if (trigger.form) {
                trigger.form.dataset.gnpConfirmed = '1';
                if (trigger.form.requestSubmit) trigger.form.requestSubmit(trigger);
                else trigger.form.submit();
            }
        });
    });

    // <form data-confirm>
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form.matches || !form.matches('form[data-confirm]')) return;
        if (form.dataset.gnpConfirmed === '1') {
            delete form.dataset.gnpConfirmed;
            return;
        }
        e.preventDefault();
        var submitter = e.submitter;
        confirmDialog(optionsFrom(form)).then(function (ok) {
            if (!ok) return;
            form.dataset.gnpConfirmed = '1';
            if (form.requestSubmit) form.requestSubmit(submitter || undefined);
            else form.submit();
        });
    });

    // ================= API + hàng đợi từ PHP =================
    var api = { confirm: confirmDialog, toast: toast };
    window.GNPopup = api;

    // PHP có thể in toast trước khi file này nạp: window.GNPopupQueue = [{type, message, title}]
    var queued = window.GNPopupQueue || [];
    window.GNPopupQueue = {
        push: function () {
            for (var i = 0; i < arguments.length; i++) toast(arguments[i]);
        }
    };
    window.GNPopupQueue.push.apply(null, queued);
})(window, document);
