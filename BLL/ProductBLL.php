<?php
require_once __DIR__ . '/../DAL/ProductDAL.php';

class ProductBLL {
    private $dal;
    private $pdo;

    public function __construct($pdo) {
        $this->dal = new ProductDAL($pdo);
        $this->pdo = $pdo;
    }



    public function getProducts($search = '') {
        return $this->dal->getProducts($search);
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
        return $this->dal->addVariant($data);
    }

    public function updateVariant($data) {
        return $this->dal->updateVariant($data);
    }

    public function deleteVariant($variant_id) {
        return $this->dal->deleteVariant($variant_id);
    }
}
?>
