<?php
require_once __DIR__ . '/../CONFIG/db.php';
require_once __DIR__ . '/../BLL/UserBLL.php';
require_once __DIR__ . '/../BLL/LogBLL.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bll = new UserBLL($pdo);
    $logBll = new LogBLL($pdo);
    $user_id = $_SESSION['user_id'] ?? null;

    try {
        if ($action == 'add') {
            if (empty($_POST['username']) || empty($_POST['email']) || empty($_POST['password'])) {
                echo json_encode(["status" => "error", "message" => "Tên đăng nhập, email và mật khẩu không được để trống"]);
                exit;
            }
            if ($bll->addUser($_POST)) {
                $logBll->addLog($user_id, 'ADD', 'User', null, "Thêm tài khoản mới: " . $_POST['username']);
                echo json_encode(["status" => "success", "message" => "Thêm tài khoản thành công!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Thêm thất bại"]);
            }
        } 
        elseif ($action === 'update') {
            if (empty($_POST['user_id'])) {
                echo json_encode(["status" => "error", "message" => "Thiếu ID tài khoản"]);
                exit;
            }
            if ($bll->updateUser($_POST)) {
                $logBll->addLog($user_id, 'UPDATE', 'User', $_POST['user_id'], "Cập nhật thông tin tài khoản: " . $_POST['user_id']);
                echo json_encode(["status" => "success", "message" => "Cập nhật thành công!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Cập nhật thất bại"]);
            }
        }
        elseif ($action === 'change_password') {
            if (empty($_POST['user_id']) || empty($_POST['password'])) {
                echo json_encode(["status" => "error", "message" => "Thiếu thông tin bắt buộc"]);
                exit;
            }
            if ($bll->updatePassword($_POST['user_id'], $_POST['password'])) {
                $logBll->addLog($user_id, 'UPDATE', 'User', $_POST['user_id'], "Đổi mật khẩu tài khoản");
                echo json_encode(["status" => "success", "message" => "Đổi mật khẩu thành công!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Đổi mật khẩu thất bại"]);
            }
        }
        elseif ($action === 'delete') {
            if (empty($_POST['user_id'])) {
                echo json_encode(["status" => "error", "message" => "Thiếu ID tài khoản"]);
                exit;
            }
            if ($bll->deleteUser($_POST['user_id'])) {
                $logBll->addLog($user_id, 'DELETE', 'User', $_POST['user_id'], "Xóa tài khoản ID: " . $_POST['user_id']);
                echo json_encode(["status" => "success", "message" => "Xóa tài khoản thành công!"]);
            } else {
                echo json_encode(["status" => "error", "message" => "KHÔNG THỂ XÓA: Phải giữ lại ít nhất 1 tài khoản admin!"]);
            }
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "Lỗi hệ thống: " . $e->getMessage()]);
    }
}
?>
