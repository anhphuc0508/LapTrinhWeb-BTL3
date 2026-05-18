<?php
require_once __DIR__ . '/../CONFIG/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../BLL/ProductBLL.php';

$bll = new ProductBLL($pdo);
$topProducts = $bll->getTopSellingProducts(10);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tổng quan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fa-solid fa-cube"></i> Trang Quản Lý</h2>
            </div>
            <ul class="nav-links">
                <li><a href="dashboard.php" class="active"><i class="fa-solid fa-chart-pie"></i> Tổng quan</a></li>
                <li><a href="index.php"><i class="fa-solid fa-box"></i> Sản phẩm</a></li>
                <li><a href="categories.php"><i class="fa-solid fa-tags"></i> Danh mục</a></li>
                <li><a href="orders.php"><i class="fa-solid fa-cart-shopping"></i> Đơn hàng</a></li>
            </ul>
            <ul class="nav-links" style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <div></div>
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
                    <h1>Tổng quan</h1>
                    <p>Thống kê và báo cáo hoạt động kinh doanh.</p>
                </div>
            </div>

            <div class="table-container table-responsive bg-white rounded shadow-sm p-3 mt-4">
                <h4 class="mb-4 text-primary"><i class="fa-solid fa-fire"></i> Top 10 Sản Phẩm Bán Chạy Nhất</h4>
                <table class="table table-hover table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên Sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Giá Bán</th>
                            <th>Tồn kho</th>
                            <th>Đã Bán</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($topProducts) > 0): ?>
                            <?php foreach ($topProducts as $index => $p): ?>
                                <tr>
                                    <td>#<?= $p['product_id'] ?></td>
                                    <td><strong><?= htmlspecialchars($p['product_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($p['category_name'] ?? '-') ?></td>
                                    <td><strong class="text-danger"><?= number_format($p['unit_price'] ?? 0, 0, ',', '.') ?> đ</strong></td>
                                    <td><?= $p['stock_quantity'] ?></td>
                                    <td>
                                        <span class="badge bg-success" style="font-size: 14px; padding: 6px 12px;">
                                            <?= $p['total_sold'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">Chưa có dữ liệu bán hàng.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
