<div class="ad-head">
    <div>
        <h2 class="ad-head-title">Thêm quản trị viên</h2>
        <p class="ad-head-sub">Tài khoản mới có toàn quyền vào trang quản trị</p>
    </div>
</div>

<?php MessageHelper::logMessage(); ?>

<form class="ad-form-layout" action="index.php?controller=accountAdmin&action=addAccountAdmin" method="post" data-validate autocomplete="off">
    <div class="ad-form-main">
        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><i class="fas fa-key"></i> Thông tin đăng nhập</h3>
            </div>
            <div class="ad-card-body">
                <div class="ad-grid-2">
                    <div class="ad-field">
                        <label class="ad-label" for="username">Tên đăng nhập <span class="ad-req">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" maxlength="10" placeholder="VD: admin001"
                            autocomplete="off" data-label="tên đăng nhập" data-rules="required|min:3|max:10">
                        <div class="ad-error" data-error-for="username"></div>
                        <div class="ad-hint">3–10 ký tự.</div>
                    </div>
                    <div class="ad-field">
                        <label class="ad-label" for="password">Mật khẩu <span class="ad-req">*</span></label>
                        <div class="ad-input-group">
                            <input type="password" class="form-control" id="password" name="password" autocomplete="new-password"
                                data-label="mật khẩu" data-rules="required">
                            <button type="button" class="btn btn-sm position-absolute top-50 end-0 translate-middle-y me-1 ad-muted"
                                id="togglePassword" aria-label="Hiện mật khẩu"><i class="fas fa-eye"></i></button>
                        </div>
                        <div class="ad-error" data-error-for="password"></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="ad-card">
            <div class="ad-card-head">
                <h3 class="ad-card-title"><i class="fas fa-id-card"></i> Thông tin cá nhân</h3>
            </div>
            <div class="ad-card-body">
                <div class="ad-grid-2">
                    <div class="ad-field ad-span-2">
                        <label class="ad-label" for="full_name">Họ và tên <span class="ad-req">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" placeholder="VD: Nguyễn Minh Quân"
                            data-label="họ và tên" data-rules="required|max:100">
                        <div class="ad-error" data-error-for="full_name"></div>
                    </div>
                    <div class="ad-field">
                        <label class="ad-label" for="email">Email <span class="ad-req">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="ten@vidu.com"
                            data-label="email" data-rules="required|email">
                        <div class="ad-error" data-error-for="email"></div>
                    </div>
                    <div class="ad-field">
                        <label class="ad-label" for="phone">Số điện thoại <span class="ad-req">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="VD: 0377931295" inputmode="numeric"
                            data-label="số điện thoại" data-rules="required|phone">
                        <div class="ad-error" data-error-for="phone"></div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="ad-form-side">
        <section class="ad-card">
            <div class="ad-card-body ad-small ad-muted">
                <i class="fas fa-shield-halved me-1"></i> Tên đăng nhập và email không được trùng với tài khoản đã có. Mật khẩu được mã hóa trước khi lưu.
            </div>
        </section>
    </div>

    <section class="ad-card ad-form-actions-card">
        <div class="ad-card-body">
            <div class="ad-form-actions">
                <a href="index.php?controller=accountAdmin&action=listAccountAdmin" class="btn ad-btn-light">Hủy</a>
                <button type="submit" name="btnAddAccountAdmin" value="1" class="btn ad-btn-primary">
                    <i class="fas fa-user-plus"></i> Tạo tài khoản
                </button>
            </div>
        </div>
    </section>
</form>

<script>
    (function() {
        const btn = document.getElementById('togglePassword');
        const input = document.getElementById('password');
        btn.addEventListener('click', function() {
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show ? '<i class="fas fa-eye-slash"></i>' : '<i class="fas fa-eye"></i>';
            btn.setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        });
    })();
</script>
