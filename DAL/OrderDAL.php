<?php
class OrderDAL {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getOrder() {
        $sql = 'SELECT o.*, c.customer_name, c.phone, c.address, u.full_name as staff_name 
                FROM orders as o
                LEFT JOIN customers as c ON c.customer_id = o.customer_id
                LEFT JOIN users as u ON u.user_id = o.user_id';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(); 
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrderById($order_id) {
        $sql = 'SELECT o.*, c.customer_name, c.phone, c.address, c.email as customer_email, u.full_name as staff_name 
                FROM orders as o
                LEFT JOIN customers as c ON c.customer_id = o.customer_id
                LEFT JOIN users as u ON u.user_id = o.user_id
                WHERE o.order_id = :order_id';
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['order_id' => $order_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateOrderStatus($order_id, $status) {
        $stmt = $this->pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        return $stmt->execute([$status, $order_id]);
    }
    
    public function getOrderDetail($order_id) {
        $sql = 'SELECT od.*, p.product_name, p.unit_price as current_price
                FROM order_details as od
                LEFT JOIN products as p ON p.product_id = od.product_id
                WHERE od.order_id = :order_id'; 
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['order_id' => $order_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createOrder($customer_id, $user_id, $status = 'Chờ xác nhận', $total_amount = 0) {
        $sql = "INSERT INTO orders (customer_id, user_id, status, total_amount, order_date) 
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$customer_id, $user_id, $status, $total_amount]);
        return $this->pdo->lastInsertId();
    }

    public function createOrderDetail($order_id, $product_id, $quantity, $unit_price) {
        $sql = "INSERT INTO order_details (order_id, product_id, quantity, unit_price) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$order_id, $product_id, $quantity, $unit_price]);
    }

    public function updateOrderTotal($order_id) {
        $sql = "UPDATE orders SET total_amount = (
                    SELECT SUM(quantity * unit_price) 
                    FROM order_details 
                    WHERE order_id = ?
                ) WHERE order_id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$order_id, $order_id]);
    }
    public function getTotalOrder(){
        $sql = "SELECT COUNT(*) FROM orders";
        $stmt =$this->pdo->query($sql); 
        return $stmt->fetchColumn();
    }
    public function getTotalRevenue() {
        $sql = "SELECT SUM(total_amount) FROM orders WHERE status = 'Hoàn thành'";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchColumn(); 
    }
   
}
?>