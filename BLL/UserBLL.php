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
}
?>