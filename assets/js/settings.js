// Hàm tạo Cookie lưu cấu hình trong 30 ngày
function setCookie(name, value, days = 30) {
    const date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/`;
}

// XỬ LÝ ĐỔI DARK / LIGHT MODE
const themeSelect = document.getElementById("themeSelect");

if (themeSelect) {
    themeSelect.addEventListener("change", () => {
        // 1. Lưu cấu hình mới vào cookie 'theme'
        setCookie("theme", themeSelect.value);
        
        // 2. Tải lại trang ngay lập tức để áp dụng giao diện mới toàn hệ thống
        window.location.reload();
    });
}

// 2. XỬ LÝ ĐỔI NGÔN NGỮ
const langSelect = document.getElementById("langSelect");
if (langSelect) {
    langSelect.addEventListener("change", () => {
        setCookie("lang", langSelect.value);
        // Reload lại trang lập tức để áp dụng ngôn ngữ mới
        window.location.reload();
    });
}
// Auto-save notification toggle settings
const notificationSettingsForm = document.getElementById("notificationSettingsForm");
if (notificationSettingsForm) {
    notificationSettingsForm
        .querySelectorAll('input[type="checkbox"]')
        .forEach(input => {
            input.addEventListener("change", () => {
                notificationSettingsForm.requestSubmit ? notificationSettingsForm.requestSubmit() : notificationSettingsForm.dispatchEvent(new Event("submit", {cancelable:true, bubbles:true}));
            });
        });
}
