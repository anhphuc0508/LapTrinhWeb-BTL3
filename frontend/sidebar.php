<?php
// Lấy tên file hiện tại đang mở trên trình duyệt (ví dụ: 'index.php', 'categories.php',...)
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <h2>
            <i class="fa-solid fa-cube"></i> Trang Quản Lý</h2>
    </div>
    <ul class="nav-links">
        <li>
            <a href="index.php" class="<?= ($current_page == 'index.php' || $current_page == 'product_form.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-box"></i> Sản phẩm
            </a>
        </li>
        <li>
            <a href="categories.php" class="<?= ($current_page == 'categories.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-tags"></i> Danh mục
            </a>
        </li>
        <li>
            <a href="orders.php" class="<?= ($current_page == 'orders.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-cart-shopping"></i> Đơn hàng
            </a>
        </li>
        <li>
            <a href="log.php" class="<?= ($current_page == 'log.php') ? 'active' : '' ?>">
                <i class="fa-solid fa-clock-rotate-left"></i> Lịch sử
            </a>
        </li>
    </ul>
    <ul class="nav-links" style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
        <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a></li>
    </ul>
</aside>