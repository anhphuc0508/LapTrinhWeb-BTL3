<?php
require_once __DIR__ . '/../CONFIG/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Chỉ admin mới được xem trang này
if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../BLL/UserBLL.php';
$bll = new UserBLL($pdo);

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalUsers = $bll->getTotalUsers($search);
$totalPages = ceil($totalUsers / $limit);
$users = $bll->getUsers($search, $limit, $offset);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tài Khoản</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .avatar-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; background: linear-gradient(135deg, var(--primary-color), #818cf8); margin-right: 12px;}
        .badge-admin { background: #f59e0b; } .badge-employee { background: #10b981; }
    </style>
</head>
<body>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <form class="search-bar" method="GET" action="users.php">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm tên đăng nhập, email...">
                </form>
                <div class="user-profile">
                    <?php $fullname = !empty($_SESSION['full_name']) ? $_SESSION['full_name'] : ($_SESSION['username'] ?? 'Admin'); ?>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullname) ?>&background=0D8ABC&color=fff" alt="User">
                    <div>
                        <span><?= htmlspecialchars($fullname) ?></span><br>
                        <small style="color: <?= ($_SESSION['role'] ?? '') === 'admin' ? '#f59e0b' : '#10b981' ?>; font-weight: 600; font-size: 11px; text-transform: uppercase;">
                            <?= ($_SESSION['role'] ?? '') === 'admin' ? 'Quản trị viên' : 'Nhân viên' ?>
                        </small>
                    </div>
                </div>
            </header>

            <div class="content-header">
                <div class="title">
                    <h1>Quản lý Tài Khoản</h1>
                    <p>Quản lý quyền hạn và thông tin người dùng hệ thống.</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="openModal()">
                        <i class="fa-solid fa-user-plus"></i> Thêm Tài Khoản
                    </button>
                </div>
            </div>

            <div class="table-container table-responsive bg-white rounded shadow-sm p-3">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã TK</th>
                            <th>Tài Khoản</th>
                            <th>Họ Tên</th>
                            <th>Email</th>
                            <th>Quyền</th>
                            <th>Ngày Tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($users) > 0): ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><strong>#<?= $u['user_id'] ?></strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle"><?= mb_substr(htmlspecialchars($u['username']), 0, 1, "UTF-8") ?></div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($u['username']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($u['full_name'] ?? '—') ?></td>
                                    <td><i class="fa-solid fa-envelope me-1"></i> <?= htmlspecialchars($u['email'] ?? 'Chưa cập nhật') ?></td>
                                    <td>
                                        <span class="badge <?= ($u['role'] === 'admin') ? 'badge-admin' : 'badge-employee' ?>">
                                            <?= ($u['role'] === 'admin') ? 'Admin' : 'Nhân viên' ?>
                                        </span>
                                    </td>
                                    <td class="text-muted small"><?= date('d/m/Y', strtotime($u['created_at'] ?? 'now')) ?></td>
                                    <td class="action-btns">
                                        <button class="btn btn-icon" title="Sửa thông tin" onclick='editUser(<?= json_encode($u) ?>)'>
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button class="btn btn-icon" title="Đổi mật khẩu" onclick='changePassword(<?= $u["user_id"] ?>, "<?= htmlspecialchars(addslashes($u["username"])) ?>")'>
                                            <i class="fa-solid fa-key"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" title="Xóa" onclick="deleteUser(<?= $u['user_id'] ?>, '<?= htmlspecialchars(addslashes($u['username'])) ?>')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center py-4">Không tìm thấy tài khoản nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>">Trước</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>">Sau</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal Thêm/Sửa Tài Khoản -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm Tài Khoản</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm">
                        <input type="hidden" id="action" name="action" value="add">
                        <input type="hidden" id="user_id" name="user_id">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên đăng nhập <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3" id="passwordField">
                            <label class="form-label fw-bold">Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Họ tên</label>
                                <input type="text" class="form-control" id="full_name" name="fullname">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Quyền <span class="text-danger">*</span></label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="employee">Nhân viên</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="saveUser()">Lưu thông tin</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Đổi Mật Khẩu -->
    <div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Đổi Mật Khẩu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="passwordForm">
                        <input type="hidden" id="pwd_action" name="action" value="change_password">
                        <input type="hidden" id="pwd_user_id" name="user_id">
                        
                        <p class="text-muted mb-3">Đổi mật khẩu cho tài khoản: <strong id="pwd_username"></strong></p>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="new_password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="savePassword()">Cập nhật mật khẩu</button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let userModal = new bootstrap.Modal(document.getElementById('userModal'));
        let passwordModal = new bootstrap.Modal(document.getElementById('passwordModal'));

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + type + ' show';
            setTimeout(() => { toast.classList.remove('show'); }, 3000);
        }

        function openModal() {
            document.getElementById('userForm').reset();
            document.getElementById('action').value = 'add';
            document.getElementById('user_id').value = '';
            document.getElementById('username').disabled = false;
            document.getElementById('passwordField').style.display = 'block';
            document.getElementById('password').required = true;
            document.getElementById('modalTitle').innerText = 'Thêm Tài Khoản Mới';
            userModal.show();
        }

        function editUser(user) {
            document.getElementById('action').value = 'update';
            document.getElementById('user_id').value = user.user_id;
            document.getElementById('username').value = user.username;
            document.getElementById('username').disabled = true;
            document.getElementById('email').value = user.email;
            document.getElementById('full_name').value = user.full_name || '';
            document.getElementById('role').value = user.role;
            document.getElementById('passwordField').style.display = 'none';
            document.getElementById('password').required = false;
            document.getElementById('modalTitle').innerText = 'Cập nhật Tài Khoản';
            userModal.show();
        }

        function changePassword(userId, username) {
            document.getElementById('pwd_user_id').value = userId;
            document.getElementById('pwd_username').innerText = username;
            document.getElementById('passwordForm').reset();
            passwordModal.show();
        }

        function saveUser() {
            const form = document.getElementById('userForm');
            const formData = new FormData(form);

            fetch('../API/UserAPI.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        showToast(data.message, 'success');
                        userModal.hide();
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(() => showToast('Lỗi kết nối server!', 'error'));
        }

        function savePassword() {
            const password = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (password !== confirmPassword) {
                showToast('Mật khẩu xác nhận không khớp!', 'error');
                return;
            }

            if (password.length < 6) {
                showToast('Mật khẩu phải ít nhất 6 ký tự!', 'error');
                return;
            }

            const form = document.getElementById('passwordForm');
            const formData = new FormData(form);

            fetch('../API/UserAPI.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        showToast(data.message, 'success');
                        passwordModal.hide();
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(() => showToast('Lỗi kết nối server!', 'error'));
        }

        function deleteUser(id, username) {
            if (confirm(`Bạn có chắc chắn muốn xóa tài khoản "${username}" không?\nLưu ý: Phải giữ lại ít nhất 1 tài khoản admin.`)) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('user_id', id);

                fetch('../API/UserAPI.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            showToast(data.message, 'success');
                            setTimeout(() => location.reload(), 800);
                        } else {
                            showToast(data.message, 'error');
                        }
                    })
                    .catch(() => showToast('Lỗi kết nối server!', 'error'));
            }
        }
    </script>
</body>
</html>
