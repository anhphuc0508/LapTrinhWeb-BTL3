<?php
require_once __DIR__ . '/../CONFIG/db.php';
require_once __DIR__ . '/../BLL/OrderBLL.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $action = $_POST['action'] ?? '';
    $bll = new OrderBLL($pdo);
    
    try {
        if ($action === 'get_details') {
            $order_id = $_POST['order_id'] ?? null;
            if ($order_id) {
                $order = $bll->getOrderById($order_id);
                $details = $bll->getOrderDetail($order_id);
                
                if ($order) {
                    echo json_encode(['status' => 'success', 'order' => $order, 'details' => $details]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Order not found']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Missing order ID']);
            }
            exit;
        }
        elseif ($action === 'update_status') {
            $order_id = $_POST['order_id'] ?? null;
            $status = $_POST['status'] ?? null;
            if ($order_id && $status) {
                $result = $bll->updateOrderStatus($order_id, $status);
                if ($result) {
                    echo json_encode(['status' => 'success', 'message' => 'Cập nhật trạng thái thành công']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Cập nhật trạng thái thất bại']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Thiếu thông tin đơn hàng hoặc trạng thái']);
            }
            exit;
        }
        else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            exit;
        }
        
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    $bll = new OrderBLL($pdo);
    
    if ($action === 'get_orders') {
        try {
            $orders = $bll->getOrder();
            echo json_encode(['status' => 'success', 'data' => $orders]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
        exit;
    }
}
?>