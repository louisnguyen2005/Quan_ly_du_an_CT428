<div id="permissionModal" class="modal">

    <div class="modal-content">

        <div class="modal-header">

            <h2>Chỉnh sửa quyền</h2>

            <span class="close-permission-modal">
                &times;
            </span>

        </div>

        <form
            method="POST"
            action="index.php?action=updateUserRole">

            <input
                type="hidden"
                name="user_id"
                id="editUserId">

            <div class="form-group">

                <label>Username</label>

                <input
                    type="text"
                    id="editUserName"
                    readonly>

            </div>

            <div class="form-group">

                <label>Email</label>

                <input
                    type="text"
                    id="editUserEmail"
                    readonly>

            </div>

            <div class="form-group">

                <label>Vai trò</label>

                <select
                    name="role_id"
                    id="editUserRole">

                    <option value="1">
                        Admin
                    </option>

                    <option value="2">
                        Manager
                    </option>

                    <option value="3">
                        Staff
                    </option>

                </select>

            </div>

            <button
                type="submit"
                class="btn-primary">

                Lưu thay đổi

            </button>

        </form>

    </div>

</div>