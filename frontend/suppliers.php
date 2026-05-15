<?php
require_once __DIR__ . '/../CONFIG/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../BLL/SupplierBLL.php';
$bll = new SupplierBLL($pdo);

$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalSuppliers = $bll->getTotalSuppliers($search);
$totalPages = ceil($totalSuppliers / $limit);
$suppliers = $bll->getSuppliers($search, $limit, $offset);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Nhà Cung Cấp</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .avatar-circle { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; background: linear-gradient(135deg, var(--primary-color), #818cf8); margin-right: 12px;}
    </style>
</head>
<body>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <form class="search-bar" method="GET" action="suppliers.php">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm tên hoặc SĐT nhà cung cấp...">
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
                    <h1>Quản lý Nhà Cung Cấp</h1>
                    <p>Lưu trữ và quản lý thông tin nhà cung cấp.</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-primary" onclick="openModal()">
                        <i class="fa-solid fa-plus"></i> Thêm Nhà Cung Cấp
                    </button>
                </div>
            </div>

            <div class="table-container table-responsive bg-white rounded shadow-sm p-3">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Mã NCC</th>
                            <th>Nhà Cung Cấp</th>
                            <th>Liên hệ</th>
                            <th>Địa chỉ</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($suppliers) > 0): ?>
                            <?php foreach ($suppliers as $s): ?>
                                <tr>
                                    <td><strong>#<?= $s['supplier_id'] ?></strong></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle"><?= mb_substr(htmlspecialchars($s['supplier_name']), 0, 1, "UTF-8") ?></div>
                                            <div>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($s['supplier_name']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                  
                                    <td>
                                        <div class="text-muted small"><i class="fa-solid fa-phone me-1"></i> <?= htmlspecialchars($s['phone'] ?? 'Chưa cập nhật') ?></div>
                                        <div class="text-muted small"><i class="fa-solid fa-envelope me-1"></i> <?= htmlspecialchars($s['email'] ?? 'Chưa cập nhật') ?></div>
                                    </td>
                                    <td style="max-width: 250px;" class="text-truncate" title="<?= htmlspecialchars($s['address'] ?? '') ?>">
                                        <?= htmlspecialchars($s['address'] ?? '—') ?>
                                    </td>
                                    <td style="max-width: 250px;" class="text-truncate" title="<?= htmlspecialchars($s['address'] ?? '') ?>">
                                        <?= htmlspecialchars($s['address'] ?? '—') ?>
                                    </td>
                                    <td class="action-btns">
                                        <button class="btn btn-icon" title="Sửa thông tin" onclick='editSupplier(<?= json_encode($s) ?>)'>
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                        <button class="btn btn-danger btn-sm" title="Xóa" onclick="deleteSupplier(<?= $s['supplier_id'] ?>, '<?= htmlspecialchars(addslashes($s['supplier_name'])) ?>')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4">Không tìm thấy nhà cung cấp nào.</td></tr>
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

    <div class="modal fade" id="supplierModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Thêm Nhà Cung Cấp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="supplierForm">
                        <input type="hidden" id="action" name="action" value="add">
                        <input type="hidden" id="supplier_id" name="supplier_id">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên nhà cung cấp <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="supplier_name" name="supplier_name" required>
                        </div>
                       
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <input type="text" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Địa chỉ</label>
                            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" onclick="saveSupplier()">Lưu thông tin</button>
                </div>
            </div>
        </div>
    </div>

    <div class="toast" id="toast"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let myModal = new bootstrap.Modal(document.getElementById('supplierModal'));

        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = 'toast ' + type + ' show';
            setTimeout(() => { toast.classList.remove('show'); }, 3000);
        }

        function openModal() {
            document.getElementById('supplierForm').reset();
            document.getElementById('action').value = 'add';
            document.getElementById('supplier_id').value = '';
            document.getElementById('modalTitle').innerText = 'Thêm Nhà Cung Cấp Mới';
            myModal.show();
        }

        function editSupplier(supplier) {
            document.getElementById('action').value = 'update';
            document.getElementById('supplier_id').value = supplier.supplier_id;
            document.getElementById('supplier_name').value = supplier.supplier_name;
        
            document.getElementById('phone').value = supplier.phone || '';
            document.getElementById('email').value = supplier.email || '';
            document.getElementById('address').value = supplier.address || '';
            document.getElementById('modalTitle').innerText = 'Cập nhật Nhà Cung Cấp';
            myModal.show();
        }

        function saveSupplier() {
            const form = document.getElementById('supplierForm');
            const formData = new FormData(form);

            fetch('../API/SupplierAPI.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        showToast(data.message, 'success');
                        myModal.hide();
                        setTimeout(() => location.reload(), 800);
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(() => showToast('Lỗi kết nối server!', 'error'));
        }

        function deleteSupplier(id, name) {
            if (confirm(`Bạn có chắc chắn muốn xóa nhà cung cấp "${name}" không?\nLưu ý: Không thể xóa nếu nhà cung cấp này có sản phẩm liên kết.`)) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('supplier_id', id);

                fetch('../API/SupplierAPI.php', { method: 'POST', body: formData })
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
