<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PMS</title>
    <link rel="icon" type="image/svg+xml" href="assets/images/projecthub-icon.svg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }
        body { background: #f5f7fb; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        
        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 450px;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            text-align: center;
        }
        
        .logo-box {
            width: 64px;
            height: 64px;
            background: #2563eb;
            color: white;
            border-radius: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 28px;
            margin: 0 auto 24px auto;
        }
        
        .login-card h2 { font-size: 24px; color: #1e293b; font-weight: 700; margin-bottom: 8px; }
        .login-card p { color: #64748b; font-size: 15px; margin-bottom: 24px; }
        
        /* Giao diện thông báo lỗi đăng nhập */
        .error-alert {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #b91c1c;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
            text-align: left;
        }
        
        .form-group { margin-bottom: 20px; text-align: left; position: relative; }
        .form-group label { display: block; font-size: 15px; font-weight: 600; color: #1e293b; margin-bottom: 8px; }
        
        .input-wrapper { position: relative; }
        .input-wrapper i { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #64748b; font-size: 18px; }
        .input-wrapper input {
            width: 100%;
            height: 52px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding-left: 48px;
            padding-right: 16px;
            font-size: 15px;
            outline: none;
            transition: all 0.2s;
        }
        .input-wrapper input:focus { border-color: #2563eb; background: #fff; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }
        
        .form-options { display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; font-size: 14px; }
        .remember-me { display: flex; align-items: center; gap: 8px; color: #64748b; cursor: pointer; }
        .remember-me input { width: 16px; height: 16px; accent-color: #2563eb; }
        .forgot-pass { color: #2563eb; text-decoration: none; font-weight: 500; }
        .forgot-pass:hover { text-decoration: underline; }
        
        .btn-login {
            width: 100%;
            height: 52px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-login:hover { opacity: 0.95; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="logo-box">
        <i class="fa-regular fa-clipboard"></i>
    </div>
    <h2>Chào mừng trở lại</h2>
    <p>Đăng nhập vào hệ thống quản lý dự án</p>
    
    <?php if (isset($_SESSION['login_error'])): ?>
        <div class="error-alert">
            <i class="fa-solid fa-circle-exclamation"></i> <?= $_SESSION['login_error']; ?>
            <?php unset($_SESSION['login_error']); // Xem xong thì xóa lỗi khỏi bộ nhớ ?>
        </div>
    <?php endif; ?>
    
    <form action="index.php?action=loginSubmit" method="POST">
        <div class="form-group">
            <label>Email</label>
            <div class="input-wrapper">
                <i class="fa-regular fa-envelope"></i>
                <input type="email" name="email" placeholder="your.email@company.com" required>
            </div>
        </div>
        
        <div class="form-group">
            <label>Mật khẩu</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>
        </div>
        
        <div class="form-options">
            <label class="remember-me">
                <input type="checkbox" name="remember"> Ghi nhớ đăng nhập
            </label>
            <a href="#" class="forgot-pass">Quên mật khẩu?</a>
        </div>
        
        <button type="submit" class="btn-login">Đăng nhập</button>
    </form>
</div>

</body>
</html>