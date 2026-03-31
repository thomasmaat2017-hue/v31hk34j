<?php
require_once __DIR__ . '/../config/database.php';

class Alliance {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function createAlliance($user_id, $name, $tag, $description) {
        $query = "SELECT id FROM alliances WHERE name = :name OR tag = :tag";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':tag', $tag);
        $stmt->execute();
        if($stmt->rowCount() > 0) return false;
        
        $query = "INSERT INTO alliances (name, tag, description, leader_id) VALUES (:name, :tag, :description, :leader_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':tag', $tag);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':leader_id', $user_id);
        
        if($stmt->execute()) {
            $alliance_id = $this->conn->lastInsertId();
            $query = "INSERT INTO alliance_members (alliance_id, user_id, rank) VALUES (:alliance_id, :user_id, 'leader')";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':alliance_id', $alliance_id);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->execute();
            return true;
        }
        return false;
    }
    
    public function joinAlliance($user_id, $alliance_id) {
        $query = "SELECT alliance_id FROM alliance_members WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        if($stmt->rowCount() > 0) return false;
        
        $query = "INSERT INTO alliance_members (alliance_id, user_id) VALUES (:alliance_id, :user_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':alliance_id', $alliance_id);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }
    
    public function leaveAlliance($user_id) {
        $query = "SELECT a.id FROM alliances a JOIN alliance_members am ON a.id = am.alliance_id 
                  WHERE am.user_id = :user_id AND am.rank = 'leader'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        if($stmt->rowCount() > 0) return false;
        
        $query = "DELETE FROM alliance_members WHERE user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        return $stmt->execute();
    }
    
    public function getUserAlliance($user_id) {
        $query = "SELECT a.*, am.rank FROM alliances a JOIN alliance_members am ON a.id = am.alliance_id 
                  WHERE am.user_id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getAllianceDetails($alliance_id) {
        $query = "SELECT * FROM alliances WHERE id = :alliance_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':alliance_id', $alliance_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getAllianceMembers($alliance_id) {
        $query = "SELECT u.id as user_id, u.username, am.rank, am.joined_at 
                  FROM alliance_members am JOIN users u ON am.user_id = u.id 
                  WHERE am.alliance_id = :alliance_id ORDER BY FIELD(am.rank, 'leader', 'member'), u.username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':alliance_id', $alliance_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getAlliances() {
        $query = "SELECT a.*, u.username as leader_name,
                  (SELECT COUNT(*) FROM alliance_members WHERE alliance_id = a.id) as member_count
                  FROM alliances a JOIN users u ON a.leader_id = u.id ORDER BY member_count DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function sendAllianceMessage($alliance_id, $from_user_id, $subject, $content) {
        $query = "INSERT INTO alliance_messages (alliance_id, from_user_id, subject, content) 
                  VALUES (:alliance_id, :from_user_id, :subject, :content)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':alliance_id', $alliance_id);
        $stmt->bindParam(':from_user_id', $from_user_id);
        $stmt->bindParam(':subject', $subject);
        $stmt->bindParam(':content', $content);
        return $stmt->execute();
    }
    
    public function getAllianceMessages($alliance_id) {
        $query = "SELECT am.*, u.username FROM alliance_messages am JOIN users u ON am.from_user_id = u.id 
                  WHERE am.alliance_id = :alliance_id ORDER BY am.created_at DESC LIMIT 50";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':alliance_id', $alliance_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function kickMember($alliance_id, $member_id) {
        $query = "SELECT rank FROM alliance_members WHERE alliance_id = :alliance_id AND user_id = :member_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':alliance_id', $alliance_id);
        $stmt->bindParam(':member_id', $member_id);
        $stmt->execute();
        $member = $stmt->fetch(PDO::FETCH_ASSOC);
        if($member && $member['rank'] == 'leader') return false;
        
        $query = "DELETE FROM alliance_members WHERE alliance_id = :alliance_id AND user_id = :member_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':alliance_id', $alliance_id);
        $stmt->bindParam(':member_id', $member_id);
        return $stmt->execute();
    }
    
    public function updateDescription($alliance_id, $description) {
        $query = "UPDATE alliances SET description = :description WHERE id = :alliance_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':alliance_id', $alliance_id);
        return $stmt->execute();
    }
    public function getAllianceRankings($limit = 100, $offset = 0) {
        $query = "SELECT * FROM alliance_rankings ORDER BY total_population DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllianceRank($alliance_id) {
        $query = "SELECT FIND_IN_SET(total_population, (
                    SELECT GROUP_CONCAT(total_population ORDER BY total_population DESC) 
                    FROM alliance_rankings
                  )) as rank FROM alliance_rankings WHERE id = :alliance_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':alliance_id', $alliance_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['rank'] : null;
    }

    public function getTotalAlliances() {
        $query = "SELECT COUNT(*) as total FROM alliance_rankings";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    public function getMilitaryRankings($limit = 100, $offset = 0) {
        $query = "SELECT * FROM military_rankings ORDER BY total_troops DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
?>