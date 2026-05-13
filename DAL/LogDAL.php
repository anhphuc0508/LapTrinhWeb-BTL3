<?php
class LogDAL {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addLog($user_id, $action_type, $entity_type, $entity_id, $description) {
        $stmt = $this->pdo->prepare("
            INSERT INTO activity_logs (user_id, action_type, entity_type, entity_id, description) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$user_id, $action_type, $entity_type, $entity_id, $description]);
    }
    
    public function getLogs() {
        $sql = "SELECT l.*, u.username, u.full_name 
                FROM activity_logs l 
                LEFT JOIN users u ON l.user_id = u.user_id 
                ORDER BY l.created_at DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }
}
?>