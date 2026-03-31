<?php
require_once __DIR__ . '/../config/database.php';

class Battle {
    private $conn;
    
    private $unit_stats = [
        'legionnaire' => ['attack' => 40, 'defense_infantry' => 35, 'defense_cavalry' => 50, 'speed' => 6, 'carry' => 50, 'cost' => [120, 100, 150, 40]],
        'praetorian' => ['attack' => 30, 'defense_infantry' => 65, 'defense_cavalry' => 35, 'speed' => 5, 'carry' => 30, 'cost' => [100, 130, 160, 70]],
        'club_swinger' => ['attack' => 40, 'defense_infantry' => 20, 'defense_cavalry' => 5, 'speed' => 7, 'carry' => 60, 'cost' => [95, 75, 40, 40]],
        'spearman' => ['attack' => 10, 'defense_infantry' => 35, 'defense_cavalry' => 60, 'speed' => 7, 'carry' => 40, 'cost' => [145, 70, 85, 40]],
        'phalanx' => ['attack' => 15, 'defense_infantry' => 40, 'defense_cavalry' => 50, 'speed' => 7, 'carry' => 35, 'cost' => [100, 130, 55, 30]],
        'swordsman' => ['attack' => 65, 'defense_infantry' => 35, 'defense_cavalry' => 20, 'speed' => 6, 'carry' => 45, 'cost' => [140, 150, 185, 60]]
    ];
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getTrainingCost($unit_type) {
        if(isset($this->unit_stats[$unit_type])) {
            $cost = $this->unit_stats[$unit_type]['cost'];
            return ['wood' => $cost[0], 'clay' => $cost[1], 'iron' => $cost[2], 'crop' => $cost[3]];
        }
        return ['wood' => 100, 'clay' => 100, 'iron' => 100, 'crop' => 50];
    }
    
    public function getTrainingTime($unit_type) {
        $times = [
            'legionnaire' => 1800, 'praetorian' => 2100, 'club_swinger' => 1500,
            'spearman' => 1800, 'phalanx' => 1800, 'swordsman' => 2100
        ];
        return isset($times[$unit_type]) ? $times[$unit_type] : 1800;
    }
    
    public function getUnitStat($unit_type, $stat) {
        return isset($this->unit_stats[$unit_type][$stat]) ? $this->unit_stats[$unit_type][$stat] : 0;
    }
    
    public function sendAttack($from_village, $to_village, $units, $user_id) {
        $distance = $this->calculateDistance($from_village, $to_village);
        $fastest_speed = $this->getFastestUnitSpeed($units);
        $travel_time = ceil($distance / $fastest_speed * 3600);
        $arrival_time = date('Y-m-d H:i:s', time() + $travel_time);
        
        $query = "INSERT INTO troop_movements (movement_type, from_village_id, to_village_id, user_id, arrival_time, units, travel_time)
                  VALUES ('attack', :from_village, :to_village, :user_id, :arrival_time, :units, :travel_time)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':from_village', $from_village);
        $stmt->bindParam(':to_village', $to_village);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':arrival_time', $arrival_time);
        $stmt->bindParam(':units', json_encode($units));
        $stmt->bindParam(':travel_time', $travel_time);
        
        if($stmt->execute()) {
            foreach($units as $unit_type => $count) {
                $this->removeTroops($from_village, $unit_type, $count);
            }
            return ['success' => true, 'travel_time' => $travel_time];
        }
        return ['success' => false, 'message' => 'Failed to send attack'];
    }
    
    private function calculateDistance($village1_id, $village2_id) {
        $query = "SELECT v1.x as x1, v1.y as y1, v2.x as x2, v2.y as y2
                  FROM villages v1, villages v2 WHERE v1.id = :v1 AND v2.id = :v2";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':v1', $village1_id);
        $stmt->bindParam(':v2', $village2_id);
        $stmt->execute();
        $coords = $stmt->fetch(PDO::FETCH_ASSOC);
        $dx = abs($coords['x1'] - $coords['x2']);
        $dy = abs($coords['y1'] - $coords['y2']);
        return sqrt($dx * $dx + $dy * $dy);
    }
    
    private function getFastestUnitSpeed($units) {
        $max_speed = 6;
        foreach($units as $unit_type => $count) {
            if($count > 0 && isset($this->unit_stats[$unit_type])) {
                $max_speed = max($max_speed, $this->unit_stats[$unit_type]['speed']);
            }
        }
        return $max_speed;
    }
    
    private function removeTroops($village_id, $unit_type, $count) {
        $query = "UPDATE troops SET quantity = quantity - :count WHERE village_id = :village_id AND unit_type = :unit_type";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':count', $count);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->bindParam(':unit_type', $unit_type);
        $stmt->execute();
    }
}
?>