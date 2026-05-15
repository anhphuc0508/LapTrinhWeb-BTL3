<?php
require_once __DIR__ . '/../CONFIG/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../BLL/ProductBLL.php';
require_once __DIR__ . '/../BLL/OrderBLL.php';
require_once __DIR__ . '/../BLL/CategoryBLL.php';
require_once __DIR__ . '/../BLL/ProductBLL.php';

$productBll = new ProductBLL($pdo);
$topProducts = $productBll->getTopSellingProducts(5);
$ProductBLL = new ProductBLL($pdo);
$OrderBLL = new OrderBLL($pdo);
$CategoryBLL = new CategoryBLL($pdo);
$orderBll = new OrderBLL($pdo);

$search = $_GET['search'] ?? '';
$category_id = $_GET['category_id'] ?? '';

$totalOrder = $OrderBLL->getTotalOrder();
$totalProducts = $ProductBLL->getTotalProducts($search, $category_id);
$totalCategory = $CategoryBLL->getCategoryTotal();





$maxSold = isset($topProducts[0]) ? $topProducts[0]['total_sold'] : 1;
$totalRevenue = $orderBll->getTotalRevenue();
if ($totalRevenue >= 1000000000) {
    $revenueDisplay = str_replace('.', ',', round($totalRevenue / 1000000000, 2)) . ' tỷ';
} elseif ($totalRevenue >= 1000000) {
    $revenueDisplay = str_replace('.', ',', round($totalRevenue / 1000000, 2)) . ' triệu';
} else {
    $revenueDisplay = number_format($totalRevenue, 0, ',', '.') . ' đ';
}

$phoneCount = $productBll->countProductsByCategoryName('Điện thoại');
$phukienCount = $productBll->countProductsByCategoryName('Phụ kiện');
$laptopCount = $productBll->countProductsByCategoryName('Laptop');
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tổng Quan</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .stat-info h3 {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-main);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
        }

        .stat-icon.primary {
            background-color: var(--primary-color);
        }

        .stat-icon.success {
            background-color: var(--success-color);
        }

        .stat-icon.warning {
            background-color: #f59e0b;
        }

        .stat-icon.danger {
            background-color: var(--danger-color);
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-main);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--primary-color);
        }

        .products-container {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .product-row {
            display: grid;
            grid-template-columns: 60px 1fr 120px 120px 120px;
            gap: 20px;
            padding: 16px 24px;
            border-bottom: 1px solid var(--border-color);
            align-items: center;
            transition: background-color 0.3s ease;
        }

        .product-row:hover {
            background-color: var(--bg-main);
        }

        .product-row:last-child {
            border-bottom: none;
        }

        .product-image {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary-color), #a78bfa);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .product-name {
            font-weight: 600;
            color: var(--text-main);
        }

        .product-category {
            font-size: 12px;
            color: var(--text-muted);
        }

        .product-sales {
            text-align: center;
        }

        .product-sales .number {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-color);
        }

        .product-sales .label {
            font-size: 12px;
            color: var(--text-muted);
        }

        .product-revenue {
            text-align: center;
        }

        .product-revenue .amount {
            font-size: 16px;
            font-weight: 700;
            color: var(--success-color);
        }

        .progress-bar-small {
            width: 100%;
            height: 4px;
            background-color: var(--border-color);
            border-radius: 2px;
            margin-top: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background-color: var(--primary-color);
            border-radius: 2px;
        }

        .two-column {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        @media (max-width: 1024px) {
            .two-column {
                grid-template-columns: 1fr;
            }

            .product-row {
                grid-template-columns: 1fr;
                gap: 10px;
            }
        }

        .chart-placeholder {
            background: linear-gradient(135deg, var(--bg-main) 0%, var(--secondary-color) 100%);
            border-radius: var(--radius-lg);
            padding: 40px;
            text-align: center;
            color: var(--text-muted);
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-style: italic;
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
        }

        .category-card {
            background-color: var(--bg-main);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 16px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .category-card:hover {
            border-color: var(--primary-color);
            background-color: var(--bg-card);
            box-shadow: var(--shadow-md);
        }

        .category-icon {
            font-size: 32px;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .category-name {
            font-weight: 600;
            color: var(--text-main);
            font-size: 14px;
            margin-bottom: 8px;
        }

        .category-count {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-color);
        }
    </style>
</head>

<body>
    <div class="app-container">
        <?php require_once 'sidebar.php'; ?>

        <main class="main-content">
           
            <div class="topbar">
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" placeholder="Tìm kiếm...">
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
            </div>

            <div class="content-header">
                <div>
                    <h1><i class="fa-solid fa-chart-line"></i> Tổng Quan</h1>
                    <p>Cập nhật hôm nay - <?= date('d/m/Y') ?></p>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Tổng Sản Phẩm</h3>
                        <div class="stat-value"><?= $totalProducts ?></div>
                    </div>
                    <div class="stat-icon primary">
                        <i class="fa-solid fa-box"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3>TỔNG ĐƠN HÀNG</h3>
                        <div class="stat-value"><?= $totalOrder ?></div>
                    </div>
                    <div class="stat-icon success">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Doanh Thu</h3>
                        <div class="stat-value"><?= $revenueDisplay?> đ</div>
                    </div>
                    <div class="stat-icon warning">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-info">
                        <h3>Danh Mục</h3>
                        <div class="stat-value"><?php echo $totalCategory ?></div>
                    </div>
                    <div class="stat-icon danger">
                        <i class="fa-solid fa-tags"></i>
                    </div>
                </div>
            </div>

            <div class="two-column">
                <div>
                    <h2 class="section-title">
                        <i class="fa-solid fa-fire"></i> Sản Phẩm Bán Chạy
                    </h2>
                    <div class="products-container">

                        <?php if (count($topProducts) > 0): ?>
                            <?php foreach ($topProducts as $index => $top):
                                $rank = $index + 1;
                                $percent = round(($top['total_sold'] / $maxSold) * 100);

                                $revenueM = round($top['total_revenue'] / 1000000, 1) . 'M';

                                // Giả lập tỉ lệ tăng trưởng (Vì chưa có logic lưu số liệu tháng trước)
                                $trend = rand(5, 20);
                            ?>

                                <div class="product-row">
                                    <div class="product-image"><?= $rank ?></div>
                                    <div>
                                        <div class="product-name"><?= htmlspecialchars($top['product_name']) ?></div>
                                        <div class="product-category"><?= htmlspecialchars($top['category_name'] ?? 'Không phân loại') ?></div>
                                        <div class="progress-bar-small">
                                            <div class="progress-fill" style="width: <?= $percent ?>%;"></div>
                                        </div>
                                    </div>
                                    <div class="product-sales">
                                        <div class="number"><?= $top['total_sold'] ?></div>
                                        <div class="label">bán</div>
                                    </div>
                                    <div class="product-revenue">
                                        <div class="amount"><?= $revenueM ?></div>
                                    </div>
                                    <div style="text-align: right; color: var(--success-color); font-weight: 600;">
                                        ↑ <?= $trend ?>%
                                    </div>
                                </div>

                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="padding: 15px; color: var(--text-muted);">Chưa có dữ liệu bán hàng.</p>
                        <?php endif; ?>

                    </div>

                    
                    
            </div>

            
    </div>

    <!-- Categories Section -->
    <div>
        <h2 class="section-title">
            <i class="fa-solid fa-list"></i> Danh Mục Sản Phẩm
        </h2>
        <div class="category-grid">
            <div class="category-card">
                <div class="category-icon"><i class="fa-solid fa-laptop"></i></div>
                <div class="category-name">Laptop</div>
                <div class="category-count"><?= $laptopCount ?></div>
            </div>
            <div class="category-card">
                <div class="category-icon"><i class="fa-solid fa-mobile"></i></div>
                <div class="category-name">Điện Thoại</div>
                <div class="category-count"><?= $phoneCount ?></div>
            </div>
          
          
            <div class="category-card">
                <div class="category-icon"><i class="fa-solid fa-keyboard"></i></div>
                <div class="category-name">Phụ Kiện</div>
                <div class="category-count"><?= $phukienCount ?></div>
            </div>
                         
        </div>
    </div>

    
    </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>