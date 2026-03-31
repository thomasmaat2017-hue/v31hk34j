<?php
require_once __DIR__ . '/../config/database.php';

class Village {
    private $conn;
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getResources($village_id) {
        $query = "SELECT * FROM resources WHERE village_id = :village_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function updateResources($village_id) {
        $query = "SELECT last_update, wood_production, clay_production, iron_production, crop_production 
                  FROM resources WHERE village_id = :village_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        $resource = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$resource) return;
        
        $last_update = strtotime($resource['last_update']);
        $now = time();
        $seconds_passed = $now - $last_update;
        
        if($seconds_passed > 0) {
            $wood_gain = ($resource['wood_production'] * $seconds_passed) / 3600;
            $clay_gain = ($resource['clay_production'] * $seconds_passed) / 3600;
            $iron_gain = ($resource['iron_production'] * $seconds_passed) / 3600;
            $crop_gain = ($resource['crop_production'] * $seconds_passed) / 3600;
            
            $storage_capacity = $this->getStorageCapacity($village_id);
            $granary_capacity = $this->getGranaryCapacity($village_id);
            
            $query = "UPDATE resources SET 
                      wood = LEAST(wood + :wood_gain, :storage_capacity),
                      clay = LEAST(clay + :clay_gain, :storage_capacity),
                      iron = LEAST(iron + :iron_gain, :storage_capacity),
                      crop = LEAST(crop + :crop_gain, :granary_capacity),
                      last_update = NOW()
                      WHERE village_id = :village_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':wood_gain', $wood_gain);
            $stmt->bindParam(':clay_gain', $clay_gain);
            $stmt->bindParam(':iron_gain', $iron_gain);
            $stmt->bindParam(':crop_gain', $crop_gain);
            $stmt->bindParam(':storage_capacity', $storage_capacity);
            $stmt->bindParam(':granary_capacity', $granary_capacity);
            $stmt->bindParam(':village_id', $village_id);
            $stmt->execute();
        }
    }
    
    private function getStorageCapacity($village_id) {
        $query = "SELECT level FROM buildings WHERE village_id = :village_id AND building_type = 'warehouse'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $level = $row ? $row['level'] : 0;
        return 5000 + ($level * 2000);
    }
    
    private function getGranaryCapacity($village_id) {
        $query = "SELECT level FROM buildings WHERE village_id = :village_id AND building_type = 'granary'";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $level = $row ? $row['level'] : 0;
        return 5000 + ($level * 2000);
    }
    
    public function getBuildings($village_id) {
        $query = "SELECT * FROM buildings WHERE village_id = :village_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getBuildingQueue($village_id) {
        $query = "SELECT * FROM building_queue WHERE village_id = :village_id AND end_time > NOW() ORDER BY end_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTroops($village_id) {
        $query = "SELECT unit_type, quantity FROM troops WHERE village_id = :village_id AND quantity > 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        $troops = [];
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $troops[$row['unit_type']] = $row['quantity'];
        }
        return $troops;
    }
    
    public function deductResources($village_id, $cost) {
        $query = "UPDATE resources SET 
                  wood = wood - :wood,
                  clay = clay - :clay,
                  iron = iron - :iron,
                  crop = crop - :crop
                  WHERE village_id = :village_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':wood', $cost['wood']);
        $stmt->bindParam(':clay', $cost['clay']);
        $stmt->bindParam(':iron', $cost['iron']);
        $stmt->bindParam(':crop', $cost['crop']);
        $stmt->bindParam(':village_id', $village_id);
        return $stmt->execute();
    }
    
    public function addTrainingQueue($village_id, $unit_type, $quantity, $training_time) {
        $end_time = date('Y-m-d H:i:s', time() + $training_time);
        $query = "INSERT INTO training_queue (village_id, unit_type, quantity, end_time) 
                  VALUES (:village_id, :unit_type, :quantity, :end_time)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->bindParam(':unit_type', $unit_type);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':end_time', $end_time);
        return $stmt->execute();
    }
    
    public function getTrainingQueue($village_id) {
        $query = "SELECT * FROM training_queue WHERE village_id = :village_id AND end_time > NOW() AND processed = 0 ORDER BY end_time ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getTrainingQueueCount($village_id) {
        $query = "SELECT COUNT(*) as count FROM training_queue WHERE village_id = :village_id AND processed = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['count'];
    }
    
    public function getTrainingTimeRemaining($village_id) {
        $query = "SELECT SUM(TIMESTAMPDIFF(SECOND, NOW(), end_time)) as total FROM training_queue 
                  WHERE village_id = :village_id AND end_time > NOW() AND processed = 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?: 0;
    }
}
?>