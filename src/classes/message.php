<?php
require_once __DIR__ . '/../config/database.php';

class Message {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function sendMessage($from_user_id, $to_username, $subject, $content) {
        $query = "SELECT id FROM users WHERE username = :username AND is_active = 1 AND is_banned = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $to_username);
        $stmt->execute();
        $to_user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$to_user) return ['success' => false, 'message' => 'Player not found'];
        if($to_user['id'] == $from_user_id) return ['success' => false, 'message' => 'Cannot send to yourself'];
        
        $query = "INSERT INTO messages (from_user_id, to_user_id, subject, content) 
                  VALUES (:from_user_id, :to_user_id, :subject, :content)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':from_user_id', $from_user_id);
        $stmt->bindParam(':to_user_id', $to_user['id']);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':content', $content);
        
        return $stmt->execute() ? ['success' => true] : ['success' => false, 'message' => 'Failed to send'];
    }
    
    public function getInbox($user_id) {
        $query = "SELECT m.*, u.username as from_username FROM messages m 
                  JOIN users u ON m.from_user_id = u.id WHERE m.to_user_id = :user_id 
                  ORDER BY m.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getSentMessages($user_id) {
        $query = "SELECT m.*, u.username as to_username FROM messages m 
                  JOIN users u ON m.to_user_id = u.id WHERE m.from_user_id = :user_id 
                  ORDER BY m.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getMessage($message_id) {
        $query = "SELECT m.*, u1.username as from_username, u2.username as to_username 
                  FROM messages m JOIN users u1 ON m.from_user_id = u1.id 
                  JOIN users u2 ON m.to_user_id = u2.id WHERE m.id = :message_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':message_id', $message_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function markAsRead($message_id, $user_id) {
        $query = "UPDATE messages SET is_read = 1 WHERE id = :message_id AND to_user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':message_id', $message_id);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }
    
    public function deleteMessage($message_id, $user_id) {
        $query = "DELETE FROM messages WHERE id = :message_id AND (from_user_id = :user_id OR to_user_id = :user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':message_id', $message_id);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }
    
    public function getUnreadCount($user_id) {
        $query = "SELECT COUNT(*) as count FROM messages WHERE to_user_id = :user_id AND is_read = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
}
?>