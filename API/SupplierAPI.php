<?php
require_once __DIR__ . '/../CONFIG/db.php';
require_once __DIR__ . '/../BLL/SupplierBLL.php';
require_once __DIR__ . '/../BLL/LogBLL.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bll = new SupplierBLL($pdo);
    $logBll = new LogBLL($pdo);
    $user_id = $_SESSION['user_id'] ?? null;

    try {
        if ($action == 'add') {
            if (empty($_POST['supplier_name'])) {
                echo json_encode(["status" => "error", "message" => "Tên nhà cung cấp không được để trống"]);
                exit;
            }
            if ($bll->addSupplier($_POST)) {
                $logBll->addLog($user_id, 'ADD', 'Supplier', null, "Thêm nhà cung cấp mới: " . $_POST['supplier_name']);
                echo json_encode(["status" => "success", "message" => "Thêm nhà cung cấp thành công!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Thêm thất bại"]);
            }
        } 
        elseif ($action === 'update') {
            if (empty($_POST['supplier_id']) || empty($_POST['supplier_name'])) {
                echo json_encode(["status" => "error", "message" => "Thiếu thông tin bắt buộc"]);
                exit;
            }
            if ($bll->updateSupplier($_POST)) {
                $logBll->addLog($user_id, 'UPDATE', 'Supplier', $_POST['supplier_id'], "Cập nhật thông tin nhà cung cấp: " . $_POST['supplier_name']);
                echo json_encode(["status" => "success", "message" => "Cập nhật thành công!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Cập nhật thất bại"]);
            }
        } 
        elseif ($action === 'delete') {
            if (empty($_POST['supplier_id'])) {
                echo json_encode(["status" => "error", "message" => "Thiếu ID nhà cung cấp"]);
                exit;
            }
            if ($bll->deleteSupplier($_POST['supplier_id'])) {
                $logBll->addLog($user_id, 'DELETE', 'Supplier', $_POST['supplier_id'], "Xóa nhà cung cấp ID: " . $_POST['supplier_id']);
                echo json_encode(["status" => "success", "message" => "Xóa thành công!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "KHÔNG THỂ XÓA: Nhà cung cấp này có sản phẩm liên kết!"]);
            }
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Lỗi hệ thống: " . $e->getMessage()]);
    }
}
?>
