<?php

/**
 * Security helpers for Project Management MVC app.
 * - RBAC backend guard
 * - SQLi/XSS/CSRF helpers
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

function app_security_headers(): void
{
    if (!headers_sent()) {
        header('X-Frame-Options: SAMEORIGIN');
        header('X-Content-Type-Options: nosniff');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        header("Content-Security-Policy: default-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com data:; img-src 'self' data:; connect-src 'self'");
    }
}

function app_escape($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_e($value): void
{
    echo app_escape($value);
}

function app_clean_string(string $value, bool $stripTags = true): string
{
    $value = trim($value);
    $value = str_replace("\0", '', $value);

    if ($stripTags) {
        $value = strip_tags($value);
    }

    return $value;
}

function app_clean_input($value)
{
    if (is_array($value)) {
        $clean = [];
        foreach ($value as $key => $item) {
            $cleanKey = is_string($key) ? app_clean_string($key) : $key;
            $clean[$cleanKey] = app_clean_input($item);
        }
        return $clean;
    }

    if (is_string($value)) {
        return app_clean_string($value);
    }

    return $value;
}

function app_int($value, int $default = 0): int
{
    $filtered = filter_var($value, FILTER_VALIDATE_INT);
    return $filtered === false ? $default : (int)$filtered;
}

function app_email(string $value): string
{
    $value = app_clean_string($value);
    return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : '';
}

function app_safe_sql_identifier(string $identifier, array $allowed): string
{
    if (!in_array($identifier, $allowed, true)) {
        throw new InvalidArgumentException('Invalid SQL identifier.');
    }

    return '`' . str_replace('`', '``', $identifier) . '`';
}

function app_pdo_execute(PDO $pdo, string $sql, array $params = []): PDOStatement
{
    $stmt = $pdo->prepare($sql);
    foreach ($params as $key => $value) {
        $param = is_int($value) ? PDO::PARAM_INT : (is_bool($value) ? PDO::PARAM_BOOL : PDO::PARAM_STR);
        $placeholder = is_int($key) ? $key + 1 : $key;
        $stmt->bindValue($placeholder, $value, $param);
    }
    $stmt->execute();
    return $stmt;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf_token" value="' . app_escape(csrf_token()) . '">';
}

function csrf_meta_tag(): string
{
    return '<meta name="csrf-token" content="' . app_escape(csrf_token()) . '">';
}

function csrf_token_from_request(): string
{
    return (string)(
        $_POST['_csrf_token']
        ?? $_GET['_csrf_token']
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']
        ?? $_SERVER['HTTP_X_XSRF_TOKEN']
        ?? ''
    );
}

function csrf_verify(?string $token = null): bool
{
    $token = $token ?? csrf_token_from_request();
    return is_string($token)
        && $token !== ''
        && isset($_SESSION['_csrf_token'])
        && hash_equals((string)$_SESSION['_csrf_token'], $token);
}

function require_csrf_token(bool $protectGet = false): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && !$protectGet) {
        return;
    }

    if (!csrf_verify()) {
        if (app_is_ajax_request()) {
            app_json_response(false, 'CSRF token không hợp lệ hoặc đã hết hạn', null, ['code' => 'CSRF_INVALID'], 419);
        }

        $_SESSION['error'] = 'CSRF token không hợp lệ hoặc đã hết hạn';
        header('Location:' . app_default_page_for_role());
        exit;
    }
}

function csrf_action_requires_token(string $action): bool
{
    $protectedActions = [
        'updateNotificationSettings',
        'changePassword',
        'createUser',
        'updateUser',
        'deleteUser',
        'toggleUserStatus',
        'updateUserRole',
        'createProject',
        'createTask',
        'managerCreateProject',
        'managerUpdateProject',
        'managerDeleteProject',
        'managerCreateTask',
        'managerUpdateTask',
        'managerDeleteTask',
        'managerAddTaskComment',
        'managerAddTaskCommentAjax',
        'managerApproveTask',
        'managerRejectTask',
        'updateTaskStatus',
        'addTaskComment',
        'uploadTaskReport',
        'saveTaskProgressDraft',
        'submitTaskProgress',
        'updateProfile',
    ];

    return in_array($action, $protectedActions, true);
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
        'managerApproveTask' => [ROLE_MANAGER],
        'managerRejectTask' => [ROLE_MANAGER],

        // Shared authenticated actions
        'ajaxTaskDetail' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
        'ajaxProjectDetail' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
        'updateNotificationSettings' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
        'changePassword' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
        'updateProfile' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
        'updateTaskStatus' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
        'addTaskComment' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
        'uploadTaskReport' => [ROLE_ADMIN, ROLE_MANAGER, ROLE_STAFF],
        'saveTaskProgressDraft' => [ROLE_STAFF],
        'submitTaskProgress' => [ROLE_STAFF],
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

/**
 * Nhãn trạng thái task hiển thị theo vai trò người xem, dùng cho JSON trả về của
 * ajaxTaskDetail (staff_modals.js, approval.js) - dùng chung để 2 modal AJAX luôn khớp với
 * badge render sẵn trong PHP (mstatus() bên manager, staff_status_label() bên staff).
 *
 * - Pending/In Progress: manager luôn thấy "Đang thực hiện"; staff thấy "Cần làm", trừ khi còn
 *   rejected_reason (chưa cập nhật lại) thì staff thấy "Bị từ chối".
 * - Pending Approval: cả hai thấy "Chờ duyệt".
 * - Completed: cả hai thấy "Hoàn thành".
 */
function task_display_status(string $status, int $roleId, ?string $rejectedReason = null): string
{
    $isStaff = $roleId === ROLE_STAFF;

    if (in_array($status, ['Pending', 'In Progress'], true)) {
        if ($isStaff && !empty($rejectedReason)) {
            return 'Bị từ chối';
        }

        return $isStaff ? 'Cần làm' : 'Đang thực hiện';
    }

    return match ($status) {
        'Pending Approval' => 'Chờ duyệt',
        'Completed' => 'Hoàn thành',
        default => $status,
    };
}

/**
 * Class màu cho badge trạng thái task, đi kèm task_display_status() ở trên - dùng để tô màu
 * badge trong modal chi tiết task (staff_modals.js, approval.js).
 */
function task_display_status_class(string $status, int $roleId, ?string $rejectedReason = null): string
{
    $isStaff = $roleId === ROLE_STAFF;

    if (in_array($status, ['Pending', 'In Progress'], true)) {
        // "in-progress" (xanh dương) để khớp màu mtaskclass() bên Manager cho cùng trạng thái này.
        return ($isStaff && !empty($rejectedReason)) ? 'danger' : 'in-progress';
    }

    return match ($status) {
        'Pending Approval' => 'pending-approval',
        'Completed' => 'completed',
        default => 'muted',
    };
}

/** Nhãn độ ưu tiên task bằng tiếng Việt - dùng chung cho cả PHP render và JSON trả về AJAX. */
function task_priority_label(string $priority): string
{
    return match ($priority) {
        'Critical' => 'Khẩn cấp',
        'High' => 'Cao',
        'Medium' => 'Trung bình',
        'Low' => 'Thấp',
        default => $priority ?: 'Trung bình',
    };
}

/**
 * Class màu cho badge độ ưu tiên, đi kèm task_priority_label(). Dùng đúng tên class thô
 * (high/medium/low/critical) như bên Manager (mclass($priority)) để 2 bên ra CÙNG MỘT MÀU cho
 * cùng 1 mức ưu tiên - trước đây staff tự đặt tên class riêng (orange/primary/...) khiến "Cao"
 * hiện màu cam bên Staff nhưng lại màu đỏ bên Manager, không khớp nhau.
 */
function task_priority_class(string $priority): string
{
    return match ($priority) {
        'Critical' => 'critical',
        'High' => 'high',
        'Medium' => 'medium',
        'Low' => 'low',
        default => 'medium',
    };
}

/**
 * Nhãn trạng thái DỰ ÁN (khác trạng thái task: Planning/In Progress/Completed).
 * Dùng chung cho ajaxProjectDetail để modal chi tiết dự án bên Staff hiển thị tiếng Việt
 * giống hệt text bên Manager (views/manager/projects.php) thay vì để nguyên tiếng Anh.
 */
function project_status_label(string $status): string
{
    return match ($status) {
        'Planning' => 'Lập kế hoạch',
        'In Progress' => 'Đang thực hiện',
        'Completed' => 'Hoàn thành',
        default => $status ?: 'Đang thực hiện',
    };
}

/**
 * Class màu cho badge trạng thái dự án, đi kèm project_status_label(). Dùng đúng tên class thô
 * như mclass() bên Manager (planning/in-progress/completed) để 2 bên ra cùng 1 màu.
 */
function project_status_class(string $status): string
{
    return strtolower(str_replace([' ', '_'], '-', $status ?: 'planning'));
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
