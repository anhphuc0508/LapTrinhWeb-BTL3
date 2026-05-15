<?php
class CustomerDAL {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getCustomers($search = '', $limit = 10, $offset = 0) {
        $sql = "SELECT * FROM customers WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            // Tìm kiếm theo tên hoặc số điện thoại
            $sql .= " AND (customer_name LIKE :search OR phone LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY customer_id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTotalCustomers($search = '') {
        $sql = "SELECT COUNT(*) FROM customers WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (customer_name LIKE :search OR phone LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }

    public function addCustomer($name, $phone, $email, $address) {
        $stmt = $this->pdo->prepare("
            INSERT INTO customers (customer_name, phone, email, address) 
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$name, $phone, $email, $address]);
    }

    public function updateCustomer($id, $name, $phone, $email, $address) {
        $stmt = $this->pdo->prepare("
            UPDATE customers 
            SET customer_name = ?, phone = ?, email = ?, address = ? 
            WHERE customer_id = ?
        ");
        return $stmt->execute([$name, $phone, $email, $address, $id]);
    }

    public function deleteCustomer($id) {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM orders WHERE customer_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Không cho xóa nếu đã mua hàng
        }

        $stmt = $this->pdo->prepare("DELETE FROM customers WHERE customer_id = ?");
        return $stmt->execute([$id]);
    }
}
?>