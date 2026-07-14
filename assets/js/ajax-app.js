// Global AJAX helper for PHP MVC actions.
// It progressively enhances old forms/links: if JavaScript fails, normal PHP redirect still works.
(function () {
    function isActionUrl(url) {
        return url && url.indexOf('index.php?action=') !== -1;
    }

    function showAjaxToast(message, ok) {
        let toast = document.getElementById('globalAjaxToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'globalAjaxToast';
            toast.className = 'global-ajax-toast';
            document.body.appendChild(toast);
        }

        toast.textContent = message || (ok ? 'Thao tác thành công' : 'Thao tác thất bại');
        toast.classList.toggle('success', !!ok);
        toast.classList.toggle('error', !ok);
        toast.classList.add('show');

        clearTimeout(window.__ajaxToastTimer);
        window.__ajaxToastTimer = setTimeout(function () {
            toast.classList.remove('show');
        }, 2000);
    }

    function setLoading(el, loading) {
        if (!el) return;
        if (loading) {
            el.dataset.oldText = el.innerHTML;
            el.disabled = true;
            el.innerHTML = 'Đang xử lý...';
        } else {
            el.disabled = false;
            if (el.dataset.oldText) el.innerHTML = el.dataset.oldText;
        }
    }

    function askConfirm(message) {
        return new Promise(function (resolve) {
            let overlay = document.getElementById('globalAjaxConfirm');
            if (overlay) overlay.remove();

            overlay = document.createElement('div');
            overlay.id = 'globalAjaxConfirm';
            overlay.className = 'global-ajax-confirm-overlay';
            overlay.innerHTML = `
                <div class="global-ajax-confirm-box">
                    <div class="global-ajax-confirm-icon">!</div>
                    <h3>Xác nhận thao tác</h3>
                    <p>${message || 'Bạn có chắc muốn thực hiện thao tác này?'}</p>
                    <div class="global-ajax-confirm-actions">
                        <button type="button" class="confirm-cancel">Hủy</button>
                        <button type="button" class="confirm-ok">Đồng ý</button>
                    </div>
                </div>
            `;
            document.body.appendChild(overlay);

            const close = function (value) {
                overlay.classList.add('hide');
                setTimeout(function () { overlay.remove(); resolve(value); }, 160);
            };

            overlay.querySelector('.confirm-cancel').addEventListener('click', function () { close(false); });
            overlay.querySelector('.confirm-ok').addEventListener('click', function () { close(true); });
            overlay.addEventListener('click', function (e) { if (e.target === overlay) close(false); });
        });
    }

    async function submitFormAjax(form) {
        const submitter = form.querySelector('[type="submit"]');
        setLoading(submitter, true);

        try {
            const response = await fetch(form.action, {
                method: (form.method || 'POST').toUpperCase(),
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            const contentType = response.headers.get('content-type') || '';
            let data = null;

            if (contentType.includes('application/json')) {
                data = await response.json();
            } else {
                // Fallback: old action returned HTML/JS instead of JSON.
                showAjaxToast('Thao tác đã gửi, đang tải lại...', true);
                setTimeout(function () { window.location.reload(); }, 600);
                return;
            }

            showAjaxToast(data.message, !!data.success);

            if (data.success && data.redirect && !form.hasAttribute('data-ajax-stay')) {
                setTimeout(function () {
                    window.location.href = data.redirect;
                }, 650);
            } else if (data.success && form.hasAttribute('data-ajax-reload')) {
                setTimeout(function () {
                    window.location.reload();
                }, 650);
            }
        } catch (error) {
            showAjaxToast('Không kết nối được server. Vui lòng thử lại.', false);
        } finally {
            setLoading(submitter, false);
        }
    }

    async function getActionAjax(url) {
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });

            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                window.location.href = url;
                return;
            }

            const data = await response.json();
            showAjaxToast(data.message, !!data.success);

            if (data.success && data.redirect) {
                setTimeout(function () {
                    window.location.href = data.redirect;
                }, 650);
            }
        } catch (error) {
            showAjaxToast('Không kết nối được server. Vui lòng thử lại.', false);
        }
    }

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.hasAttribute('data-no-ajax')) return;
        if (!isActionUrl(form.action)) return;
        if (form.action.indexOf('action=loginSubmit') !== -1) return;

        event.preventDefault();
        submitFormAjax(form);
    });

    document.addEventListener('click', async function (event) {
        const link = event.target.closest('a[href*="index.php?action="]');
        if (!link) return;
        if (link.hasAttribute('data-no-ajax')) return;

        const href = link.getAttribute('href') || '';
        const lower = href.toLowerCase();
        const isDanger = lower.includes('delete') || lower.includes('toggleuserstatus');

        if (!isDanger) return;

        event.preventDefault();

        const question = link.dataset.confirm || (lower.includes('toggleuserstatus')
            ? 'Bạn có chắc muốn thay đổi trạng thái tài khoản này?'
            : 'Bạn có chắc muốn xóa dữ liệu này?');

        const ok = await askConfirm(question);
        if (!ok) return;

        getActionAjax(link.href);
    });

    window.showAjaxToast = showAjaxToast;
})();
