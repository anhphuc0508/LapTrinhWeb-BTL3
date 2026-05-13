<?php
require_once __DIR__ . '/../CONFIG/db.php';
require_once __DIR__ . '/../BLL/ProductBLL.php';
require_once __DIR__ . '/../BLL/LogBLL.php'; // 1. Gọi thêm file LogBLL

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bll = new ProductBLL($pdo);
    $logBll = new LogBLL($pdo); // 2. Khởi tạo LogBLL
    $user_id = $_SESSION['user_id'] ?? null; 

    try {
        if ($action === 'add' || $action === 'update') {
            $data = $_POST;
            $data['user_id'] = $user_id;

            if ($action === 'add') {
                $product_id = $bll->addProduct($data); 
                if ($product_id) {
                    $_SESSION['success'] = "Thêm sản phẩm thành công!";
                    $logBll->addLog($user_id, 'ADD', 'Product', $product_id, "Thêm sản phẩm: " . $data['product_name']);
                }
            } else {
                $bll->updateProduct($data);
                $_SESSION['success'] = "Cập nhật sản phẩm thành công!";
                $logBll->addLog($user_id, 'UPDATE', 'Product', $data['product_id'], "Cập nhật sản phẩm: " . $data['product_name']);
            }
        }
        elseif ($action === 'delete') {
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                $product_id = $_POST['product_id'];
                $bll->deleteProduct($product_id);
                $_SESSION['success'] = "Xóa sản phẩm thành công!";
                $logBll->addLog($user_id, 'DELETE', 'Product', $product_id, "Xóa sản phẩm ID: " . $product_id);
            } else {
                $_SESSION['error'] = "Bạn không có quyền xóa sản phẩm!";
            }
        }
        elseif ($action === 'clone') {
            if ($bll->cloneProduct($_POST['product_id'])) {
                $_SESSION['success'] = "Nhân bản sản phẩm thành công!";
                $logBll->addLog($user_id, 'CLONE', 'Product', $_POST['product_id'], "Nhân bản từ sản phẩm ID: " . $_POST['product_id']);
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
                    $logBll->addLog($user_id, 'ADD', 'Variant', $_POST['product_id'], "Thêm biến thể mới cho sản phẩm ID: " . $_POST['product_id']);
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
                    $logBll->addLog($user_id, 'UPDATE', 'Variant', $_POST['variant_id'], "Cập nhật biến thể có mã SKU/Tên: " . ($_POST['variant_name'] ?? 'Không tên'));
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
            $logBll->addLog($user_id, 'DELETE', 'Variant', $variant_id, "Xóa biến thể ID: " . $variant_id . " của sản phẩm ID: " . $product_id);
            
            header('Location: ../frontend/product_form.php?product_id=' . $product_id);
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Lỗi xử lý: " . $e->getMessage();
    }

    header('Location: ../frontend/index.php');
    exit;
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'export') {
    $bll = new ProductBLL($pdo);
    $products = $bll->getProducts();
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=products_' . date('Ymd_His') . '.csv');
    
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Tên sản phẩm', 'Danh mục', 'Nhà cung cấp', 'Giá bán (VNĐ)', 'Tồn kho', 'Ngày tạo']);
    
    foreach ($products as $p) {
        fputcsv($output, [
            $p['product_id'],
            $p['product_name'],
            $p['category_name'] ?? '',
            $p['supplier_name'] ?? '',
            $p['unit_price'],
            $p['stock_quantity'],
            $p['created_at']
        ]);
    }
    fclose($output);
    exit;
} else {
    header('Location: ../frontend/index.php');
    exit;
}
?>