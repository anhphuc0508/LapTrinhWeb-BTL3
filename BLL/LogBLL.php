<?php
require_once __DIR__ . '/../DAL/LogDAL.php';

class LogBLL {
    private $dal;

    public function __construct($pdo) {
        $this->dal = new LogDAL($pdo);
    }

    public function addLog($user_id, $action_type, $entity_type, $entity_id, $description) {
        if (!$user_id) return false;
        return $this->dal->addLog($user_id, $action_type, $entity_type, $entity_id, $description);
    }
    
    public function getLogs($limit = 10, $offset = 0) {
        return $this->dal->getLogs($limit, $offset);
    }
    
    public function getTotalLogs() {
        return $this->dal->getTotalLogs();
    }
}
?>