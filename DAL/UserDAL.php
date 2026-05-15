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

    public function getUsers($search = '', $limit = 10, $offset = 0) {
        $sql = "SELECT user_id, username, email, full_name, role, created_at FROM users WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (username LIKE :search OR email LIKE :search OR full_name LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY user_id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTotalUsers($search = '') {
        $sql = "SELECT COUNT(*) FROM users WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (username LIKE :search OR email LIKE :search OR full_name LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }

    public function updateUser($id, $fullname, $email, $role) {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET full_name = ?, email = ?, role = ? 
            WHERE user_id = ?
        ");
        return $stmt->execute([$fullname, $email, $role, $id]);
    }

    public function updatePassword($id, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET password = ? 
            WHERE user_id = ?
        ");
        return $stmt->execute([$hashedPassword, $id]);
    }

    public function deleteUser($id) {
        // Kiểm tra để không xóa tài khoản admin duy nhất
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        $adminCount = $stmt->fetchColumn();

        if ($adminCount <= 1) {
            return false; // Không cho xóa admin cuối cùng
        }

        $stmt = $this->pdo->prepare("DELETE FROM users WHERE user_id = ?");
        return $stmt->execute([$id]);
    }
}
?>