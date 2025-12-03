<?php
class History {
    private $conn;

    public function __construct() {
        // Savienojumu iegūst no Database klases instances
        $database = new Database(); 
        $this->conn = $database->connect();
    }

    public function logAction(int $user_id, string $action_type, string $action_description, $target_id = null, $target_type = null): bool {
        
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

        $sql = "INSERT INTO action_history (user_id, action_type, action_description, target_id, target_type, ip_address, user_agent) 
                VALUES (:user_id, :action_type, :action_description, :target_id, :target_type, :ip_address, :user_agent)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':action_type', $action_type);
            $stmt->bindParam(':action_description', $action_description);
            $stmt->bindParam(':target_id', $target_id, PDO::PARAM_INT);
            $stmt->bindParam(':target_type', $target_type);
            $stmt->bindParam(':ip_address', $ip_address);
            $stmt->bindParam(':user_agent', $user_agent);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    

    public function getAllHistory(): array {
        $sql = "SELECT ah.*, u.username, u.first_name, u.last_name FROM action_history ah 
                JOIN users u ON ah.user_id = u.id 
                ORDER BY ah.created_at DESC";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}