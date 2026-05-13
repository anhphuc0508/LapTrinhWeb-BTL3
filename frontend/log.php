<?php
require_once __DIR__ . '/../CONFIG/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../BLL/LogBLL.php';
$logBll = new LogBLL($pdo);
$logs = $logBll->getLogs();

// Hàm hỗ trợ hiển thị màu sắc cho các loại hành động
function getActionBadge($action) {
    $class = 'bg-secondary';
    if ($action === 'ADD') $class = 'bg-success';
    elseif ($action === 'UPDATE') $class = 'bg-primary';
    elseif ($action === 'DELETE') $class = 'bg-danger';
    elseif ($action === 'CLONE') $class = 'bg-warning text-dark';
    
    return "<span class='badge $class'>" . htmlspecialchars($action) . "</span>";
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lịch sử hoạt động</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="app-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="topbar">
                <div class="search-bar"></div>
                <div class="user-profile">
                    <?php $fullname = !empty($_SESSION['full_name']) ? $_SESSION['full_name'] : ($_SESSION['username'] ?? 'Admin'); ?>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullname) ?>&background=0D8ABC&color=fff" alt="User">
                    <div>
                        <span><?= htmlspecialchars($fullname) ?></span><br>
                        <small style="text-transform: uppercase; font-size: 11px; font-weight: 600;">
                            <?= ($_SESSION['role'] ?? '') === 'admin' ? 'Quản trị viên' : 'Nhân viên' ?>
                        </small>
                    </div>
                </div>
            </header>

            <div class="content-header">
                <div class="title">
                    <h1>Lịch sử hoạt động</h1>
               
                </div>
            </div>

            <div class="table-container table-responsive bg-white rounded shadow-sm p-3">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Thời gian</th>
                            <th>Nhân viên</th>
                            <th>Hành động</th>
                            <th>Đối tượng</th>
                            <th>ID Đối tượng</th>
                            <th>Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($logs) > 0): ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td class="text-muted"><?= date('d/m/Y H:i:s', strtotime($log['created_at'])) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($log['full_name'] ?? $log['username']) ?></strong>
                                    </td>
                                    <td><?= getActionBadge($log['action_type']) ?></td>
                                    <td><span class="text-uppercase small fw-bold text-muted"><?= htmlspecialchars($log['entity_type']) ?></span></td>
                                    <td>#<?= htmlspecialchars($log['entity_id']) ?></td>
                                    <td><?= htmlspecialchars($log['description']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">Chưa có dữ liệu lịch sử nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>