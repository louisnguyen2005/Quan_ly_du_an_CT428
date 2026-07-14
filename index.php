<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/helpers/security.php';

// Session hardening: HttpOnly + SameSite giảm rủi ro session theft/CSRF ở web truyền thống.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once 'controllers/authController.php';
require_once 'controllers/userController.php';
require_once 'controllers/roleController.php';
require_once 'controllers/projectController.php';
require_once 'controllers/taskController.php';
require_once 'controllers/notificationController.php';
require_once 'controllers/dashboardController.php';

$action = $_GET['action'] ?? '';

function is_ajax_request(): bool
{
    return app_is_ajax_request();
}

function ajax_response(bool $success, string $message, ?string $redirect = null, array $extra = []): void
{
    if (is_ajax_request()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message,
            'redirect' => $redirect,
        ], $extra));
        exit;
    }
}

function redirect_or_json(string $url, bool $success = true, string $message = ''): void
{
    ajax_response($success, $message ?: ($success ? 'Thao tác thành công' : 'Thao tác thất bại'), $url);
    header('Location:' . $url);
    exit;
}


if ($action === 'loginSubmit') {
    $authController = new AuthController();
    $authController->login();
    exit;
}

if ($action === 'apiLogin') {
    (new AuthController())->apiLogin();
    exit;
}

if ($action === 'apiRefreshToken') {
    (new AuthController())->apiRefreshToken();
    exit;
}

// if ($action === 'apiMe') {
//     require_action_role($action);
//     (new AuthController())->apiMe();
//     exit;
// }

if ($action === 'apiLogout') {
    require_action_role($action);
    (new AuthController())->logout();
    exit;
}

if ($action !== '') {
    require_action_role($action);
}

if ($action === 'updateNotificationSettings') {
    $controller = new NotificationController();
    $userId = (int)($_SESSION['user_id'] ?? 0);

    if ($userId <= 0) {
        header('Location:index.php?page=login');
        exit;
    }

    $result = $controller->updatePreferences($userId, $_POST);

    $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
    if ($isAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => (bool)$result,
            'message' => $result
                ? 'Cập nhật cài đặt thông báo thành công'
                : 'Cập nhật cài đặt thông báo thất bại'
        ]);
        exit;
    }

    $_SESSION[$result ? 'settings_success' : 'settings_error'] = $result
        ? 'Cập nhật cài đặt thông báo thành công'
        : 'Cập nhật cài đặt thông báo thất bại';

    $roleId = (int)($_SESSION['role_id'] ?? 0);

    if ($roleId === 2) {
        header('Location:index.php?page=manager-settings');
    } elseif ($roleId === 3) {
        header('Location:index.php?page=staff-settings');
    } else {
        header('Location:index.php?page=settings');
    }

    exit;
}

if ($action === 'changePassword') {
    $authController = new AuthController();
    $authController->changePassword();
    exit;
}

if ($action === 'createUser') {
    $controller = new UserController();
    $result = $controller->store();
    $message = $result ? 'Thêm người dùng thành công!' : ($controller->getLastError() ?: 'Thêm người dùng thất bại!');

    ajax_response((bool)$result, $message, $result ? 'index.php?page=users' : null);

    if ($result) {
        echo "<script>alert('Thêm người dùng thành công!'); window.location='index.php?page=users';</script>";
    } else {
        echo "<script>alert('" . addslashes($message) . "'); window.location='index.php?page=users';</script>";
    }

    exit;
}

if ($action === 'updateUser') {
    $controller = new UserController();
    $controller->update($_GET['id']);

    redirect_or_json('index.php?page=users', true, 'Cập nhật người dùng thành công');
}

if ($action === 'deleteUser') {
    $controller = new UserController();
    $result = $controller->destroy($_GET['id']);

    ajax_response((bool)$result, $result ? 'Xóa người dùng thành công' : 'Không thể xóa người dùng này. Bạn không thể xóa chính tài khoản đang đăng nhập hoặc dữ liệu đang bị ràng buộc.', 'index.php?page=users');

    if ($result) {
        header('Location:index.php?page=users');
    } else {
        echo "<script>alert('Không thể xóa người dùng này. Bạn không thể xóa chính tài khoản đang đăng nhập hoặc dữ liệu đang bị ràng buộc.'); window.location='index.php?page=users';</script>";
    }

    exit;
}

if ($action === 'toggleUserStatus') {
    $controller = new UserController();
    $controller->toggleStatus();

    redirect_or_json('index.php?page=users', true, 'Cập nhật trạng thái người dùng thành công');
}

if ($action === 'updateUserRole') {
    $controller = new UserController();
    $result = $controller->updateRole();

    ajax_response((bool)$result, $result ? 'Cập nhật quyền thành công' : 'Cập nhật quyền thất bại', $result ? 'index.php?page=roles' : null);

    if ($result) {
        echo "<script>alert('Cập nhật quyền thành công'); window.location='index.php?page=roles';</script>";
    } else {
        echo "<script>alert('Cập nhật quyền thất bại'); history.back();</script>";
    }

    exit;
}

if ($action === 'apiMe') {
    header('Content-Type: application/json; charset=utf-8');

    $payload = get_bearer_token_payload();

    if (!$payload) {
        echo json_encode([
            'success' => false,
            'message' => 'Token không hợp lệ hoặc thiếu Authorization Bearer'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Thông tin token hợp lệ',
        'user' => $payload
    ], JSON_UNESCAPED_UNICODE);
    exit;
}



/* MANAGER PROJECT/TASK ACTIONS */
if (in_array($action, ['managerCreateProject', 'managerUpdateProject', 'managerDeleteProject', 'managerCreateTask', 'managerUpdateTask', 'managerDeleteTask', 'managerAddTaskComment', 'managerGetTaskComments', 'managerAddTaskCommentAjax'], true)) {
    require_once 'config/database.php';
    $db = new Database();
    $pdo = $db->connect();
    $managerId = (int)($_SESSION['user_id'] ?? 0);
    function updateProjectStatus(PDO $pdo, int $projectId): void
    {
        $stmt = $pdo->prepare("
        SELECT
            COUNT(*) AS total_tasks,
            SUM(CASE WHEN status='Completed' THEN 1 ELSE 0 END) AS completed_tasks
        FROM tasks
        WHERE project_id=?
        AND is_deleted=0
    ");

        $stmt->execute([$projectId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $total = (int)$row['total_tasks'];
        $completed = (int)$row['completed_tasks'];

        if ($total === 0) {
            $status = 'Planning';
            $progress = 0;
        } elseif ($completed === $total) {
            $status = 'Completed';
            $progress = 100;
        } else {
            $status = 'In Progress';
            $progress = round($completed / $total * 100);
        }

        $pdo->prepare("
        UPDATE projects
        SET status=?,
            progress=?
        WHERE project_id=?
    ")->execute([
            $status,
            $progress,
            $projectId
        ]);
    }


    function managerUploadTaskAttachment(PDO $pdo, int $taskId, int $managerId): void
    {
        if (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
            return;
        }

        $allowedExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', 'png', 'jpg', 'jpeg'];
        $originalName = basename($_FILES['attachment']['name']);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($ext === '' || !in_array($ext, $allowedExt, true)) {
            return;
        }

        $uploadDir = __DIR__ . '/uploads/tasks/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newName = time() . '_' . bin2hex(random_bytes(6)) . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        $target = $uploadDir . $newName;

        if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $target)) {
            return;
        }

        $stmt = $pdo->prepare("INSERT INTO attachments(task_id,file_name,file_path,file_type,file_size,uploaded_by) VALUES(?,?,?,?,?,?)");
        $stmt->execute([
            $taskId,
            $originalName,
            'uploads/tasks/' . $newName,
            $_FILES['attachment']['type'] ?? null,
            $_FILES['attachment']['size'] ?? 0,
            $managerId
        ]);
    }

    function managerShouldNotify(PDO $pdo, int $userId, string $type): bool
    {
        try {
            $stmt = $pdo->prepare("SELECT task_notifications, comment_notifications, deadline_notifications FROM notification_preferences WHERE user_id = ? LIMIT 1");
            $stmt->execute([$userId]);
            $prefs = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$prefs) {
                return true;
            }

            $type = strtolower($type);

            if (str_contains($type, 'task') && (int)$prefs['task_notifications'] === 0) {
                return false;
            }

            if (str_contains($type, 'comment') && (int)$prefs['comment_notifications'] === 0) {
                return false;
            }

            if ((str_contains($type, 'deadline') || str_contains($type, 'due')) && (int)$prefs['deadline_notifications'] === 0) {
                return false;
            }
        } catch (Throwable $e) {
            return true;
        }

        return true;
    }

    function managerCreateNotification(PDO $pdo, int $userId, string $type, string $title, string $content, string $relatedType = 'task', ?int $relatedId = null): void
    {
        if ($userId <= 0 || !managerShouldNotify($pdo, $userId, $type)) {
            return;
        }

        $stmt = $pdo->prepare("INSERT INTO notifications(user_id,type,title,content,related_type,related_id,is_read,created_at) VALUES(?,?,?,?,?,?,0,NOW())");
        $stmt->execute([$userId, $type, $title, $content, $relatedType, $relatedId]);
    }

    if (($roleId ?? ($_SESSION['role_id'] ?? 0)) != 2 || $managerId <= 0) {
        header('Location:index.php?page=login');
        exit;
    }

    if ($action === 'managerCreateProject') {
        $code = 'PRJ-' . date('YmdHis');

        $stmt = $pdo->prepare("
        INSERT INTO projects
        (
            project_code,
            name,
            description,
            start_date,
            end_date,
            created_by,
            manager_id,
            status,
            priority,
            progress,
            is_deleted
        )
        VALUES
        (
            ?,?,?,?,?,?,?,
            'Planning',
            'Medium',
            0,
            0
        )
    ");

        $stmt->execute([
            $code,
            $_POST['name'] ?? '',
            $_POST['description'] ?? '',
            $_POST['start_date'] ?? null,
            $_POST['end_date'] ?? null,
            $managerId,
            $managerId
        ]);

        $projectId = (int)$pdo->lastInsertId();

        $pdo->prepare("
        INSERT INTO project_members
        (
            project_id,
            user_id,
            role_in_project
        )
        VALUES
        (
            ?,?,?
        )
    ")->execute([
            $projectId,
            $managerId,
            'Manager'
        ]);

        foreach ($_POST['members'] ?? [] as $uid) {
            $uid = (int)$uid;

            if ($uid > 0) {
                $pdo->prepare("
                INSERT IGNORE INTO project_members
                (
                    project_id,
                    user_id,
                    role_in_project
                )
                VALUES
                (
                    ?,?,?
                )
            ")->execute([
                    $projectId,
                    $uid,
                    'Member'
                ]);
            }
        }

        $pdo->prepare("
        INSERT INTO audit_logs
        (
            user_id,
            action,
            entity,
            entity_id,
            project_id,
            description
        )
        VALUES
        (
            ?,?,?,?,?,?
        )
    ")->execute([
            $managerId,
            'CREATE_PROJECT',
            'projects',
            $projectId,
            $projectId,
            'đã tạo dự án "' . ($_POST['name'] ?? '') . '"'
        ]);

        redirect_or_json('index.php?page=projects', true, 'Thao tác dự án thành công');
    }

    if ($action === 'managerUpdateProject') {
        $projectId = (int)($_POST['project_id'] ?? 0);

        $stmt = $pdo->prepare("UPDATE projects SET name=?, description=?, start_date=?, end_date=?, manager_id=? WHERE project_id=? AND is_deleted=0 AND (created_by=? OR manager_id=?)");
        $stmt->execute([
            $_POST['name'] ?? '',
            $_POST['description'] ?? '',
            $_POST['start_date'] ?? null,
            $_POST['end_date'] ?? null,
            $managerId,
            $projectId,
            $managerId,
            $managerId
        ]);

        if (isset($_POST['members'])) {
            $pdo->prepare("DELETE FROM project_members WHERE project_id=?")->execute([$projectId]);

            $pdo->prepare("INSERT INTO project_members(project_id,user_id,role_in_project) VALUES(?,?,?)")
                ->execute([$projectId, $managerId, 'Manager']);

            foreach (($_POST['members'] ?? []) as $uid) {
                $uid = (int)$uid;

                if ($uid > 0 && $uid !== $managerId) {
                    $pdo->prepare("INSERT IGNORE INTO project_members(project_id,user_id,role_in_project) VALUES(?,?,?)")
                        ->execute([$projectId, $uid, 'Member']);
                }
            }
        }

        $pdo->prepare("INSERT INTO audit_logs(user_id,action,entity,entity_id,project_id,description) VALUES(?,?,?,?,?,?)")
            ->execute([
                $managerId,
                'UPDATE_PROJECT',
                'projects',
                $projectId,
                $projectId,
                'đã cập nhật dự án "' . ($_POST['name'] ?? '') . '"'
            ]);

        redirect_or_json('index.php?page=project-detail&id=' . $projectId, true, 'Cập nhật dự án thành công');
    }

    if ($action === 'managerDeleteProject') {
        $projectId = (int)($_GET['id'] ?? 0);

        $check = $pdo->prepare("SELECT name FROM projects WHERE project_id=? AND is_deleted=0 AND (created_by=? OR manager_id=?)");
        $check->execute([$projectId, $managerId, $managerId]);

        if ($project = $check->fetch(PDO::FETCH_ASSOC)) {

            $pdo->prepare("DELETE FROM project_members WHERE project_id=?")->execute([$projectId]);

            $pdo->prepare("DELETE FROM projects WHERE project_id=?")->execute([$projectId]);

            $pdo->prepare("INSERT INTO audit_logs(user_id,action,entity,entity_id,project_id,description) VALUES(?,?,?,?,?,?)")
                ->execute([
                    $managerId,
                    'DELETE_PROJECT',
                    'projects',
                    $projectId,
                    $projectId,
                    'đã xóa dự án "' . $project['name'] . '"'
                ]);
        }

        redirect_or_json('index.php?page=projects', true, 'Thao tác dự án thành công');
    }

    if ($action === 'managerCreateTask') {
        $stmt = $pdo->prepare("INSERT INTO tasks(project_id,title,description,status,priority,assignee_id,created_by,due_date,is_deleted) VALUES(?,?,?,?,?,?,?,?,0)");
        $stmt->execute([
            (int)$_POST['project_id'],
            $_POST['title'] ?? '',
            $_POST['description'] ?? '',
            'Pending',
            $_POST['priority'] ?? 'Medium',
            ($_POST['assignee_id'] ?? '') !== '' ? (int)$_POST['assignee_id'] : null,
            $managerId,
            $_POST['due_date'] ?? null
        ]);

        $taskId = (int)$pdo->lastInsertId();
        managerUploadTaskAttachment($pdo, $taskId, $managerId);
        updateProjectStatus($pdo, (int)$_POST['project_id']);
        $pdo->prepare("INSERT INTO audit_logs(user_id,action,entity,entity_id,project_id,description) VALUES(?,?,?,?,?,?)")
            ->execute([
                $managerId,
                'CREATE_TASK',
                'tasks',
                $taskId,
                (int)$_POST['project_id'],
                'đã tạo nhiệm vụ "' . ($_POST['title'] ?? '') . '"'
            ]);

        $assigneeId = ($_POST['assignee_id'] ?? '') !== '' ? (int)$_POST['assignee_id'] : 0;
        if ($assigneeId > 0) {
            managerCreateNotification(
                $pdo,
                $assigneeId,
                'task',
                'Nhiệm vụ mới',
                'Bạn được giao nhiệm vụ "' . ($_POST['title'] ?? '') . '"',
                'task',
                $taskId
            );
        }

        redirect_or_json('index.php?page=tasks', true, 'Thao tác nhiệm vụ thành công');
    }

    if ($action === 'managerUpdateTask') {
        $taskId = (int)($_POST['task_id'] ?? 0);

        $stmt = $pdo->prepare("UPDATE tasks t JOIN projects p ON p.project_id=t.project_id SET t.project_id=?, t.title=?, t.description=?, t.priority=?, t.assignee_id=?, t.due_date=? WHERE t.task_id=? AND t.is_deleted=0 AND (p.created_by=? OR p.manager_id=?)");
        $stmt->execute([
            (int)$_POST['project_id'],
            $_POST['title'] ?? '',
            $_POST['description'] ?? '',
            $_POST['priority'] ?? 'Medium',
            ($_POST['assignee_id'] ?? '') !== '' ? (int)$_POST['assignee_id'] : null,
            $_POST['due_date'] ?? null,
            $taskId,
            $managerId,
            $managerId
        ]);

        managerUploadTaskAttachment($pdo, $taskId, $managerId);

        $pdo->prepare("INSERT INTO audit_logs(user_id,action,entity,entity_id,project_id,description) VALUES(?,?,?,?,?,?)")
            ->execute([
                $managerId,
                'UPDATE_TASK',
                'tasks',
                $taskId,
                (int)$_POST['project_id'],
                'đã cập nhật nhiệm vụ "' . ($_POST['title'] ?? '') . '"'
            ]);

        $assigneeId = ($_POST['assignee_id'] ?? '') !== '' ? (int)$_POST['assignee_id'] : 0;
        if ($assigneeId > 0) {
            managerCreateNotification(
                $pdo,
                $assigneeId,
                'task',
                'Nhiệm vụ được cập nhật',
                'Quản lý đã cập nhật nhiệm vụ "' . ($_POST['title'] ?? '') . '"',
                'task',
                $taskId
            );
        }

        updateProjectStatus($pdo, (int)$_POST['project_id']);
        redirect_or_json('index.php?page=tasks', true, 'Thao tác nhiệm vụ thành công');
    }

    if ($action === 'managerDeleteTask') {
        $taskId = (int)($_GET['id'] ?? 0);

        $get = $pdo->prepare("SELECT t.title,t.project_id FROM tasks t JOIN projects p ON p.project_id=t.project_id WHERE t.task_id=? AND (p.created_by=? OR p.manager_id=?)");
        $get->execute([$taskId, $managerId, $managerId]);

        if ($task = $get->fetch(PDO::FETCH_ASSOC)) {

            $pdo->prepare("DELETE FROM attachments WHERE task_id=?")->execute([$taskId]);
            $pdo->prepare("DELETE FROM comments WHERE task_id=?")->execute([$taskId]);

            $stmt = $pdo->prepare("DELETE t FROM tasks t JOIN projects p ON p.project_id=t.project_id WHERE t.task_id=? AND (p.created_by=? OR p.manager_id=?)");
            $stmt->execute([$taskId, $managerId, $managerId]);
            updateProjectStatus($pdo, (int)$task['project_id']);
            $pdo->prepare("INSERT INTO audit_logs(user_id,action,entity,entity_id,project_id,description) VALUES(?,?,?,?,?,?)")
                ->execute([
                    $managerId,
                    'DELETE_TASK',
                    'tasks',
                    $taskId,
                    $task['project_id'],
                    'đã xóa nhiệm vụ "' . $task['title'] . '"'
                ]);
        }

        redirect_or_json('index.php?page=tasks', true, 'Thao tác nhiệm vụ thành công');
    }

    if ($action === 'managerAddTaskComment') {
        $taskId = (int)($_POST['task_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        if ($taskId > 0 && $content !== '') {
            $pdo->prepare("INSERT INTO comments(task_id,user_id,content) VALUES(?,?,?)")->execute([$taskId, $managerId, $content]);
        }
        redirect_or_json('index.php?page=tasks', true, 'Thao tác nhiệm vụ thành công');
    }

    if ($action === 'managerGetTaskComments') {

        header('Content-Type: application/json');

        $taskId = (int)($_GET['task_id'] ?? 0);

        $stmt = $pdo->prepare("
        SELECT
            c.comment_id,
            c.task_id,
            c.user_id,
            c.content,
            c.created_at,
            u.username
        FROM comments c
        JOIN users u ON u.user_id = c.user_id
        WHERE c.task_id = ?
        ORDER BY c.created_at ASC
    ");

        $stmt->execute([$taskId]);

        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

        exit;
    }

    if ($action === 'managerAddTaskCommentAjax') {

        header('Content-Type: application/json');

        $taskId = (int)($_POST['task_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if ($taskId <= 0 || $content === '') {
            echo json_encode([
                'success' => false,
                'message' => 'Nội dung không hợp lệ.'
            ]);
            exit;
        }

        $stmt = $pdo->prepare("
        INSERT INTO comments(task_id,user_id,content)
        VALUES(?,?,?)
    ");

        $stmt->execute([
            $taskId,
            $managerId,
            $content
        ]);

        $stmt = $pdo->prepare("SELECT title, assignee_id FROM tasks WHERE task_id = ? LIMIT 1");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($task && !empty($task['assignee_id'])) {
            managerCreateNotification(
                $pdo,
                (int)$task['assignee_id'],
                'comment',
                'Bình luận mới',
                'Quản lý đã bình luận vào nhiệm vụ "' . $task['title'] . '"',
                'task',
                $taskId
            );
        }

        echo json_encode([
            'success' => true
        ]);

        exit;
    }
}

if ($action === 'createTask') {
    $controller = new TaskController();
    $result = $controller->store();

    ajax_response((bool)$result, $result ? 'Tạo task thành công' : 'Tạo task thất bại', $result ? 'index.php?page=tasks' : null);

    if ($result) {
        echo "<script>alert('Tạo task thành công'); window.location='index.php?page=tasks';</script>";
    } else {
        echo "<script>alert('Tạo task thất bại'); history.back();</script>";
    }

    exit;
}

if ($action === 'updateTaskStatus') {
    $controller = new TaskController();
    $result = $controller->updateStatus();

    $redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php?page=tasks';
    ajax_response((bool)$result, $result ? 'Cập nhật trạng thái thành công' : 'Cập nhật trạng thái thất bại', $result ? $redirect : null);

    if ($result) {
        header('Location:' . $redirect);
    } else {
        echo "<script>alert('Cập nhật trạng thái thất bại'); history.back();</script>";
    }

    exit;
}

if ($action === 'addTaskComment') {
    $controller = new TaskController();
    $result = $controller->addComment();
    $taskId = (int)($_POST['task_id'] ?? 0);

    ajax_response((bool)$result, $result ? 'Gửi bình luận thành công' : 'Gửi bình luận thất bại', $result ? 'index.php?page=task-detail&id=' . $taskId : null);

    if ($result) {
        header('Location:index.php?page=task-detail&id=' . $taskId);
    } else {
        echo "<script>alert('Gửi bình luận thất bại'); history.back();</script>";
    }

    exit;
}

if ($action === 'uploadTaskReport') {
    $controller = new TaskController();
    $result = $controller->uploadReport();
    $taskId = (int)($_POST['task_id'] ?? 0);

    ajax_response((bool)$result, $result ? 'Upload báo cáo thành công' : 'Upload báo cáo thất bại', $result ? 'index.php?page=task-detail&id=' . $taskId : null);

    if ($result) {
        header('Location:index.php?page=task-detail&id=' . $taskId);
    } else {
        echo "<script>alert('Upload báo cáo thất bại'); history.back();</script>";
    }

    exit;
}


if ($action === 'updateProfile') {
    $controller = new UserController();
    $result = $controller->updateProfile();

    $_SESSION[$result ? 'profile_success' : 'profile_error'] =
        $result ? 'Cập nhật hồ sơ thành công' : 'Cập nhật hồ sơ thất bại';

    $roleId = (int)($_SESSION['role_id'] ?? 0);
    if ($roleId === 1) {
        $redirect = 'index.php?page=profile';
    } elseif ($roleId === 2) {
        $redirect = 'index.php?page=manager-profile';
    } elseif ($roleId === 3) {
        $redirect = 'index.php?page=staff-profile';
    } else {
        $redirect = 'index.php?page=login';
    }

    ajax_response((bool)$result, $result ? 'Cập nhật hồ sơ thành công' : 'Cập nhật hồ sơ thất bại', $redirect);

    header('Location: ' . $redirect);
    exit;
}

if ($action === 'createProject') {
    $controller = new ProjectController();
    $result = $controller->store();

    ajax_response((bool)$result, $result ? 'Tạo dự án thành công' : 'Tạo dự án thất bại', $result ? 'index.php?page=projects' : null);

    if ($result) {
        echo "<script>alert('Tạo dự án thành công'); window.location='index.php?page=projects';</script>";
    } else {
        echo "<script>alert('Tạo dự án thất bại'); history.back();</script>";
    }

    exit;
}

$page = $_GET['page'] ?? 'dashboard';

if ($page === 'logout') {
    (new AuthController())->logout();
    exit;
}

if (!isset($_SESSION['user_id']) && $page !== 'login') {
    header('Location:index.php?page=login');
    exit;
}

if (isset($_SESSION['user_id']) && $page === 'login') {
    if ($_SESSION['role_id'] == 1) {
        header('Location:index.php?page=dashboard');
        exit;
    }

    if ($_SESSION['role_id'] == 2) {
        header('Location:index.php?page=manager-dashboard');
        exit;
    }

    if ($_SESSION['role_id'] == 3) {
        header('Location:index.php?page=staff-dashboard');
        exit;
    }
}

$roleId = $_SESSION['role_id'] ?? 0;

$adminPages = [
    'dashboard',
    'users',
    'edit-user',
    'roles'
];

$managerPages = [
    'manager-dashboard'
];

$staffPages = [
    'staff-dashboard',
    'staff-projects',
    'staff-tasks',
    'staff-report',
    'staff-kanban',
    'staff-calendar',
    'staff-notifications',
    'staff-charts',
    'staff-profile',
    'staff-settings'
];

$commonPages = [
    'projects',
    'project-detail',
    'tasks',
    'task-detail',
    'notifications',
    'profile',
    'settings',
    'manager-profile',
    'manager-settings',
    'logout'
];

if ($page !== 'login') {
    if (in_array($page, $adminPages) && $roleId != 1) {
        if ($roleId == 2) {
            header('Location:index.php?page=manager-dashboard');
            exit;
        }

        if ($roleId == 3) {
            header('Location:index.php?page=staff-dashboard');
            exit;
        }
    }

    if (in_array($page, $managerPages) && $roleId != 2) {
        if ($roleId == 1) {
            header('Location:index.php?page=dashboard');
            exit;
        }

        if ($roleId == 3) {
            header('Location:index.php?page=staff-dashboard');
            exit;
        }
    }

    if (in_array($page, $staffPages) && $roleId != 3) {
        if ($roleId == 1) {
            header('Location:index.php?page=dashboard');
            exit;
        }

        if ($roleId == 2) {
            header('Location:index.php?page=manager-dashboard');
            exit;
        }
    }

    $allowedPages = array_merge(
        $adminPages,
        $managerPages,
        $staffPages,
        $commonPages
    );

    if (!in_array($page, $allowedPages)) {
        if ($roleId == 1) {
            header('Location:index.php?page=dashboard');
            exit;
        }

        if ($roleId == 2) {
            header('Location:index.php?page=manager-dashboard');
            exit;
        }

        if ($roleId == 3) {
            header('Location:index.php?page=staff-dashboard');
            exit;
        }
    }
}



if ($page === 'manager-dashboard') {
    include 'views/dashboard/manager.php';
    exit;
}

if ($roleId == 2 && $page === 'projects') {
    include 'views/manager/projects.php';
    exit;
}
if ($roleId == 2 && $page === 'project-detail') {
    include 'views/manager/project_detail.php';
    exit;
}
if ($roleId == 2 && $page === 'tasks') {
    include 'views/manager/tasks.php';
    exit;
}
if ($roleId == 2 && $page === 'notifications') {
    include 'views/manager/notifications.php';
    exit;
}
if ($roleId == 2 && $page === 'profile') {
    header('Location:index.php?page=manager-profile');
    exit;
}
if ($roleId == 2 && $page === 'settings') {
    header('Location:index.php?page=manager-settings');
    exit;
}
if ($roleId == 2 && $page === 'manager-profile') {
    include 'views/manager/profile.php';
    exit;
}
if ($roleId == 2 && $page === 'manager-settings') {
    include 'views/manager/settings.php';
    exit;
}

if ($roleId == 3 && $page === 'tasks') {
    header('Location:index.php?page=staff-tasks');
    exit;
}

if ($roleId == 3 && $page === 'profile') {
    header('Location:index.php?page=staff-profile');
    exit;
}

if ($roleId == 3 && $page === 'settings') {
    header('Location:index.php?page=staff-settings');
    exit;
}

if ($roleId == 3 && in_array($page, $staffPages, true)) {
    include 'views/dashboard/staff.php';
    exit;
}

if ($page !== 'login') {
    include 'views/layouts/header.php';
}

echo '<div class="main-content">';

switch ($page) {
    case 'login':
        include 'views/auth/login.php';
        break;

    case 'logout':
        (new AuthController())->logout();
        break;

    case 'dashboard':
        include 'views/dashboard/index.php';
        break;

    case 'manager-dashboard':
        include 'views/dashboard/manager.php';
        break;

    case 'staff-dashboard':
        include 'views/dashboard/staff.php';
        break;

    case 'users':
        include 'views/users/index.php';
        break;

    case 'edit-user':
        include 'views/users/edit.php';
        break;

    case 'roles':
        include 'views/roles/index.php';
        break;

    case 'projects':
        include 'views/projects/index.php';
        break;

    case 'project-detail':
        include 'views/projects/project_detail.php';
        break;

    case 'tasks':
        include 'views/tasks/index.php';
        break;

    case 'task-detail':
        include 'views/tasks/task-detail.php';
        break;

    case 'notifications':
        include 'views/notifications/index.php';
        break;

    case 'profile':
        include 'views/profile/index.php';
        break;

    case 'settings':
        include 'views/settings/index.php';
        break;

    default:
        if ($roleId == 1) {
            include 'views/dashboard/index.php';
        } elseif ($roleId == 2) {
            include 'views/dashboard/manager.php';
        } elseif ($roleId == 3) {
            include 'views/dashboard/staff.php';
        }
        break;
}

echo '</div>';

if ($page !== 'login') {
    include 'views/layouts/footer.php';
}
