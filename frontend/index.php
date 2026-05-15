<?php
require_once __DIR__ . '/../CONFIG/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../BLL/ProductBLL.php';

$bll = new ProductBLL($pdo);

$search = $_GET['search'] ?? '';
$products = $bll->getProducts($search);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sản phẩm</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .alert { padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; color: white; font-weight: 500; }
        .alert-success { background-color: var(--success-color); }
        .alert-error { background-color: var(--danger-color); }
    </style>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fa-solid fa-cube"></i> Trang Quản Lý</h2>
            </div>
            <ul class="nav-links">
                <li><a href="index.php" class="active"><i class="fa-solid fa-box"></i> Sản phẩm</a></li>
                <li><a href="categories.php"><i class="fa-solid fa-tags"></i> Danh mục</a></li>
                <li><a href="orders.php"><i class="fa-solid fa-cart-shopping"></i> Đơn hàng</a></li>
            </ul>
            <ul class="nav-links" style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <form class="search-bar" method="GET" action="index.php">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Tìm kiếm sản phẩm theo tên...">
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
                    <h1>Quản lý Sản phẩm</h1>
                    <p>Quản lý kho hàng, thông tin sản phẩm và giá cả.</p>
                </div>
                <div class="header-actions">
                   
                    <a href="product_form.php" class="btn btn-primary" style="text-decoration: none;">
                        <i class="fa-solid fa-plus"></i> Thêm Sản Phẩm Mới
                    </a>
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
                <table class="table table-hover table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên Sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Nhà cung cấp</th>
                            <th>Giá Bán</th>
                            <th>Tồn kho</th>
                            <th>Ngày Tạo</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($products) > 0): ?>
                            <?php foreach ($products as $p): ?>
                                <tr>
                                    <td>#<?= $p['product_id'] ?></td>
                                    <td><strong style="cursor: pointer; color: #0066cc;" onclick="showVariants(<?= $p['product_id'] ?>, '<?= htmlspecialchars($p['product_name']) ?>')" data-bs-toggle="modal" data-bs-target="#variantModal" title="Click để xem biến thể"><?= htmlspecialchars($p['product_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($p['category_name'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($p['supplier_name'] ?? '-') ?></td>
                                    <td><strong class="text-danger"><?= number_format($p['unit_price'] ?? 0, 0, ',', '.') ?> đ</strong></td>
                                    <td><?= $p['stock_quantity'] ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></td>
                                    <td class="action-btns">
                                        <a href="product_form.php?product_id=<?= $p['product_id'] ?>" class="btn btn-icon"><i class="fa-solid fa-pen"></i></a>
                                        <form method="POST" action="../API/ProductAPI.php" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn nhân bản sản phẩm này?');">
                                            <input type="hidden" name="action" value="clone">
                                            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                                            <button type="submit" class="btn btn-warning text-white btn-sm" title="Nhân bản"><i class="fa-solid fa-copy"></i></button>
                                        </form>
                                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                        <form method="POST" action="../API/ProductAPI.php" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center">Không tìm thấy sản phẩm nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div class="modal fade" id="variantModal" tabindex="-1" aria-labelledby="variantModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="variantModalLabel">Biến Thể Sản Phẩm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="variantContent" style="max-height: 500px; overflow-y: auto;">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function showVariants(productId, productName) {
            const variantContent = document.getElementById('variantContent');
            const modalLabel = document.getElementById('variantModalLabel');
            
            modalLabel.textContent = `Biến Thể: ${productName}`;
            variantContent.innerHTML = '<div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Đang tải...</span></div></div>';
            
            fetch('../API/ProductAPI.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=getVar&product_id=' + productId
            })
            .then(response => response.json())
            .then(data => {
                if (Array.isArray(data) && data.length > 0) {
                    let html = '<div class="table-responsive"><table class="table table-sm table-hover table-bordered">';
                    html += '<thead><tr>';
                    html += '<th>ID</th><th>SKU</th><th>Tên biến thể</th><th>Giá bán</th><th>Tồn kho</th>';
                    html += '</tr></thead><tbody>';
                    
                    // Render các biến thể
                    data.forEach(variant => {
                        html += '<tr>';
                        html += `<td>#${variant.variant_id}</td>`;
                        html += `<td>${variant.sku || '-'}</td>`;
                        html += `<td>${variant.variant_name || '-'}</td>`;
                        html += `<td>${new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(variant.price || 0)}</td>`;
                        html += `<td>${variant.stock_quantity || 0}</td>`;
                        html += '</tr>';
                    });
                    
                    html += '</tbody></table></div>';
                    variantContent.innerHTML = html;
                } else {
                    variantContent.innerHTML = '<div class="alert alert-warning" role="alert">Không có biến thể nào cho sản phẩm này.</div>';
                }
            })
            .catch(error => {
                console.error('Lỗi:', error);
                variantContent.innerHTML = '<div class="alert alert-danger" role="alert">Lỗi khi tải biến thể!</div>';
            });
        }
    </script>
</body>
</html>
