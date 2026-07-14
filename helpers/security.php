<?php

/**
 * Security helpers for Project Management MVC app.
 * - RBAC backend guard
 * - Secure JSON/redirect responses
 * - JWT HS256 helpers for API usage
 * - Password policy helpers
 */

if (!defined('ROLE_ADMIN')) {
    define('ROLE_ADMIN', 1);
    define('ROLE_MANAGER', 2);
    define('ROLE_STAFF', 3);
}

function app_is_ajax_request(): bool
{
    return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || (isset($_SERVER['HTTP_ACCEPT']) && str_contains(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json'));
}

function app_json_response(bool $success, string $message, ?string $redirect = null, array $extra = [], int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array_merge([
        'success' => $success,
        'message' => $message,
        'redirect' => $redirect,
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function app_current_role_id(): int
{
    return (int)($_SESSION['role_id'] ?? 0);
}

function app_current_user_id(): int
{
    return (int)($_SESSION['user_id'] ?? 0);
}

function app_is_logged_in(): bool
{
    return app_current_user_id() > 0;
}

function app_default_page_for_role(?int $roleId = null): string
{
    $roleId = $roleId ?? app_current_role_id();

    return match ((int)$roleId) {
        ROLE_ADMIN => 'index.php?page=dashboard',
        ROLE_MANAGER => 'index.php?page=manager-dashboard',
        ROLE_STAFF => 'index.php?page=staff-dashboard',
        default => 'index.php?page=login',
    };
}

function app_forbidden(string $message = 'Bạn không có quyền thực hiện thao tác này'): void
{
    if (app_is_ajax_request()) {
        app_json_response(false, $message, null, ['code' => 'FORBIDDEN'], 403);
    }

    $_SESSION['error'] = $message;
    header('Location:' . app_default_page_for_role());
    exit;
}

function require_login(): void
{
    if (!app_is_logged_in()) {
        // Cho phép web/API dùng JWT từ Authorization Bearer hoặc cookie API_SESSION.
        $token = current_auth_token();
        $payload = is_string($token) ? jwt_decode_token($token) : null;

        if (is_array($payload) && !empty($payload['sub'])) {
            $_SESSION['user_id'] = (int)$payload['sub'];
            $_SESSION['username'] = $payload['username'] ?? '';
            $_SESSION['email'] = $payload['email'] ?? '';
            $_SESSION['role_id'] = (int)($payload['role_id'] ?? 0);
            $_SESSION['role_code'] = $payload['role_code'] ?? '';
            $_SESSION['role_name'] = match ((int)($payload['role_id'] ?? 0)) {
                ROLE_ADMIN => 'Admin',
                ROLE_MANAGER => 'Manager',
                ROLE_STAFF => 'Staff',
                default => '',
            };
            return;
        }

        if (app_is_ajax_request()) {
            app_json_response(false, 'Phiên đăng nhập đã hết hạn', 'index.php?page=login', ['code' => 'UNAUTHENTICATED'], 401);
        }

        header('Location:index.php?page=login');
        exit;
    }
}

function require_role(array $allowedRoles): void
{
    require_login();

    if (!in_array(app_current_role_id(), array_map('intval', $allowedRoles), true)) {
        app_forbidden();
    }
}

function require_action_role(string $action): void
{
    if ($action === '' || in_array($action, ['loginSubmit', 'apiLogin', 'apiRefreshToken', 'apiMe', 'apiLogout'], true)) {
        return;
    }

    $rules = [
        // Admin-only backend actions
        'createUser' => [ROLE_ADMIN],
        'updateUser' => [ROLE_ADMIN],
        'deleteUser' => [ROLE_ADMIN],
        'toggleUserStatus' => [ROLE_ADMIN],
        'updateUserRole' => [ROLE_ADMIN],
        'createProject' => [ROLE_ADMIN],
        'createTask' => [ROLE_ADMIN],

        // Manager CRUD actions
        'managerCreateProject' => [ROLE_MANAGER],
        'managerUpdateProject' => [ROLE_MANAGER],
        'managerDeleteProject' => [ROLE_MANAGER],
        'managerCreateTask' => [ROLE_MANAGER],
        'managerUpdateTask' => [ROLE_MANAGER],
        'managerDeleteTask' => [ROLE_MANAGER],
        'managerAddTaskComment' => [ROLE_MANAGER],
        'managerGetTaskComments' => [ROLE_MANAGER],
        'managerAddTaskCommentAjax' => [ROLE_MANAGER],

        // Shared authenticated actions
        'updateNotificationSettings' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
        'changePassword' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
        'updateProfile' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
        'updateTaskStatus' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
        'addTaskComment' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
        'uploadTaskReport' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
        'apiMe' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
        'apiLogout' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
    ];

    if (isset($rules[$action])) {
        require_role($rules[$action]);
        return;
    }

    // Any unknown action must at least be authenticated.
    require_login();
}

function password_policy_errors(string $password): array
{
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'ít nhất 8 ký tự';
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'có chữ thường';
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'có chữ hoa';
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'có chữ số';
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $errors[] = 'có ký tự đặc biệt';
    }

    return $errors;
}

function password_is_strong(string $password): bool
{
    return count(password_policy_errors($password)) === 0;
}

function password_policy_message(): string
{
    return 'Mật khẩu phải có ít nhất 8 ký tự, gồm chữ hoa, chữ thường, số và ký tự đặc biệt.';
}

function jwt_secret(): string
{
    $secret = getenv('JWT_SECRET');

    if (is_string($secret) && strlen($secret) >= 32) {
        return $secret;
    }

    // Demo fallback for local XAMPP. Khi deploy thật, hãy cấu hình biến môi trường JWT_SECRET >= 32 ký tự.
    return hash('sha256', __DIR__ . '|project_management|change-this-secret-in-production');
}

function base64url_encode_data(string $data): string
{
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function base64url_decode_data(string $data): string|false
{
    $remainder = strlen($data) % 4;
    if ($remainder) {
        $data .= str_repeat('=', 4 - $remainder);
    }
    return base64_decode(strtr($data, '-_', '+/'), true);
}

function jwt_encode(array $payload, int $ttlSeconds = 3600): string
{
    $now = time();
    $payload = array_merge([
        'iat' => $now,
        'nbf' => $now,
        'exp' => $now + $ttlSeconds,
        'iss' => 'Quan_ly_du_an',
    ], $payload);

    $header = ['typ' => 'JWT', 'alg' => 'HS256'];
    $header64 = base64url_encode_data(json_encode($header, JSON_UNESCAPED_UNICODE));
    $payload64 = base64url_encode_data(json_encode($payload, JSON_UNESCAPED_UNICODE));
    $signature = hash_hmac('sha256', $header64 . '.' . $payload64, jwt_secret(), true);

    return $header64 . '.' . $payload64 . '.' . base64url_encode_data($signature);
}

function jwt_decode_token(string $token): ?array
{
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        return null;
    }

    [$header64, $payload64, $signature64] = $parts;
    $expected = base64url_encode_data(hash_hmac('sha256', $header64 . '.' . $payload64, jwt_secret(), true));

    if (!hash_equals($expected, $signature64)) {
        return null;
    }

    $payloadJson = base64url_decode_data($payload64);
    if ($payloadJson === false) {
        return null;
    }

    $payload = json_decode($payloadJson, true);
    if (!is_array($payload)) {
        return null;
    }

    $now = time();
    if (isset($payload['nbf']) && $now < (int)$payload['nbf']) {
        return null;
    }
    if (isset($payload['exp']) && $now > (int)$payload['exp']) {
        return null;
    }

    return $payload;
}

function bearer_token(): ?string
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

    if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
        return trim($matches[1]);
    }

    return null;
}

function make_user_jwt(array $user, int $ttlSeconds = 3600): string
{
    return jwt_encode([
        'sub' => (int)$user['user_id'],
        'username' => $user['username'] ?? '',
        'email' => $user['email'] ?? '',
        'role_id' => (int)($user['role_id'] ?? 0),
        'role_code' => $user['role_code'] ?? '',
    ], $ttlSeconds);
}


function current_auth_token(): ?string
{
    $token = bearer_token();

    if (is_string($token) && $token !== '') {
        return $token;
    }

    if (!empty($_COOKIE['API_SESSION'])) {
        return (string)$_COOKIE['API_SESSION'];
    }

    if (!empty($_SESSION['jwt_token'])) {
        return (string)$_SESSION['jwt_token'];
    }

    return null;
}

function jwt_cookie_options(int $expires): array
{
    return [
        'expires' => $expires,
        'path' => '/',
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ];
}

function set_api_session_cookie(string $token, int $ttlSeconds = 3600): void
{
    setcookie('API_SESSION', $token, jwt_cookie_options(time() + $ttlSeconds));
    $_COOKIE['API_SESSION'] = $token;
}

function clear_api_session_cookie(): void
{
    setcookie('API_SESSION', '', jwt_cookie_options(time() - 42000));
    unset($_COOKIE['API_SESSION']);
}

function get_bearer_token_payload()
{
    $token = current_auth_token();

    if (!$token) {
        return false;
    }

    return jwt_decode_token($token) ?: false;
}

/* Compatibility aliases for older snippets used in index.php/views. */
function base64url_encode($data)
{
    return base64url_encode_data((string)$data);
}

function base64url_decode($data)
{
    return base64url_decode_data((string)$data);
}

function jwt_secret_key()
{
    return jwt_secret();
}

function createJWT(array $payload, int $ttl = 3600)
{
    return jwt_encode($payload, $ttl);
}

function decodeJWT($token)
{
    return jwt_decode_token((string)$token) ?: false;
}
