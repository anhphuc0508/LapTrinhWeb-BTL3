<?php
class ProductDAL {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getProducts($search = '') {
        $sql = "
            SELECT p.*, c.category_name, s.supplier_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.category_id
            LEFT JOIN suppliers s ON p.supplier_id = s.supplier_id
        ";

        if (!empty($search)) {
            $sql .= " WHERE p.product_name LIKE :search";
        }

        $sql .= " ORDER BY p.product_id DESC";

        $stmt = $this->pdo->prepare($sql);

        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%");
        }

        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getProductById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getCategories() {
        return $this->pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name ASC")->fetchAll();
    }

    public function getSuppliers() {
        return $this->pdo->query("SELECT supplier_id, supplier_name FROM suppliers ORDER BY supplier_name ASC")->fetchAll();
    }

    public function addProduct($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO products (category_id, supplier_id, product_name, description, unit_price, stock_quantity, user_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            empty($data['category_id']) ? null : $data['category_id'],
            empty($data['supplier_id']) ? null : $data['supplier_id'],
            $data['product_name'],
            $data['description'],
            $data['unit_price'] ?? 0,
            $data['stock_quantity'] ?? 0,
            $data['user_id'] ?? null
        ]);
        
        return $this->pdo->lastInsertId();
    }

    public function updateProduct($data) {
        $stmt = $this->pdo->prepare("
            UPDATE products 
            SET category_id = ?, supplier_id = ?, product_name = ?, description = ?, unit_price = ?, stock_quantity = ?, user_id = ?
            WHERE product_id = ?
        ");
        $stmt->execute([
            empty($data['category_id']) ? null : $data['category_id'],
            empty($data['supplier_id']) ? null : $data['supplier_id'],
            $data['product_name'],
            $data['description'],
            $data['unit_price'] ?? 0,
            $data['stock_quantity'] ?? 0,
            $data['user_id'] ?? null,
            $data['product_id']
        ]);
        
        return true;
    }

    public function deleteProduct($id) {
        $stmt = $this->pdo->prepare("DELETE FROM products WHERE product_id = ?");
        return $stmt->execute([$id]);
    }
    public function getProductVariants($product_id = null){
        if ($product_id) {
            $stmt = $this->pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY variant_id DESC");
            $stmt->execute([$product_id]);
            return $stmt->fetchAll();
        }
        return $this->pdo->query("SELECT * FROM product_variants ORDER BY variant_id DESC")->fetchAll();
    }

    public function addVariant($data) {
        $stmt = $this->pdo->prepare("
            INSERT INTO product_variants (product_id, sku, variant_name, price, stock_quantity) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $data['product_id'],
            $data['sku'] ?? null,
            $data['variant_name'] ?? null,
            $data['price'] ?? 0,
            $data['stock_quantity'] ?? 0
        ]);
    }

    public function updateVariant($data) {
        $stmt = $this->pdo->prepare("
            UPDATE product_variants 
            SET sku = ?, variant_name = ?, price = ?, stock_quantity = ?
            WHERE variant_id = ?
        ");
        return $stmt->execute([
            $data['sku'] ?? null,
            $data['variant_name'] ?? null,
            $data['price'] ?? 0,
            $data['stock_quantity'] ?? 0,
            $data['variant_id']
        ]);
    }

    public function deleteVariant($variant_id) {
        $stmt = $this->pdo->prepare("DELETE FROM product_variants WHERE variant_id = ?");
        return $stmt->execute([$variant_id]);
    }

    public function getTopSellingProducts($limit = 10) {
        $sql = "
            SELECT p.product_id, p.product_name, c.category_name, p.unit_price, p.stock_quantity, SUM(od.quantity) as total_sold
            FROM products p
            JOIN order_details od ON p.product_id = od.product_id
            JOIN orders o ON od.order_id = o.order_id
            LEFT JOIN categories c ON p.category_id = c.category_id
            WHERE o.status != 'Đã hủy'
            GROUP BY p.product_id, p.product_name, c.category_name, p.unit_price, p.stock_quantity
            ORDER BY total_sold DESC
            LIMIT :limit
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
