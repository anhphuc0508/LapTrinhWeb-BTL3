<?php
require_once __DIR__ . '/../CONFIG/db.php';
require_once __DIR__ . '/../BLL/ProductBLL.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bll = new ProductBLL($pdo);

    try {
        if ($action === 'add' || $action === 'update') {
            $data = $_POST;
            $data['user_id'] = $_SESSION['user_id'] ?? null;

            if ($action === 'add') {
                $new_id = $bll->addProduct($data);
                echo json_encode(['status' => 'success', 'message' => 'Thêm sản phẩm thành công!', 'new_product_id' => $new_id]);
            } else {
                $bll->updateProduct($data);
                echo json_encode(['status' => 'success', 'message' => 'Cập nhật sản phẩm thành công!']);
            }
            exit;
        }
        elseif ($action === 'delete') {
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                $bll->deleteProduct($_POST['product_id']);
                echo json_encode(['status' => 'success', 'message' => 'Xóa sản phẩm thành công!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền xóa sản phẩm!']);
            }
            exit;
        }
        elseif ($action === 'clone') {
            if ($bll->cloneProduct($_POST['product_id'])) {
                echo json_encode(['status' => 'success', 'message' => 'Nhân bản sản phẩm thành công!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Nhân bản sản phẩm thất bại!']);
            }
            exit;
        }
        elseif($action === 'getVar'){
            $id = $_POST['product_id'] ?? null; 
            $data = $bll->getProductVariants($id); 
            
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'data' => $data]);
            exit;
        }
        elseif($action === 'addVariant'){
            try {
                if (!isset($_POST['product_id'])) {
                    throw new Exception("Thiếu product_id!");
                }
                $result = $bll->addVariant($_POST);
                if ($result) {
                    echo json_encode(['status' => 'success', 'message' => 'Thêm biến thể thành công!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Thêm biến thể thất bại!']);
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()]);
            }
            exit;
        }
        elseif($action === 'updateVariant'){
            try {
                if (!isset($_POST['variant_id']) || !isset($_POST['product_id'])) {
                    throw new Exception("Thiếu variant_id hoặc product_id!");
                }
                $result = $bll->updateVariant($_POST);
                if ($result) {
                    echo json_encode(['status' => 'success', 'message' => 'Cập nhật biến thể thành công!']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Cập nhật biến thể thất bại!']);
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => 'Lỗi: ' . $e->getMessage()]);
            }
            exit;
        }
        elseif($action === 'deleteVariant'){
            $variant_id = $_POST['variant_id'] ?? null;
            $bll->deleteVariant($variant_id);
            echo json_encode(['status' => 'success', 'message' => 'Xóa biến thể thành công!']);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi xử lý: ' . $e->getMessage()]);
    }

} else {
    header('Location: ../frontend/index.php');
    exit;
}
?>
