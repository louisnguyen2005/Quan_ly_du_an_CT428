<?php

require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../helpers/security.php';

class AuthController
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    private function settingsRedirect(): string
    {
        $roleId = (int)($_SESSION['role_id'] ?? 0);

        if ($roleId === ROLE_MANAGER) {
            return 'index.php?page=manager-settings';
        }

        if ($roleId === ROLE_STAFF) {
            return 'index.php?page=staff-settings';
        }

        return 'index.php?page=settings';
    }

    private function verifyPasswordAndUpgrade(array $user, string $inputPassword): bool
    {
        $storedPassword = (string)($user['password'] ?? '');

        if (password_verify($inputPassword, $storedPassword)) {
            if (password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
                $this->userModel->updatePassword((int)$user['user_id'], password_hash($inputPassword, PASSWORD_DEFAULT));
            }
            return true;
        }

        // Tương thích dữ liệu cũ nếu từng seed password plain-text.
        // Nếu khớp, hệ thống lập tức hash lại để không tiếp tục lưu cryptographic failure.
        $passwordInfo = password_get_info($storedPassword);
        if (($passwordInfo['algo'] ?? 0) === 0 && hash_equals($storedPassword, $inputPassword)) {
            $this->userModel->updatePassword((int)$user['user_id'], password_hash($inputPassword, PASSWORD_DEFAULT));
            return true;
        }

        return false;
    }

    private function issueLoginTokens(array $user): array
    {
        $accessToken = make_user_jwt($user, 3600);
        $refreshToken = bin2hex(random_bytes(32));
        $refreshExpiredAt = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 7);

        $this->userModel->updateRefreshToken((int)$user['user_id'], hash('sha256', $refreshToken), $refreshExpiredAt);

        $_SESSION['jwt_token'] = $accessToken;
        $_SESSION['jwt_expires_at'] = time() + 3600;
        $_SESSION['refresh_token'] = $refreshToken;

        // Web login cũng nhận JWT qua cookie HttpOnly.
        // Cookie này dùng để chứng minh JWT và dùng cho API nếu không gửi Authorization Bearer.
        set_api_session_cookie($accessToken, 3600);

        return [
            'access_token' => $accessToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'refresh_token' => $refreshToken,
        ];
    }

    private function putUserIntoSession(array $user): void
    {
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role_id'] = (int)$user['role_id'];
        $_SESSION['role_name'] = $user['role_name'];
        $_SESSION['role_code'] = $user['role_code'];
        $_SESSION['email'] = $user['email'];

        unset($_SESSION['login_error']);
    }

    private function redirectAfterLogin(int $roleId): void
    {
        header('Location:' . app_default_page_for_role($roleId));
        exit;
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location:index.php?page=login');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->verifyPasswordAndUpgrade($user, $password)) {
            $_SESSION['login_error'] = 'Email hoặc mật khẩu không chính xác, hoặc tài khoản đã bị khóa';
            header('Location:index.php?page=login');
            exit;
        }

        $this->putUserIntoSession($user);
        $tokens = $this->issueLoginTokens($user);

        if (app_is_ajax_request()) {
            app_json_response(true, 'Đăng nhập thành công', app_default_page_for_role((int)$user['role_id']), [
                'user' => [
                    'user_id' => (int)$user['user_id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'role_id' => (int)$user['role_id'],
                    'role_name' => $user['role_name'],
                ],
                'jwt' => $tokens,
            ]);
        }

        $this->redirectAfterLogin((int)$user['role_id']);
    }

    public function apiLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            app_json_response(false, 'Method không hợp lệ', null, [], 405);
        }

        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $user = $this->userModel->findByEmail($email);

        if (!$user || !$this->verifyPasswordAndUpgrade($user, $password)) {
            app_json_response(false, 'Email hoặc mật khẩu không chính xác', null, ['code' => 'INVALID_CREDENTIALS'], 401);
        }

        $this->putUserIntoSession($user);
        $tokens = $this->issueLoginTokens($user);

        app_json_response(true, 'Đăng nhập API thành công', app_default_page_for_role((int)$user['role_id']), [
            'user' => [
                'user_id' => (int)$user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'role_id' => (int)$user['role_id'],
                'role_name' => $user['role_name'],
            ],
            'jwt' => $tokens,
        ]);
    }

    public function apiMe(): void
    {
        $payload = get_bearer_token_payload();

        if (!$payload) {
            app_json_response(false, 'Token không hợp lệ hoặc thiếu Authorization Bearer/API_SESSION', null, ['code' => 'INVALID_TOKEN'], 401);
        }

        app_json_response(true, 'Thông tin token hợp lệ', null, ['user' => $payload]);
    }

    public function apiRefreshToken(): void
    {
        $refreshToken = trim($_POST['refresh_token'] ?? ($_SESSION['refresh_token'] ?? ''));

        if ($refreshToken === '') {
            app_json_response(false, 'Thiếu refresh token', null, ['code' => 'MISSING_REFRESH_TOKEN'], 400);
        }

        $user = $this->userModel->findByRefreshToken(hash('sha256', $refreshToken));

        if (!$user) {
            app_json_response(false, 'Refresh token không hợp lệ hoặc đã hết hạn', null, ['code' => 'INVALID_REFRESH_TOKEN'], 401);
        }

        $this->putUserIntoSession($user);
        $tokens = $this->issueLoginTokens($user);

        app_json_response(true, 'Làm mới token thành công', null, ['jwt' => $tokens]);
    }

    public function changePassword(): void
    {
        require_login();

        $userId = app_current_user_id();

        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        $user = $this->userModel->findById($userId);

        if (!$user) {
            $_SESSION['settings_error'] = 'Không tìm thấy tài khoản';
            redirect_or_json($this->settingsRedirect(), false, 'Không tìm thấy tài khoản');
        }

        if (!$this->verifyPasswordAndUpgrade($user, $currentPassword)) {
            $_SESSION['settings_error'] = 'Mật khẩu hiện tại không đúng';
            redirect_or_json($this->settingsRedirect(), false, 'Mật khẩu hiện tại không đúng');
        }

        if (!password_is_strong($newPassword)) {
            $message = password_policy_message();
            $_SESSION['settings_error'] = $message;
            redirect_or_json($this->settingsRedirect(), false, $message);
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['settings_error'] = 'Xác nhận mật khẩu không khớp';
            redirect_or_json($this->settingsRedirect(), false, 'Xác nhận mật khẩu không khớp');
        }

        if (password_verify($newPassword, $user['password'])) {
            $_SESSION['settings_error'] = 'Mật khẩu mới không được trùng mật khẩu hiện tại';
            redirect_or_json($this->settingsRedirect(), false, 'Mật khẩu mới không được trùng mật khẩu hiện tại');
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->userModel->updatePassword($userId, $hash);
        $this->userModel->clearRefreshToken($userId);

        unset($_SESSION['jwt_token'], $_SESSION['jwt_expires_at'], $_SESSION['refresh_token']);
        clear_api_session_cookie();
        $_SESSION['settings_success'] = 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại để nhận JWT mới.';

        redirect_or_json('index.php?page=login', true, 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.');
    }

    public function logout(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->userModel->clearRefreshToken((int)$_SESSION['user_id']);
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'] ?? '/',
                'domain' => $params['domain'] ?? '',
                'secure' => (bool)($params['secure'] ?? false),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        // JWT phải được xóa trước khi trả AJAX/redirect.
        clear_api_session_cookie();

        session_destroy();

        if (app_is_ajax_request()) {
            app_json_response(true, 'Đăng xuất thành công', 'index.php?page=login');
        }
        header('Location:index.php?page=login');
        exit;
    }
}
