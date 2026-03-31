<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $conn;
    public $id;
    public $username;
    public $email;
    public $tribe;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function register($username, $password, $email, $tribe) {
        $query = "SELECT id FROM users WHERE username = :username OR email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $protection_until = date('Y-m-d H:i:s', strtotime('+7 days'));
        
        $query = "INSERT INTO users (username, password_hash, email, tribe, beginner_protection_until) 
                  VALUES (:username, :password, :email, :tribe, :protection)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':tribe', $tribe);
        $stmt->bindParam(':protection', $protection_until);
        
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            $this->username = $username;
            $this->tribe = $tribe;
            $this->createInitialVillage();
            return ['success' => true];
        }
        
        return ['success' => false, 'message' => 'Registration failed'];
    }
    
    private function createInitialVillage() {
        $query = "SELECT x, y FROM map_cells WHERE village_id IS NULL LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $cell = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($cell) {
            $x = $cell['x'];
            $y = $cell['y'];
        } else {
            $x = 0;
            $y = 0;
        }
        
        $village_name = $this->username . "'s Village";
        $query = "INSERT INTO villages (user_id, name, x, y) VALUES (:user_id, :name, :x, :y)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->id);
        $stmt->bindParam(':name', $village_name);
        $stmt->bindParam(':x', $x);
        $stmt->bindParam(':y', $y);
        $stmt->execute();
        
        $village_id = $this->conn->lastInsertId();
        
        $query = "UPDATE map_cells SET village_id = :village_id WHERE x = :x AND y = :y";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->bindParam(':x', $x);
        $stmt->bindParam(':y', $y);
        $stmt->execute();
        
        $query = "INSERT INTO resources (village_id) VALUES (:village_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        
        // Initialize ALL buildings
        $all_buildings = [
            'main_building', 'woodcutter', 'clay_pit', 'iron_mine', 'farm',
            'warehouse', 'granary', 'barracks', 'stable', 'marketplace', 
            'embassy', 'smithy', 'academy', 'wall'
        ];
        
        foreach($all_buildings as $building) {
            $query = "INSERT INTO buildings (village_id, building_type, level) VALUES (:village_id, :building, 0)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':village_id', $village_id);
            $stmt->bindParam(':building', $building);
            $stmt->execute();
        }
        
        // Initialize troops
        $troops = ['legionnaire', 'praetorian', 'club_swinger', 'spearman', 'phalanx', 'swordsman', 'settler'];
        foreach($troops as $troop) {
            $query = "INSERT INTO troops (village_id, unit_type, quantity) VALUES (:village_id, :troop, 0)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':village_id', $village_id);
            $stmt->bindParam(':troop', $troop);
            $stmt->execute();
        }
    }
    
    public function login($username, $password) {
        $query = "SELECT id, username, password_hash, tribe, is_banned FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $row['password_hash']) && !$row['is_banned']) {
                $this->id = $row['id'];
                $this->username = $row['username'];
                $this->tribe = $row['tribe'];
                
                $query = "UPDATE users SET last_login = NOW() WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':id', $this->id);
                $stmt->execute();
                
                return ['success' => true];
            }
        }
        return ['success' => false, 'message' => 'Invalid credentials'];
    }
    
    public function getVillages() {
        $query = "SELECT * FROM villages WHERE user_id = :user_id ORDER BY is_capital DESC, created_at ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $this->id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function hasBeginnerProtection() {
        $query = "SELECT beginner_protection_until FROM users WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && strtotime($row['beginner_protection_until']) > time();
    }
    public function getPlayerRankings($limit = 100, $offset = 0) {
        $query = "SELECT * FROM player_rankings ORDER BY total_population DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPlayerRank($user_id) {
        $query = "SELECT FIND_IN_SET(total_population, (
                    SELECT GROUP_CONCAT(total_population ORDER BY total_population DESC) 
                    FROM player_rankings
                  )) as rank FROM player_rankings WHERE id = :user_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['rank'] : null;
    }

    public function getTotalPlayers() {
        $query = "SELECT COUNT(*) as total FROM player_rankings";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}
?>