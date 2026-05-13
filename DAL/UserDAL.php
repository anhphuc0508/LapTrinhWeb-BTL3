<?php
class UserDAL {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addUser($username, $email, $password, $fullname = null, $role = 'employee') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, email, password, full_name, role) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([$username, $email, $hashedPassword, $fullname, $role]);
    }

    public function checkLogin($username, $password) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                return $user; 
            }
        }
        return false; 
    }
}
?>