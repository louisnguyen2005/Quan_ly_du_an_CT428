<div id="userModal" class="modal">

    <div class="modal-content">

        <div class="modal-header">
            <h2>Thêm người dùng mới</h2>
            <span class="close-modal">&times;</span>
        </div>

        <form method="POST"
              action="index.php?action=createUser">

            <div class="form-group">
                <label>Username</label>
                <input
                    type="text"
                    name="username"
                    required>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input
                    type="email"
                    name="email"
                    required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input
                    type="password"
                    name="password"
                    required>
            </div>

            <div class="form-group">
                <label>Role</label>

                <select name="role_id">

                    <option value="1">Admin</option>
                    <option value="2">Manager</option>
                    <option value="3">Staff</option>

                </select>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn-cancel"
                    id="btnCancelModal">
                    Hủy
                </button>

                <button
                    type="submit"
                    class="btn-submit">
                    Thêm
                </button>

            </div>

        </form>

    </div>

</div>