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
                $bll->addProduct($data);
                $_SESSION['success'] = "Thêm sản phẩm thành công!";
            } else {
                $bll->updateProduct($data);
                $_SESSION['success'] = "Cập nhật sản phẩm thành công!";
            }
        }
        elseif ($action === 'delete') {
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                $bll->deleteProduct($_POST['product_id']);
                $_SESSION['success'] = "Xóa sản phẩm thành công!";
            } else {
                $_SESSION['error'] = "Bạn không có quyền xóa sản phẩm!";
            }
        }
        elseif ($action === 'clone') {
            if ($bll->cloneProduct($_POST['product_id'])) {
                $_SESSION['success'] = "Nhân bản sản phẩm thành công!";
            } else {
                $_SESSION['error'] = "Nhân bản sản phẩm thất bại!";
            }
        }
        elseif($action === 'getVar'){
            $id = $_POST['product_id'] ?? null; 
            $data = $bll->getProductVariants($id); 
            
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        }
        elseif($action === 'addVariant'){
            try {
                if (!isset($_POST['product_id'])) {
                    throw new Exception("Thiếu product_id!");
                }
                $result = $bll->addVariant($_POST);
                if ($result) {
                    $_SESSION['success'] = "Thêm biến thể thành công!";
                } else {
                    $_SESSION['error'] = "Thêm biến thể thất bại!";
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Lỗi: " . $e->getMessage();
            }
            header('Location: ../frontend/product_form.php?product_id=' . $_POST['product_id']);
            exit;
        }
        elseif($action === 'updateVariant'){
            try {
                if (!isset($_POST['variant_id']) || !isset($_POST['product_id'])) {
                    throw new Exception("Thiếu variant_id hoặc product_id!");
                }
                $result = $bll->updateVariant($_POST);
                if ($result) {
                    $_SESSION['success'] = "Cập nhật biến thể thành công!";
                } else {
                    $_SESSION['error'] = "Cập nhật biến thể thất bại!";
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Lỗi: " . $e->getMessage();
            }
            header('Location: ../frontend/product_form.php?product_id=' . $_POST['product_id']);
            exit;
        }
        elseif($action === 'deleteVariant'){
            $variant_id = $_POST['variant_id'] ?? null;
            $product_id = $_POST['product_id'] ?? null;
            $bll->deleteVariant($variant_id);
            $_SESSION['success'] = "Xóa biến thể thành công!";
            header('Location: ../frontend/product_form.php?product_id=' . $product_id);
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Lỗi xử lý: " . $e->getMessage();
    }

} else {
    header('Location: ../frontend/index.php');
    exit;
}
?>
