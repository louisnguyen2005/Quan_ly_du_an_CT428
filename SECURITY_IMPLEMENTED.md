# Security features đã thêm

## 1. Password Security / Cryptographic Failure

Áp dụng vào:
- Đăng nhập: `controllers/authController.php`
- Đổi mật khẩu: `controllers/authController.php`
- Tạo user: `models/user.php`

Nội dung:
- Dùng `password_hash(..., PASSWORD_DEFAULT)` khi tạo/cập nhật mật khẩu.
- Dùng `password_verify()` khi đăng nhập/đổi mật khẩu.
- Nếu gặp password cũ dạng plain-text, hệ thống chỉ cho qua một lần nếu khớp rồi tự hash lại ngay.
- Dùng `password_needs_rehash()` để nâng cấp hash khi thuật toán PHP thay đổi.
- Chính sách mật khẩu mới: tối thiểu 8 ký tự, có chữ hoa, chữ thường, số, ký tự đặc biệt.
- Sau khi đổi mật khẩu, refresh token bị xóa và user phải đăng nhập lại.
- Sau khi login thành công có `session_regenerate_id(true)` để chống session fixation.
- Session cookie dùng HttpOnly + SameSite=Lax.

## 2. RBAC Backend

Áp dụng vào:
- `helpers/security.php`
- `index.php`

Nội dung:
- Thêm các hàm `require_login()`, `require_role()`, `require_action_role()`.
- Không chỉ ẩn nút ở giao diện, backend cũng chặn action theo role.
- Admin-only: create/update/delete/toggle user, update role, admin create project/task.
- Manager-only: toàn bộ `managerCreateProject`, `managerUpdateProject`, `managerDeleteProject`, `managerCreateTask`, `managerUpdateTask`, `managerDeleteTask`, comment AJAX manager.
- Authenticated: profile, settings, đổi mật khẩu, notification settings, update task status, comment, upload report.

## 3. JWT

Áp dụng vào:
- `helpers/security.php`
- `controllers/authController.php`
- `models/user.php`
- `index.php`

Endpoint:
- `POST index.php?action=apiLogin`
- `POST index.php?action=apiRefreshToken`
- `GET index.php?action=apiMe`
- `POST/GET index.php?action=apiLogout`

Cách dùng:

```bash
curl -X POST http://localhost/WebPrograming/Quan_ly_du_an/index.php?action=apiLogin \
  -d "email=manager@gmail.com" \
  -d "password=123456"
```

Sau khi nhận `access_token`:

```bash
curl http://localhost/WebPrograming/Quan_ly_du_an/index.php?action=apiMe \
  -H "Authorization: Bearer ACCESS_TOKEN"
```

Lưu ý deploy:
- Cấu hình biến môi trường `JWT_SECRET` dài tối thiểu 32 ký tự.
- Ví dụ Apache/XAMPP có thể cấu hình trong vhost hoặc `.env` nếu sau này project có dotenv.
