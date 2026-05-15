<?php
require_once __DIR__ . '/../CONFIG/db.php';
require_once __DIR__ . '/../BLL/CustomerBLL.php';
require_once __DIR__ . '/../BLL/LogBLL.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bll = new CustomerBLL($pdo);
    $logBll = new LogBLL($pdo);
    $user_id = $_SESSION['user_id'] ?? null;

    try {
        if ($action == 'add') {
            if (empty($_POST['customer_name'])) {
                echo json_encode(["status" => "error", "message" => "Tên khách hàng không được để trống"]);
                exit;
            }
            if ($bll->addCustomer($_POST)) {
                $logBll->addLog($user_id, 'ADD', 'Customer', null, "Thêm khách hàng mới: " . $_POST['customer_name']);
                echo json_encode(["status" => "success", "message" => "Thêm khách hàng thành công!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Thêm thất bại"]);
            }
        } 
        elseif ($action === 'update') {
            if (empty($_POST['customer_id']) || empty($_POST['customer_name'])) {
                echo json_encode(["status" => "error", "message" => "Thiếu thông tin bắt buộc"]);
                exit;
            }
            if ($bll->updateCustomer($_POST)) {
                $logBll->addLog($user_id, 'UPDATE', 'Customer', $_POST['customer_id'], "Cập nhật thông tin khách: " . $_POST['customer_name']);
                echo json_encode(["status" => "success", "message" => "Cập nhật thành công!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Cập nhật thất bại"]);
            }
        } 
        elseif ($action === 'delete') {
            if (empty($_POST['customer_id'])) {
                echo json_encode(["status" => "error", "message" => "Thiếu ID khách hàng"]);
                exit;
            }
            if ($bll->deleteCustomer($_POST['customer_id'])) {
                $logBll->addLog($user_id, 'DELETE', 'Customer', $_POST['customer_id'], "Xóa khách hàng ID: " . $_POST['customer_id']);
                echo json_encode(["status" => "success", "message" => "Xóa thành công!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "KHÔNG THỂ XÓA: Khách hàng này đã phát sinh đơn mua hàng!"]);
            }
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Lỗi hệ thống: " . $e->getMessage()]);
    }
}
?>