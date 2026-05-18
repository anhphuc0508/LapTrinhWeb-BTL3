<?php
require_once __DIR__ . '/../CONFIG/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../BLL/CategoryBLL.php';
$bll = new CategoryBLL($pdo);
$categories = $bll->getCategories();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Danh mục</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .alert { padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; color: white; font-weight: 500; }
        .alert-success { background-color: var(--success-color); }
        .alert-error { background-color: var(--danger-color); }

        /* Category stat cards */
        .stat-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 28px;
        }
        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 22px 24px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: var(--shadow-sm);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
        }
        .stat-icon.purple { background: #ede9fe; color: #7c3aed; }
        .stat-icon.blue { background: #dbeafe; color: #2563eb; }
        .stat-icon.green { background: #d1fae5; color: #059669; }
        .stat-info h3 { font-size: 26px; font-weight: 700; margin-bottom: 2px; }
        .stat-info p { font-size: 13px; color: var(--text-muted); margin: 0; }

        .edit-row input, .edit-row textarea {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid var(--primary-color);
            border-radius: 6px;
            font-size: 14px;
            outline: none;
            transition: box-shadow 0.2s;
        }
        .edit-row input:focus, .edit-row textarea:focus {
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.15);
        }
        .edit-row textarea {
            resize: vertical;
            min-height: 40px;
        }

        .cat-name {
            font-weight: 600;
            color: var(--text-main);
        }
        .cat-desc {
            color: var(--text-muted);
            font-size: 13px;
            max-width: 400px;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-state i {
            font-size: 48px;
            color: #d1d5db;
            margin-bottom: 16px;
        }
        .empty-state h3 {
            font-size: 18px;
            color: var(--text-muted);
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fa-solid fa-cube"></i> Trang Quản Lý</h2>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
                <li><a href="index.php"><i class="fa-solid fa-box"></i> Sản phẩm</a></li>
                <li><a href="categories.php" class="active"><i class="fa-solid fa-tags"></i> Danh mục</a></li>
                <li><a href="orders.php"><i class="fa-solid fa-cart-shopping"></i> Đơn hàng</a></li>
            </ul>
            <ul class="nav-links" style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div class="search-bar">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Tìm kiếm danh mục..." oninput="filterCategories()">
                </div>
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

            <div class="stat-cards">
                <div class="stat-card">
                    <div class="stat-icon purple"><i class="fa-solid fa-tags"></i></div>
                    <div class="stat-info">
                        <h3 id="totalCategories"><?= count($categories) ?></h3>
                        <p>Tổng danh mục</p>
                    </div>
                </div>
            </div>

            <div class="content-header">
                <div class="title">
                    <h1>Quản lý Danh mục</h1>
                    <p>Thêm, sửa, xóa các danh mục sản phẩm.</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="fa-solid fa-plus"></i> Thêm Danh Mục Mới
                    </button>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <div class="table-container table-responsive bg-white rounded shadow-sm p-3">
                <table class="table table-hover table-bordered align-middle" id="categoryTable">
                    <thead>
                        <tr>
                            <th style="width: 80px;">ID</th>
                            <th>Tên danh mục</th>
                            <th>Mô tả</th>
                            <th style="width: 180px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody id="categoryTableBody">
                        <?php if (count($categories) > 0): ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr id="row-<?= $cat['category_id'] ?>" class="category-row">
                                    <td class="display-cell">#<?= $cat['category_id'] ?></td>
                                    <td class="display-cell">
                                        <span class="cat-name"><?= htmlspecialchars($cat['category_name']) ?></span>
                                    </td>
                                    <td class="display-cell">
                                        <span class="cat-desc"><?= htmlspecialchars($cat['description'] ?? '—') ?></span>
                                    </td>
                                    <td class="display-cell action-btns">
                                        <button class="btn btn-icon" title="Sửa" 
                                            onclick="startEdit(<?= $cat['category_id'] ?>, '<?= addslashes(htmlspecialchars($cat['category_name'])) ?>', '<?= addslashes(htmlspecialchars($cat['description'] ?? '')) ?>')">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                        <button class="btn btn-danger btn-sm" title="Xóa"
                                            onclick="deleteCategory(<?= $cat['category_id'] ?>, '<?= addslashes(htmlspecialchars($cat['category_name'])) ?>')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>

                                
                                    <td class="edit-cell" style="display:none;">#<?= $cat['category_id'] ?></td>
                                    <td class="edit-cell" style="display:none;">
                                        <input type="text" id="editName-<?= $cat['category_id'] ?>" value="<?= htmlspecialchars($cat['category_name']) ?>">
                                    </td>
                                    <td class="edit-cell" style="display:none;">
                                        <textarea id="editDesc-<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['description'] ?? '') ?></textarea>
                                    </td>
                                    <td class="edit-cell action-btns" style="display:none;">
                                        <button class="btn btn-primary btn-sm" onclick="saveEdit(<?= $cat['category_id'] ?>)">
                                            <i class="fa-solid fa-check"></i> Lưu
                                        </button>
                                        <button class="btn btn-secondary btn-sm" onclick="cancelEdit(<?= $cat['category_id'] ?>)">
                                            <i class="fa-solid fa-xmark"></i> Hủy
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr id="emptyRow">
                                <td colspan="4">
                                    <div class="empty-state">
                                        <i class="fa-solid fa-folder-open"></i>
                                        <h3>Chưa có danh mục nào</h3>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5><i class="fa-solid fa-plus" style="color: var(--primary-color); margin-right: 8px;"></i> Thêm danh mục mới</h5>
                <button class="close-btn" onclick="closeAddModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="addCategoryName">Tên danh mục <span style="color: var(--danger-color);">*</span></label>
                    <input type="text" id="addCategoryName" placeholder="Nhập tên danh mục...">
                </div>
                <div class="form-group">
                    <label for="addDescription">Mô tả</label>
                    <textarea id="addDescription" rows="3" placeholder="Mô tả ngắn gọn về danh mục..." style="resize: vertical;"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeAddModal()">Hủy</button>
                <button class="btn btn-primary" onclick="addCategory()">
                    <i class="fa-solid fa-plus"></i> Thêm danh mục
                </button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="deleteModal">
        <div class="modal-content" style="max-width: 450px;">
            <div class="modal-header">
                <h5><i class="fa-solid fa-triangle-exclamation" style="color: var(--danger-color); margin-right: 8px;"></i> Xác nhận xóa</h5>
                <button class="close-btn" onclick="closeDeleteModal()">&times;</button>
            </div>
            <div class="modal-body text-center">
                <p>Bạn có chắc chắn muốn xóa danh mục <strong id="deleteCatName"></strong>?</p>
                <p style="color: var(--text-muted); font-size: 13px; margin-top: 8px;">
                    <i class="fa-solid fa-circle-info"></i> Hành động này không thể hoàn tác. Các sản phẩm thuộc danh mục này sẽ mất liên kết.
                </p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeDeleteModal()">Hủy</button>
                <button class="btn btn-danger" id="confirmDeleteBtn" onclick="confirmDelete()">
                    <i class="fa-solid fa-trash"></i> Xóa
                </button>
            </div>
        </div>
    </div>

    
    <div class="toast" id="toast"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_URL = '../API/CategoryAPI.php';
        let deleteTargetId = null;

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + type + ' show';
            setTimeout(() => { toast.classList.remove('show'); }, 3000);
        }

        function openAddModal() {
            document.getElementById('addCategoryName').value = '';
            document.getElementById('addDescription').value = '';
            document.getElementById('addModal').classList.add('show');
            setTimeout(() => document.getElementById('addCategoryName').focus(), 300);
        }

        function closeAddModal() {
            document.getElementById('addModal').classList.remove('show');
        }

        async function addCategory() {
            const name = document.getElementById('addCategoryName').value.trim();
            const desc = document.getElementById('addDescription').value.trim();

            if (!name) {
                showToast('Vui lòng nhập tên danh mục!', 'error');
                document.getElementById('addCategoryName').focus();
                return;
            }

            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('category_name', name);
            formData.append('description', desc);

            try {
                const response = await fetch(API_URL, { method: 'POST', body: formData });
                const data = await response.json();

                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    closeAddModal();
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(data.message, 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối server!', 'error');
            }
        }

        function startEdit(id, name, desc) {
            document.querySelectorAll('.category-row').forEach(row => {
                row.querySelectorAll('.display-cell').forEach(c => c.style.display = '');
                row.querySelectorAll('.edit-cell').forEach(c => c.style.display = 'none');
            });

            const row = document.getElementById('row-' + id);
            row.querySelectorAll('.display-cell').forEach(c => c.style.display = 'none');
            row.querySelectorAll('.edit-cell').forEach(c => c.style.display = '');
            row.classList.add('edit-row');

            document.getElementById('editName-' + id).focus();
        }

        function cancelEdit(id) {
            const row = document.getElementById('row-' + id);
            row.querySelectorAll('.display-cell').forEach(c => c.style.display = '');
            row.querySelectorAll('.edit-cell').forEach(c => c.style.display = 'none');
            row.classList.remove('edit-row');
        }

        async function saveEdit(id) {
            const name = document.getElementById('editName-' + id).value.trim();
            const desc = document.getElementById('editDesc-' + id).value.trim();

            if (!name) {
                showToast('Tên danh mục không được để trống!', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('category_id', id);
            formData.append('category_name', name);
            formData.append('description', desc);

            try {
                const response = await fetch(API_URL, { method: 'POST', body: formData });
                const data = await response.json();

                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(data.message, 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối server!', 'error');
            }
        }

        function deleteCategory(id, name) {
            deleteTargetId = id;
            document.getElementById('deleteCatName').textContent = name;
            document.getElementById('deleteModal').classList.add('show');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('show');
            deleteTargetId = null;
        }

        async function confirmDelete() {
            if (!deleteTargetId) return;

            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('category_id', deleteTargetId);

            try {
                const response = await fetch(API_URL, { method: 'POST', body: formData });
                const data = await response.json();

                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    closeDeleteModal();
                    setTimeout(() => location.reload(), 800);
                } else {
                    showToast(data.message, 'error');
                }
            } catch (error) {
                showToast('Lỗi kết nối server!', 'error');
            }
        }

        function filterCategories() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const rows = document.querySelectorAll('.category-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.querySelector('.cat-name')?.textContent.toLowerCase() || '';
                const desc = row.querySelector('.cat-desc')?.textContent.toLowerCase() || '';
                const match = name.includes(query) || desc.includes(query);
                row.style.display = match ? '' : 'none';
                if (match) visibleCount++;
            });

            document.getElementById('totalCategories').textContent = visibleCount;
        }

        document.getElementById('addCategoryName').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') addCategory();
        });

        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) this.classList.remove('show');
            });
        });

        
    </script>
</body>
</html>
