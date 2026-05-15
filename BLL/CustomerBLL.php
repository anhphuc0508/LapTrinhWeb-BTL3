<?php
require_once __DIR__ . '/../DAL/CustomerDAL.php';

class CustomerBLL {
    private $dal;

    public function __construct($pdo) {
        $this->dal = new CustomerDAL($pdo);
    }

    public function getCustomers($search = '', $limit = 10, $offset = 0) {
        return $this->dal->getCustomers($search, $limit, $offset);
    }

    public function getTotalCustomers($search = '') {
        return $this->dal->getTotalCustomers($search);
    }

    public function addCustomer($data) {
        return $this->dal->addCustomer(
            $data['customer_name'], 
            $data['phone'] ?? null, 
            $data['email'] ?? null, 
            $data['address'] ?? null
        );
    }

    public function updateCustomer($data) {
        if (empty($data['customer_id'])) return false;
        return $this->dal->updateCustomer(
            $data['customer_id'], 
            $data['customer_name'], 
            $data['phone'] ?? null, 
            $data['email'] ?? null, 
            $data['address'] ?? null
        );
    }

    public function deleteCustomer($id) {
        return $this->dal->deleteCustomer($id);
    }
}
?>