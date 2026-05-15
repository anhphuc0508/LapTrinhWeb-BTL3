<?php
require_once __DIR__ . '/../DAL/SupplierDAL.php';

class SupplierBLL {
    private $dal;

    public function __construct($pdo) {
        $this->dal = new SupplierDAL($pdo);
    }

    public function getSuppliers($search = '', $limit = 10, $offset = 0) {
        return $this->dal->getSuppliers($search, $limit, $offset);
    }

    public function getTotalSuppliers($search = '') {
        return $this->dal->getTotalSuppliers($search);
    }

    public function addSupplier($data) {
        return $this->dal->addSupplier(
            $data['supplier_name'], 
            $data['contact_person'] ?? null, 
            $data['phone'] ?? null, 
            $data['email'] ?? null, 
            $data['address'] ?? null,
            $data['tax_code'] ?? null
        );
    }

    public function updateSupplier($data) {
        if (empty($data['supplier_id'])) return false;
        return $this->dal->updateSupplier(
            $data['supplier_id'], 
            $data['supplier_name'], 
            $data['contact_person'] ?? null, 
            $data['phone'] ?? null, 
            $data['email'] ?? null, 
            $data['address'] ?? null,
            $data['tax_code'] ?? null
        );
    }

    public function deleteSupplier($id) {
        return $this->dal->deleteSupplier($id);
    }
}
?>
