<?php

require_once 'controllers/userController.php';

$controller = new UserController();

$user = $controller->edit($_GET['id']);

?>

<style>

.edit-user-container{
    background: var(--card-bg, #ffffff);
    max-width: 700px;
    margin: 7.5rem auto;
    padding: 20px;
    border-radius: 20px;
    border: 1px solid var(--border-color, #e2e8f0);
    box-shadow: 0 4px 12px rgba(0,0,0,.05);
}

.edit-user-title{
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 30px;
}

.edit-user-form{
    display:flex;
    flex-direction:column;
    gap:20px;
}

.edit-user-form .form-group{
    display:flex;
    flex-direction:column;
    gap:8px;
}

.edit-user-form label{
    font-size:14px;
    font-weight:600;
}

.edit-user-form input,
.edit-user-form select{
    width:100%;
    height:52px;
    border:1px solid #dbe3ee;
    border-radius:14px;
    padding:0 16px;
    font-size:15px;
    outline:none;

    background: var(--input-bg, #ffffff);
    color: var(--text-color, #0f172a);
}

.edit-user-form input:focus,
.edit-user-form select:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.15);
}

.btn-submit{
    width:max-content;
    border:none;
    background:#2563eb;
    color:white;
    padding:12px 24px;
    border-radius:12px;
    font-weight:600;
    cursor:pointer;
    transition:.2s;
}

.btn-submit:hover{
    background:#1d4ed8;
}

/* ===== DARK MODE ===== */

body.dark-mode .edit-user-container{
    background:#1e293b;
    border-color:#334155;
}

body.dark-mode .edit-user-title,
body.dark-mode .edit-user-form label{
    color:#f8fafc;
}

body.dark-mode .edit-user-form input,
body.dark-mode .edit-user-form select{
    background:#0f172a;
    color:#f8fafc;
    border-color:#334155;
}

body.dark-mode .edit-user-form input::placeholder{
    color:#94a3b8;
}
</style>

<div class="edit-user-page">

    <div class="edit-user-container">

        <h2 class="edit-user-title">
            Sửa người dùng
        </h2>

        <form
            class="edit-user-form"
            method="POST"
            action="index.php?action=updateUser&id=<?= $user['user_id'] ?>">

            <div class="form-group">
                <label>Username</label>

                <input
                    type="text"
                    name="username"
                    value="<?= htmlspecialchars($user['username']) ?>">
            </div>

            <div class="form-group">
                <label>Email</label>

                <input
                    type="email"
                    name="email"
                    value="<?= htmlspecialchars($user['email']) ?>">
            </div>

            <div class="form-group">
                <label>Role</label>

                <select name="role_id">

                    <option value="1"
                        <?= $user['role_id']==1 ? 'selected' : '' ?>>
                        Admin
                    </option>

                    <option value="2"
                        <?= $user['role_id']==2 ? 'selected' : '' ?>>
                        Manager
                    </option>

                    <option value="3"
                        <?= $user['role_id']==3 ? 'selected' : '' ?>>
                        Staff
                    </option>

                </select>
            </div>

            <button type="submit" class="btn-submit">
                Cập nhật
            </button>

        </form>

    </div>

</div>