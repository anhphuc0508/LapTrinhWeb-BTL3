<?php
require_once __DIR__ . '/../CONFIG/db.php';
require_once __DIR__ . '/../BLL/CategoryBLL.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bll = new CategoryBLL($pdo);

    try {
        if ($action === 'getCategory') {
            $data = $bll->getCategories();
            if ($data !== false) {
                header('Content-Type: application/json');
                echo json_encode($data);
                exit;
            } else {
                echo "Lỗi: Không lấy được dữ liệu";
            }
        } elseif ($action == 'add') {
            if (isset($_POST['category_name']) && isset($_POST['description'])) {
                $result = $bll->addCategories($_POST);
                if ($result) {
                    echo json_encode(["status" => "success", "message" => "Thêm thành công!"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Không thể thêm dữ liệu"]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Thiếu thông tin"]);
            }
            exit;
        } elseif ($action === 'delete') {
            if (isset($_POST['category_id'])) {
                $result = $bll->deleteCategory($_POST['category_id']);
                if ($result) {
                    echo json_encode(["status" => "success", "message" => "Xóa thành công!"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Xóa thất bại"]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Thiếu ID danh mục"]);
            }
            exit;
        }
        elseif ($action === 'update') {
            if (isset($_POST['category_id'], $_POST['category_name'], $_POST['description'])) {
                $result = $bll->updateCategory($_POST);
                if ($result) {
                    echo json_encode(["status" => "success", "message" => "Cập nhật thành công!"]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Cập nhật thất bại"]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Thiếu dữ liệu để cập nhật"]);
            }
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Lỗi hệ thống: " . $e->getMessage();
    }
}
