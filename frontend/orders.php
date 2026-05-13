<?php
require_once __DIR__ . '/../CONFIG/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../BLL/OrderBLL.php';

$bll = new OrderBLL($pdo);
$orders = $bll->getOrder();

function statusSlug($status) {
    $map = [
        'Chờ xác nhận' => 'cho-xac-nhan',
        'Đang xử lý'   => 'dang-xu-ly',
        'Hoàn thành'   => 'hoan-thanh',
        'Đã hủy'       => 'da-huy',
    ];
    return $map[$status] ?? 'cho-xac-nhan';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Đơn hàng</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css?v=<?= time() ?>">
    <style>
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-cho-xac-nhan { background-color: #fff3cd; color: #856404; }
        .status-dang-xu-ly { background-color: #cce5ff; color: #004085; }
        .status-hoan-thanh { background-color: #d4edda; color: #155724; }
        .status-da-huy { background-color: #f8d7da; color: #721c24; }
        
        #orderDetailsTable th { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fa-solid fa-cube"></i> Trang Quản Lý</h2>
            </div>
            <ul class="nav-links">
                <li><a href="index.php"><i class="fa-solid fa-box"></i> Sản phẩm</a></li>
                <li><a href="categories.php"><i class="fa-solid fa-tags"></i> Danh mục</a></li>
                <li><a href="orders.php" class="active"><i class="fa-solid fa-cart-shopping"></i> Đơn hàng</a></li>
            </ul>
            <ul class="nav-links" style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
                <li><a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="topbar">
                <form class="search-bar" method="GET" action="orders.php">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" name="search" placeholder="Tìm kiếm đơn hàng...">
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
                    <h1>Quản lý Đơn hàng</h1>
                    <p>Theo dõi và xử lý các đơn đặt hàng từ khách hàng.</p>
                </div>
            </div>

            <div class="table-container table-responsive bg-white rounded shadow-sm p-3">
                <table class="table table-hover table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Mã ĐH</th>
                            <th>Khách hàng</th>
                            <th>Ngày đặt</th>
                            <th>Trạng thái</th>
                            <th>Tổng tiền</th>
                            <th>Nhân viên phụ trách</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($orders) > 0): ?>
                            <?php foreach ($orders as $o): ?>
                                <tr>
                                    <td><strong>#<?= $o['order_id'] ?></strong></td>
                                    <td><?= htmlspecialchars($o['customer_name'] ?? 'Khách lẻ') ?></td>
                                    <td><?= date('d/m/Y H:i', strtotime($o['order_date'])) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= statusSlug($o['status']) ?>">
                                            <?= htmlspecialchars($o['status']) ?>
                                        </span>
                                    </td>
                                    <td><strong class="text-danger"><?= number_format($o['total_amount'] ?? 0, 0, ',', '.') ?> đ</strong></td>
                                    <td><?= htmlspecialchars($o['staff_name'] ?? '-') ?></td>
                                    <td class="action-btns">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="viewOrderDetails(<?= $o['order_id'] ?>)" title="Xem chi tiết">
                                            <i class="fa-solid fa-eye"></i> Xem
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="text-center">Không có đơn hàng nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div class="modal fade" id="orderModal" tabindex="-1" aria-labelledby="orderModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="orderModalLabel">Chi tiết Đơn hàng <span id="modalOrderId" class="text-primary"></span></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
             <div id="orderInfo" class="mb-4 p-3 bg-light rounded border">
                 <div class="row">
                     <div class="col-md-6">
                         <p class="mb-1"><strong>Khách hàng:</strong> <span id="modalCustomerName"></span></p>
                         <p class="mb-1"><strong>Số điện thoại:</strong> <span id="modalCustomerPhone"></span></p>
                         <p class="mb-0"><strong>Địa chỉ:</strong> <span id="modalCustomerAddress"></span></p>
                     </div>
                     <div class="col-md-6">
                         <p class="mb-1"><strong>Ngày đặt:</strong> <span id="modalOrderDate"></span></p>
                         <p class="mb-1"><strong>Trạng thái:</strong> <span id="modalStatus"></span></p>
                         <p class="mb-0"><strong>Nhân viên:</strong> <span id="modalStaffName"></span></p>
                     </div>
                 </div>
             </div>
             <div class="table-responsive">
                 <table class="table table-bordered table-striped" id="orderDetailsTable">
                     <thead>
                         <tr>
                             <th>Mã SP</th>
                             <th>Tên Sản phẩm</th>
                             <th>Số lượng</th>
                             <th>Đơn giá</th>
                             <th>Thành tiền</th>
                         </tr>
                     </thead>
                     <tbody id="orderDetailsBody">
                     </tbody>
                 </table>
             </div>
             <div class="text-end mt-3">
                 <h4>Tổng cộng: <span id="modalTotalAmount" class="text-danger">0 đ</span></h4>
             </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let orderModal = null;

        document.addEventListener('DOMContentLoaded', function() {
            const modalElement = document.getElementById('orderModal');
            if (modalElement) {
                orderModal = new bootstrap.Modal(modalElement);
            }
        });

        function viewOrderDetails(orderId) {
            console.log('Viewing order:', orderId);
            
            if (!orderModal) {
                const modalElement = document.getElementById('orderModal');
                if (modalElement) {
                    orderModal = new bootstrap.Modal(modalElement);
                } else {
                    alert('Không tìm thấy modal chi tiết đơn hàng!');
                    return;
                }
            }

            document.getElementById('modalOrderId').innerText = '#' + orderId;
            document.getElementById('orderDetailsBody').innerHTML = '<tr><td colspan="5" class="text-center"><div class="spinner-border text-primary" role="status"></div> Đang tải...</td></tr>';
            
           
            document.getElementById('modalCustomerName').innerText = '...';
            document.getElementById('modalCustomerPhone').innerText = '...';
            document.getElementById('modalCustomerAddress').innerText = '...';
            document.getElementById('modalOrderDate').innerText = '...';
            document.getElementById('modalStatus').innerText = '...';
            document.getElementById('modalStaffName').innerText = '...';

      
            orderModal.show();

         
            const formData = new FormData();
            formData.append('action', 'get_details');
            formData.append('order_id', orderId);

            fetch('../API/OrderAPI.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(res => {
                console.log('API Response:', res);
                const tbody = document.getElementById('orderDetailsBody');
                tbody.innerHTML = '';
                
                if (res.status === 'success' && res.order) {
                    const o = res.order;
                    document.getElementById('modalCustomerName').innerText = o.customer_name || 'Khách lẻ';
                    document.getElementById('modalCustomerPhone').innerText = o.phone || '-';
                    document.getElementById('modalCustomerAddress').innerText = o.address || '-';
                    document.getElementById('modalOrderDate').innerText = o.order_date ? new Date(o.order_date).toLocaleString('vi-VN') : '-';
                    document.getElementById('modalStatus').innerText = o.status || '-';
                    document.getElementById('modalStaffName').innerText = o.staff_name || '-';

                    if (res.details && res.details.length > 0) {
                        let total = 0;
                        res.details.forEach(item => {
                            const subtotal = parseFloat(item.quantity) * parseFloat(item.unit_price);
                            total += subtotal;
                            
                            tbody.innerHTML += `
                                <tr>
                                    <td>#${item.product_id}</td>
                                    <td><strong>${item.product_name || 'Sản phẩm không xác định'}</strong></td>
                                    <td>${item.quantity}</td>
                                    <td>${new Intl.NumberFormat('vi-VN').format(item.unit_price)} đ</td>
                                    <td><strong class="text-danger">${new Intl.NumberFormat('vi-VN').format(subtotal)} đ</strong></td>
                                </tr>
                            `;
                        });
                        
                        document.getElementById('modalTotalAmount').innerText = new Intl.NumberFormat('vi-VN').format(total) + ' đ';
                    } else {
                        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Không tìm thấy chi tiết cho đơn hàng này.</td></tr>';
                        document.getElementById('modalTotalAmount').innerText = '0 đ';
                    }
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">Lỗi: ' + (res.message || 'Không tìm thấy dữ liệu') + '</td></tr>';
                }
            })
            .catch(err => {
                document.getElementById('orderDetailsBody').innerHTML = '<tr><td colspan="5" class="text-center text-danger">Lỗi khi tải dữ liệu!</td></tr>';
                console.error('Fetch error:', err);
            });
        }
    </script>
</body>
</html>
