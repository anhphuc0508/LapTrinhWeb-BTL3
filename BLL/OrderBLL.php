<?php
require_once __DIR__ . '/../DAL/OrderDAL.php';

class OrderBLL {
    private $dal;
    private $pdo;
    
    public function __construct($pdo){
        $this->dal = new OrderDAL($pdo);
        $this->pdo = $pdo;
    }
    
    public function getOrder(){
        return $this->dal->getOrder();
    }
    
    public function getOrderDetail($order_id) {
        return $this->dal->getOrderDetail($order_id);
    }

    public function getOrderById($order_id) {
        return $this->dal->getOrderById($order_id);
    }

    public function createOrder($data, $details) {
        try {
            $this->pdo->beginTransaction();

            $order_id = $this->dal->createOrder(
                $data['customer_id'] ?? null,
                $data['user_id'] ?? null,
                $data['status'] ?? 'Chờ xác nhận',
                
            );

            $total = 0;
            foreach ($details as $item) {
                $this->dal->createOrderDetail(
                    $order_id,
                    $item['product_id'],
                    $item['quantity'],
                    $item['unit_price']
                );
                $total += $item['quantity'] * $item['unit_price'];
            }

            $this->dal->updateOrderTotal($order_id);

            $this->pdo->commit();
            return $order_id;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    }
    ?>