/*!
 * admin.js — Hành vi dùng chung cho các trang quản trị Góc Nhỏ Có Hoa
 * Tự khởi tạo theo thuộc tính data-*, không cần viết JS riêng ở từng trang.
 *
 * 1) Kiểm tra form:  <form data-validate>
 *      <input name="product_name" data-label="tên sản phẩm" data-rules="required|min:3|max:100">
 *      <div class="ad-error" data-error-for="product_name"></div>
 *    Luật: required | min:N | max:N (độ dài chữ) | gte:N | lte:N (giá trị số) | int | email | phone
 *          | after:<tên trường> (ngày) — nhóm radio: đặt data-rules ở ô đầu tiên
 *    Đổi câu báo lỗi: data-msg-required="..." , data-msg-min="..." ...
 *
 * 2) Ô chọn ảnh:  <label class="ad-upload" data-upload> <input type="file"> <img> ... </label>
 *      nút xóa: <button data-upload-clear="#idUpload">
 *
 * 3) Lọc danh sách tại chỗ:  <div data-filter>
 *      <input data-filter-search>  <select data-filter-status>
 *      <tr data-search="chuỗi tìm" data-status="available"> ...
 *      <span data-filter-count></span>  <div data-filter-empty class="ad-no-result">...</div>
 */
(function () {
    'use strict';

    var toast = function (msg, type) {
        if (window.GNPopup) window.GNPopup.toast(msg, type || 'error');
        else window.alert(msg);
    };

    // ================= 1. KIỂM TRA FORM =================
    function labelOf(el) {
        return el.dataset.label || el.name;
    }

    function cap(s) {
        return s.charAt(0).toUpperCase() + s.slice(1);
    }

    function fmt(n) {
        return Number(n).toLocaleString('vi-VN');
    }

    function checkField(el, form) {
        var rules = (el.dataset.rules || '').split('|').filter(Boolean);
        var raw = el.value;
        if (el.type === 'radio' || el.type === 'checkbox') {
            // Nhóm radio: đặt data-rules ở ô đầu tiên, giá trị = ô đang được chọn trong nhóm
            var checked = form.querySelector('input[name="' + el.name + '"]:checked');
            raw = checked ? checked.value : '';
        }
        var val = typeof raw === 'string' ? raw.trim() : raw;
        var label = labelOf(el);
        var isSelect = el.tagName === 'SELECT';
        var msg = '';

        for (var i = 0; i < rules.length && !msg; i++) {
            var parts = rules[i].split(':');
            var rule = parts[0];
            var arg = parts[1];
            var custom = el.dataset['msg' + cap(rule)];

            switch (rule) {
                case 'required':
                    if (val === '') msg = custom || (isSelect ? 'Vui lòng chọn ' : 'Vui lòng nhập ') + label;
                    break;
                case 'min':
                    if (val !== '' && val.length < +arg) msg = custom || cap(label) + ' phải có ít nhất ' + arg + ' ký tự';
                    break;
                case 'max':
                    if (val.length > +arg) msg = custom || cap(label) + ' không được quá ' + arg + ' ký tự';
                    break;
                case 'gte':
                    if (val !== '' && (isNaN(+val) || +val < +arg)) msg = custom || cap(label) + ' phải từ ' + fmt(arg) + ' trở lên';
                    break;
                case 'lte':
                    if (val !== '' && +val > +arg) msg = custom || cap(label) + ' không được lớn hơn ' + fmt(arg);
                    break;
                case 'email':
                    if (val !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) msg = custom || cap(label) + ' không đúng định dạng';
                    break;
                case 'phone':
                    if (val !== '' && !/^[0-9]{10,15}$/.test(val)) msg = custom || cap(label) + ' phải gồm 10–15 chữ số';
                    break;
                case 'int':
                    if (val !== '' && !/^-?\d+$/.test(val)) msg = custom || cap(label) + ' phải là số nguyên';
                    break;
                case 'after':
                    var other = form.elements[arg];
                    if (val !== '' && other && other.value !== '' && val < other.value) {
                        msg = custom || cap(label) + ' phải sau hoặc bằng ' + labelOf(other);
                    }
                    break;
            }
        }

        var box = form.querySelector('[data-error-for="' + el.name + '"]');
        if (box) box.textContent = msg;
        el.classList.toggle('is-invalid', !!msg);
        el.setAttribute('aria-invalid', msg ? 'true' : 'false');
        return !msg;
    }

    function initForm(form) {
        var fields = Array.prototype.slice.call(form.querySelectorAll('[data-rules]'));
        form.setAttribute('novalidate', '');

        fields.forEach(function (el) {
            var box = form.querySelector('[data-error-for="' + el.name + '"]');
            if (box) {
                if (!box.id) box.id = 'err-' + el.name;
                el.setAttribute('aria-describedby', box.id);
            }
            var touched = false;
            if (el.type === 'radio' || el.type === 'checkbox') {
                form.querySelectorAll('input[name="' + el.name + '"]').forEach(function (r) {
                    r.addEventListener('change', function () { checkField(el, form); });
                });
                return;
            }
            // Chỉ báo lỗi khi rời ô SAU KHI đã nhập (ô trống chưa đụng tới -> chỉ báo khi bấm Lưu)
            el.addEventListener(el.tagName === 'SELECT' ? 'change' : 'input', function () {
                touched = true;
                if (el.classList.contains('is-invalid') || el.tagName === 'SELECT') checkField(el, form);
            });
            el.addEventListener('blur', function () {
                if (touched) checkField(el, form);
            });
        });

        form.addEventListener('submit', function (e) {
            if (form.dataset.submitting === '1') {
                e.preventDefault();
                return;
            }
            var firstBad = null;
            fields.forEach(function (el) {
                if (!checkField(el, form) && !firstBad) firstBad = el;
            });
            if (firstBad) {
                e.preventDefault();
                firstBad.focus();
                firstBad.scrollIntoView({ block: 'center', behavior: 'smooth' });
                return;
            }
            // Chống bấm 2 lần. Không disable nút để tên nút submit vẫn được gửi lên server.
            form.dataset.submitting = '1';
            if (form.closest('.gn-form-modal')) {
                saveDraft(form);
                form.dispatchEvent(new CustomEvent('gn:submitting', { bubbles: true }));
            }
            var btn = e.submitter || form.querySelector('[type=submit]');
            if (btn && btn.tagName === 'BUTTON') {
                btn.setAttribute('aria-busy', 'true');
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span> Đang lưu...';
            }
        });
    }

    // ================= 2. Ô CHỌN ẢNH =================
    var IMG_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    var IMG_MAX = 5 * 1024 * 1024;

    function initUpload(box) {
        var input = box.querySelector('input[type=file]');
        var img = box.querySelector('img');
        if (!input || !img) return;
        var original = img.getAttribute('src') || '';

        function show(src) {
            img.src = src || '';
            box.classList.toggle('has-image', !!src);
        }

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];
            if (!file) {
                show(original);
                return;
            }
            if (IMG_TYPES.indexOf(file.type) < 0) {
                toast('Vui lòng chọn ảnh JPG, PNG, GIF hoặc WEBP');
                input.value = '';
                return;
            }
            if (file.size > IMG_MAX) {
                toast('Ảnh không được lớn hơn 5MB');
                input.value = '';
                return;
            }
            var reader = new FileReader();
            reader.onload = function (e) { show(e.target.result); };
            reader.readAsDataURL(file);
            box.dispatchEvent(new CustomEvent('upload:change', { bubbles: true }));
        });

        ['dragenter', 'dragover'].forEach(function (ev) {
            box.addEventListener(ev, function () { box.classList.add('is-drag'); });
        });
        ['dragleave', 'drop'].forEach(function (ev) {
            box.addEventListener(ev, function () { box.classList.remove('is-drag'); });
        });

        box._gnClear = function () {
            input.value = '';
            original = '';
            show('');
        };
        show(original);
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest && e.target.closest('[data-upload-clear]');
        if (!btn) return;
        var box = document.querySelector(btn.dataset.uploadClear);
        if (box && box._gnClear) box._gnClear();
    });

    // ================= 3. LỌC DANH SÁCH =================
    function initFilter(root) {
        var search = root.querySelector('[data-filter-search]');
        var status = root.querySelector('[data-filter-status]');
        var reset = root.querySelector('[data-filter-reset]');
        var count = root.querySelector('[data-filter-count]');
        var empty = root.querySelector('[data-filter-empty]');
        var rows = Array.prototype.slice.call(root.querySelectorAll('[data-search]'));

        function norm(s) {
            return (s || '').toString().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').replace(/đ/g, 'd');
        }

        function apply() {
            var q = norm(search ? search.value.trim() : '');
            var st = status ? status.value : '';
            var shown = 0;
            rows.forEach(function (r) {
                var ok = (!q || norm(r.dataset.search).indexOf(q) >= 0) && (!st || r.dataset.status === st);
                r.style.display = ok ? '' : 'none';
                if (ok) shown++;
            });
            if (count) count.textContent = shown + ' / ' + rows.length + ' mục';
            if (empty) empty.style.display = shown === 0 && rows.length > 0 ? 'block' : 'none';
        }

        if (search) search.addEventListener('input', apply);
        if (status) status.addEventListener('change', apply);
        if (reset) reset.addEventListener('click', function () {
            if (search) search.value = '';
            if (status) status.value = '';
            apply();
        });
        apply();
    }

    function initIn(root) {
        root.querySelectorAll('form[data-validate]').forEach(initForm);
        root.querySelectorAll('[data-upload]').forEach(initUpload);
        root.querySelectorAll('[data-filter]').forEach(initFilter);
    }

    // ================= 4. BẢN NHÁP FORM (khôi phục khi server báo lỗi) =================
    // Trước khi gửi form trong popup, lưu giá trị vào sessionStorage. Nếu server báo lỗi
    // (vd: mã trùng) và popup mở lại, điền lại những gì người dùng đã nhập.
    var SKIP_TYPES = ['file', 'password', 'submit', 'button', 'reset'];

    function draftKey(form) {
        return 'gn-draft:' + form.getAttribute('action');
    }

    function saveDraft(form) {
        var data = [];
        Array.prototype.forEach.call(form.elements, function (el) {
            if (!el.name || el.disabled || SKIP_TYPES.indexOf(el.type) >= 0) return;
            if ((el.type === 'radio' || el.type === 'checkbox') && !el.checked) return;
            data.push([el.name, el.value]);
        });
        try { sessionStorage.setItem(draftKey(form), JSON.stringify(data)); } catch (e) { }
    }

    function restoreDraft(form) {
        var raw = null;
        try {
            raw = sessionStorage.getItem(draftKey(form));
            sessionStorage.removeItem(draftKey(form));
        } catch (e) { }
        if (!raw || !window.GNFlashError) return false;
        window.GNFlashError = false;
        try {
            JSON.parse(raw).forEach(function (pair) {
                var field = form.elements[pair[0]];
                if (!field) return;
                if (field instanceof RadioNodeList) {
                    Array.prototype.forEach.call(field, function (r) { r.checked = r.value === pair[1]; });
                } else {
                    field.value = pair[1];
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
            return true;
        } catch (e) {
            return false;
        }
    }

    // ================= 5. POPUP FORM (thêm / sửa) =================
    // window.GNFormModals = { "controller.action": "" | "lg" | "xl" } — khai báo trong layout.
    var FORM_MODALS = window.GNFormModals || {};
    var modalEl, modal, dialogEl, bodyEl, titleEl, subEl;
    var dirty = false, submitting = false, closeConfirmed = false;

    function formKey(url) {
        var q = new URLSearchParams((url.split('?')[1] || ''));
        return (q.get('controller') || 'dashboard') + '.' + (q.get('action') || 'index');
    }

    function isFormUrl(url) {
        return /^index\.php\?/.test(url) && Object.prototype.hasOwnProperty.call(FORM_MODALS, formKey(url));
    }

    // Script chèn bằng innerHTML không tự chạy -> tạo lại thẻ script
    function runScripts(root) {
        root.querySelectorAll('script').forEach(function (old) {
            var s = document.createElement('script');
            if (old.src) s.src = old.src;
            else s.textContent = old.textContent;
            old.parentNode.replaceChild(s, old);
        });
    }

    function setupModal() {
        modalEl = document.getElementById('gnFormModal');
        if (!modalEl || !window.bootstrap) return false;
        modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        dialogEl = modalEl.querySelector('.modal-dialog');
        bodyEl = document.getElementById('gnFormModalBody');
        titleEl = document.getElementById('gnFormModalTitle');
        subEl = document.getElementById('gnFormModalSub');

        bodyEl.addEventListener('input', function () { dirty = true; });
        bodyEl.addEventListener('change', function () { dirty = true; });
        modalEl.addEventListener('gn:submitting', function () { submitting = true; });

        // Đang nhập dở mà đóng -> hỏi lại
        modalEl.addEventListener('hide.bs.modal', function (e) {
            if (!dirty || submitting || closeConfirmed) return;
            e.preventDefault();
            window.GNPopup.confirm({
                title: 'Bỏ thay đổi?',
                message: 'Những gì bạn vừa nhập sẽ không được lưu.',
                variant: 'warning',
                okText: 'Bỏ thay đổi',
                cancelText: 'Tiếp tục sửa'
            }).then(function (ok) {
                if (!ok) return;
                closeConfirmed = true;
                modal.hide();
            });
        });
        modalEl.addEventListener('hidden.bs.modal', function () {
            bodyEl.innerHTML = '';
            dirty = submitting = closeConfirmed = false;
        });
        return true;
    }

    function openFormModal(url) {
        if (!modal && !setupModal()) {
            window.location.href = url;
            return;
        }
        var size = FORM_MODALS[formKey(url)];
        dialogEl.className = 'modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down' + (size ? ' modal-' + size : '');
        titleEl.textContent = 'Đang tải...';
        subEl.textContent = '';
        bodyEl.innerHTML = '<div class="gn-form-modal-loading"><span class="spinner-border" role="status" aria-label="Đang tải"></span></div>';
        dirty = submitting = closeConfirmed = false;
        modal.show();

        fetch(url + '&modal=1', { credentials: 'same-origin' })
            .then(function (res) {
                // Controller chuyển hướng (vd: không tìm thấy dữ liệu) -> đi theo luôn
                if (res.redirected && !/[?&]modal=1/.test(res.url)) {
                    window.location.href = res.url;
                    throw new Error('redirected');
                }
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.text();
            })
            .then(function (html) {
                bodyEl.innerHTML = html;
                // Đưa tiêu đề trang (.ad-head) lên header của popup
                var head = bodyEl.querySelector('.ad-head');
                if (head) {
                    var t = head.querySelector('.ad-head-title');
                    var s = head.querySelector('.ad-head-sub');
                    titleEl.textContent = t ? t.textContent.trim() : '';
                    subEl.textContent = s ? s.textContent.trim() : '';
                    head.remove();
                }
                // Nút "Hủy" trong form -> đóng popup
                bodyEl.querySelectorAll('.ad-form-actions a').forEach(function (a) {
                    a.setAttribute('data-bs-dismiss', 'modal');
                });
                runScripts(bodyEl);
                initIn(bodyEl);
                var form = bodyEl.querySelector('form');
                // Điền lại bản nháp (nếu có lỗi server) -> coi như đang nhập dở
                dirty = !!(form && restoreDraft(form));
                var first = bodyEl.querySelector('input:not([type=hidden]):not([disabled]), select, textarea');
                if (first && window.matchMedia('(min-width: 576px)').matches) first.focus();
            })
            .catch(function (err) {
                if (err.message === 'redirected') return;
                bodyEl.innerHTML = '';
                modal.hide();
                if (window.GNPopup) window.GNPopup.toast('Không mở được form. Vui lòng thử lại.', 'error');
            });
    }

    window.GNFormModal = { open: openFormModal };

    // Bấm link tới trang form -> mở popup thay vì chuyển trang
    // (Ctrl/Cmd + click, chuột giữa vẫn mở tab mới như thường)
    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
        var a = e.target.closest && e.target.closest('a[href]');
        if (!a || a.target === '_blank' || a.hasAttribute('data-confirm') || a.closest('.modal')) return;
        var url = a.getAttribute('href');
        if (!isFormUrl(url)) return;
        e.preventDefault();
        openFormModal(url);
    });

    function init() {
        initIn(document);
        // ?open=index.php?... -> tự mở popup (khi mở thẳng link form hoặc sau khi server báo lỗi)
        var params = new URLSearchParams(window.location.search);
        var open = params.get('open');
        if (open && isFormUrl(open)) {
            params.delete('open');
            history.replaceState(null, '', window.location.pathname + '?' + params.toString());
            openFormModal(open);
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();
