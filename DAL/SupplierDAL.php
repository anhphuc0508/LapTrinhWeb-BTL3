<?php
class SupplierDAL {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getSuppliers($search = '', $limit = 10, $offset = 0) {
        $sql = "SELECT * FROM suppliers WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            // Tìm kiếm theo tên nhà cung cấp hoặc số điện thoại
            $sql .= " AND (supplier_name LIKE :search OR phone LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY supplier_id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTotalSuppliers($search = '') {
        $sql = "SELECT COUNT(*) FROM suppliers WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (supplier_name LIKE :search OR phone LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }

    public function addSupplier($name, $contact_person, $phone, $email, $address, $tax_code) {
        $stmt = $this->pdo->prepare("
            INSERT INTO suppliers (supplier_name, contact_person, phone, email, address, tax_code) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$name, $contact_person, $phone, $email, $address, $tax_code]);
    }

    public function updateSupplier($id, $name, $contact_person, $phone, $email, $address, $tax_code) {
        $stmt = $this->pdo->prepare("
            UPDATE suppliers 
            SET supplier_name = ?, contact_person = ?, phone = ?, email = ?, address = ?, tax_code = ? 
            WHERE supplier_id = ?
        ");
        return $stmt->execute([$name, $contact_person, $phone, $email, $address, $tax_code, $id]);
    }

    public function deleteSupplier($id) {
        // Kiểm tra xem nhà cung cấp này có sản phẩm không
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM products WHERE supplier_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Không cho xóa nếu có sản phẩm liên kết
        }

        $stmt = $this->pdo->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
        return $stmt->execute([$id]);
    }
}
?>
