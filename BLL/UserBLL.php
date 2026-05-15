<?php
require_once __DIR__ . '/../DAL/UserDAL.php';
class UserBLL {
    private $dal;

    public function __construct($pdo) {
        $this->dal = new UserDAL($pdo);
    }
    
    public function addUser($data) {
        return $this->dal->addUser($data['username'], $data['email'], $data['password'], $data['fullname'] ?? null, $data['role'] ?? 'employee');
    }
    
    public function checkLogin($data) {
        return $this->dal->checkLogin($data['username'], $data['password']);
    }

    public function getUsers($search = '', $limit = 10, $offset = 0) {
        return $this->dal->getUsers($search, $limit, $offset);
    }

    public function getTotalUsers($search = '') {
        return $this->dal->getTotalUsers($search);
    }

    public function updateUser($data) {
        if (empty($data['user_id'])) return false;
        return $this->dal->updateUser(
            $data['user_id'],
            $data['full_name'] ?? null,
            $data['email'] ?? null,
            $data['role'] ?? 'employee'
        );
    }

    public function updatePassword($id, $password) {
        return $this->dal->updatePassword($id, $password);
    }

    public function deleteUser($id) {
        return $this->dal->deleteUser($id);
    }
}
?>