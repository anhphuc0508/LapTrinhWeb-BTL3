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
    
    public function getLogs($limit = 10, $offset = 0) {
        $sql = "SELECT l.*, u.username, u.full_name 
                FROM activity_logs l 
                LEFT JOIN users u ON l.user_id = u.user_id 
                ORDER BY l.created_at DESC
                LIMIT :limit OFFSET: offset";
        $stmt = $this->pdo->query($sql);
        $stmt->bindValue(':limit', (int) $limit , PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int) $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function getTotalLogs(){
        $sql = "SELECT COUNT(*) FROM activity_logs";
        $stmt= $this->pdo->query($sql);
        return $stmt->fetchColumn();
    }
}
?>