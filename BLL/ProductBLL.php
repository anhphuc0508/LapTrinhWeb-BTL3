<?php
require_once __DIR__ . '/../DAL/ProductDAL.php';

class ProductBLL {
    private $dal;
    private $pdo;

    public function __construct($pdo) {
        $this->dal = new ProductDAL($pdo);
        $this->pdo = $pdo;
    }



    public function getProducts($search = '', $category_id = '', $limit = 10, $offset = 0) {
        return $this->dal->getProducts($search, $category_id, $limit, $offset);
    }
    public function getTotalProducts($search = '', $category_id = '') {
        return $this->dal->getTotalProducts($search, $category_id);
    }

    public function getProductById($id) {
        return $this->dal->getProductById($id);
    }

    public function getCategories() {
        return $this->dal->getCategories();
    }

    public function getSuppliers() {
        return $this->dal->getSuppliers();
    }

    public function addProduct($data) {
        return $this->dal->addProduct($data);
    }

    public function updateProduct($data) {
        return $this->dal->updateProduct($data);
    }

    public function deleteProduct($id) {
        return $this->dal->deleteProduct($id);
    }

    public function cloneProduct($id) {
        $product = $this->getProductById($id);
        if (!$product) return false;

        $newData = $product;
        unset($newData['product_id']);
        unset($newData['created_at']);
        $newData['product_name'] = $product['product_name'] . ' (Copy)';
        
        return $this->dal->addProduct($newData);
    }
    public function getProductVariants($product_id = null){
        return $this->dal->getProductVariants($product_id);
    }

    public function addVariant($data) {
        $result = $this->dal->addVariant($data); 
        if ($result) {
            $this->dal->syncProductStock($data['product_id']);
        }
        return $result;
    }

    public function updateVariant($data) {
        $result = $this->dal->updateVariant($data); 
        if ($result) {
            $this->dal->syncProductStock($data['product_id']);
        }
        return $result;
    }

    public function deleteVariant($variant_id) {
        $stmt = $this->pdo->prepare("SELECT product_id FROM product_variants WHERE variant_id = ?");
        $stmt->execute([$variant_id]);
        $variant = $stmt->fetch();
        
        $result = $this->dal->deleteVariant($variant_id);
        
        if ($result && $variant) {
            $this->dal->syncProductStock($variant['product_id']); 
        }
        return $result;
    }
    public function getTopSellingProducts($limit = 5) {
        return $this->dal->getTopSellingProducts($limit);
    }
    
    public function countProductsByCategoryName($category_name) {
        return $this->dal->countProductsByCategoryName($category_name);
    }
}
?>
