<?php
require_once __DIR__ . '/../config/database.php';

class Building {
    private $conn;
    
    private $building_data = [
        'main_building' => [
            'name' => 'Main Building',
            'base_cost' => [70, 40, 60, 20],
            'base_time' => 3600,
            'description' => 'The center of your village. Required for most buildings. Increases construction speed.'
        ],
        'woodcutter' => [
            'name' => 'Woodcutter',
            'base_cost' => [40, 50, 30, 10],
            'base_time' => 1200,
            'description' => 'Produces wood. Each level increases production by 5 per hour.'
        ],
        'clay_pit' => [
            'name' => 'Clay Pit',
            'base_cost' => [40, 50, 30, 10],
            'base_time' => 1200,
            'description' => 'Produces clay. Each level increases production by 5 per hour.'
        ],
        'iron_mine' => [
            'name' => 'Iron Mine',
            'base_cost' => [40, 50, 30, 10],
            'base_time' => 1200,
            'description' => 'Produces iron. Each level increases production by 5 per hour.'
        ],
        'farm' => [
            'name' => 'Farm',
            'base_cost' => [40, 50, 30, 10],
            'base_time' => 1200,
            'description' => 'Produces crop. Each level increases production by 5 per hour.'
        ],
        'warehouse' => [
            'name' => 'Warehouse',
            'base_cost' => [130, 160, 90, 40],
            'base_time' => 1800,
            'description' => 'Increases storage capacity for wood, clay, and iron by 2000 per level.'
        ],
        'granary' => [
            'name' => 'Granary',
            'base_cost' => [80, 100, 70, 20],
            'base_time' => 1800,
            'description' => 'Increases storage capacity for crop by 2000 per level.'
        ],
        'barracks' => [
            'name' => 'Barracks',
            'base_cost' => [210, 140, 260, 120],
            'base_time' => 3600,
            'description' => 'Allows training of infantry units. Higher levels train troops faster.'
        ],
        'stable' => [
            'name' => 'Stable',
            'base_cost' => [260, 240, 300, 140],
            'base_time' => 5400,
            'description' => 'Allows training of cavalry units. Faster movement on the world map.'
        ],
        'marketplace' => [
            'name' => 'Marketplace',
            'base_cost' => [80, 70, 120, 50],
            'base_time' => 2700,
            'description' => 'Allows trading resources with other players. Each level increases merchant capacity.'
        ],
        'embassy' => [
            'name' => 'Embassy',
            'base_cost' => [180, 130, 150, 80],
            'base_time' => 3600,
            'description' => 'Allows creating or joining alliances. Higher levels allow more alliance members.'
        ],
        'smithy' => [
            'name' => 'Smithy',
            'base_cost' => [140, 160, 200, 80],
            'base_time' => 4200,
            'description' => 'Upgrades weapon and armor of troops. Increases attack and defense stats.'
        ],
        'academy' => [
            'name' => 'Academy',
            'base_cost' => [180, 200, 240, 100],
            'base_time' => 5400,
            'description' => 'Researches technologies and improvements for your village.'
        ],
        'wall' => [
            'name' => 'Wall',
            'base_cost' => [100, 120, 80, 40],
            'base_time' => 1800,
            'description' => 'Defensive wall that protects your village. Increases defense bonus by 5% per level.'
        ]
    ];
    
    private $prerequisites = [
        'barracks' => ['main_building' => 3],
        'stable' => ['main_building' => 5, 'barracks' => 3],
        'marketplace' => ['main_building' => 3],
        'embassy' => ['main_building' => 1],
        'smithy' => ['main_building' => 3, 'barracks' => 1],
        'academy' => ['main_building' => 3, 'barracks' => 3],
        'wall' => ['main_building' => 1]
    ];
    
    // Populatie bijdrage per gebouw type
    private $population_weights = [
        'main_building' => 5,
        'woodcutter' => 1,
        'clay_pit' => 1,
        'iron_mine' => 1,
        'farm' => 1,
        'warehouse' => 2,
        'granary' => 2,
        'barracks' => 3,
        'stable' => 3,
        'marketplace' => 2,
        'embassy' => 4,
        'smithy' => 3,
        'academy' => 4,
        'wall' => 2
    ];
    
    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }
    
    public function getUpgradeCost($building_type, $current_level) {
        if(!isset($this->building_data[$building_type])) {
            return false;
        }
        
        $cost = $this->building_data[$building_type]['base_cost'];
        $multiplier = pow(1.2, $current_level);
        
        // Apply tribe bonus for Romans (10% cheaper buildings)
        if(isset($_SESSION['tribe']) && $_SESSION['tribe'] == 'roman') {
            $multiplier *= 0.9;
        }
        
        return [
            'wood' => ceil($cost[0] * $multiplier),
            'clay' => ceil($cost[1] * $multiplier),
            'iron' => ceil($cost[2] * $multiplier),
            'crop' => ceil($cost[3] * $multiplier)
        ];
    }
    
    public function getUpgradeTime($building_type, $current_level) {
        if(!isset($this->building_data[$building_type])) {
            return false;
        }
        
        $base_time = $this->building_data[$building_type]['base_time'];
        $time = ceil($base_time * pow(1.1, $current_level));
        
        // Apply tribe bonus for Romans (10% faster building)
        if(isset($_SESSION['tribe']) && $_SESSION['tribe'] == 'roman') {
            $time = ceil($time * 0.9);
        }
        
        return $time;
    }
    
    public function checkPrerequisites($building_type, $village_id) {
        if(!isset($this->prerequisites[$building_type])) {
            return ['met' => true, 'missing' => []];
        }
        
        $missing = [];
        foreach($this->prerequisites[$building_type] as $req_building => $req_level) {
            $query = "SELECT level FROM buildings WHERE village_id = :village_id AND building_type = :building_type";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':village_id', $village_id);
            $stmt->bindParam(':building_type', $req_building);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $current_level = $row ? $row['level'] : 0;
            if($current_level < $req_level) {
                $missing[] = [
                    'building' => $req_building,
                    'required' => $req_level,
                    'current' => $current_level
                ];
            }
        }
        
        return [
            'met' => empty($missing),
            'missing' => $missing
        ];
    }
    
    public function startUpgrade($village_id, $building_type) {
        // Check if building exists
        $query = "SELECT level, is_upgrading FROM buildings WHERE village_id = :village_id AND building_type = :building_type";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->bindParam(':building_type', $building_type);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$row) {
            return false;
        }
        
        // Check if already upgrading
        if($row['is_upgrading']) {
            return false;
        }
        
        $current_level = $row['level'];
        
        // Check prerequisites
        $prereq_check = $this->checkPrerequisites($building_type, $village_id);
        if(!$prereq_check['met']) {
            return false;
        }
        
        $cost = $this->getUpgradeCost($building_type, $current_level);
        $time = $this->getUpgradeTime($building_type, $current_level);
        
        // Check resources
        $query = "SELECT wood, clay, iron, crop FROM resources WHERE village_id = :village_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        $resources = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($resources['wood'] < $cost['wood'] || 
           $resources['clay'] < $cost['clay'] || 
           $resources['iron'] < $cost['iron'] || 
           $resources['crop'] < $cost['crop']) {
            return false;
        }
        
        // Check if there's already a queue for this building
        $query = "SELECT id FROM building_queue WHERE village_id = :village_id AND building_type = :building_type AND end_time > NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->bindParam(':building_type', $building_type);
        $stmt->execute();
        if($stmt->rowCount() > 0) {
            return false;
        }
        
        // Start transaction
        $this->conn->beginTransaction();
        
        try {
            // Deduct resources
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
            $stmt->execute();
            
            // Add to queue
            $end_time = date('Y-m-d H:i:s', time() + $time);
            $query = "INSERT INTO building_queue (village_id, building_type, target_level, end_time, 
                      wood_cost, clay_cost, iron_cost, crop_cost)
                      VALUES (:village_id, :building_type, :target_level, :end_time,
                      :wood_cost, :clay_cost, :iron_cost, :crop_cost)";
            $stmt = $this->conn->prepare($query);
            $target_level = $current_level + 1;
            $stmt->bindParam(':village_id', $village_id);
            $stmt->bindParam(':building_type', $building_type);
            $stmt->bindParam(':target_level', $target_level);
            $stmt->bindParam(':end_time', $end_time);
            $stmt->bindParam(':wood_cost', $cost['wood']);
            $stmt->bindParam(':clay_cost', $cost['clay']);
            $stmt->bindParam(':iron_cost', $cost['iron']);
            $stmt->bindParam(':crop_cost', $cost['crop']);
            $stmt->execute();
            
            // Mark building as upgrading
            $query = "UPDATE buildings SET is_upgrading = TRUE, upgrade_complete_time = :end_time
                      WHERE village_id = :village_id AND building_type = :building_type";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':end_time', $end_time);
            $stmt->bindParam(':village_id', $village_id);
            $stmt->bindParam(':building_type', $building_type);
            $stmt->execute();
            
            $this->conn->commit();
            return true;
            
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log("Upgrade error: " . $e->getMessage());
            return false;
        }
    }
    
    public function completeUpgrade($queue_id) {
        $query = "SELECT * FROM building_queue WHERE id = :queue_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':queue_id', $queue_id);
        $stmt->execute();
        $queue = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if(!$queue || strtotime($queue['end_time']) > time()) {
            return false;
        }
        
        try {
            $this->conn->beginTransaction();
            
            // Update building level
            $query = "UPDATE buildings SET level = :level, is_upgrading = FALSE, upgrade_complete_time = NULL
                      WHERE village_id = :village_id AND building_type = :building_type";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':level', $queue['target_level']);
            $stmt->bindParam(':village_id', $queue['village_id']);
            $stmt->bindParam(':building_type', $queue['building_type']);
            $stmt->execute();
            
            // Update resource production if it's a resource building
            $this->updateResourceProduction($queue['village_id'], $queue['building_type'], $queue['target_level']);
            
            // Update population based on ALL buildings
            $new_population = $this->updateVillagePopulation($queue['village_id']);
            
            // Remove from queue
            $query = "DELETE FROM building_queue WHERE id = :queue_id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':queue_id', $queue_id);
            $stmt->execute();
            
            $this->conn->commit();
            
            error_log("Upgrade completed: {$queue['building_type']} to level {$queue['target_level']}, new population: {$new_population}");
            return true;
            
        } catch(Exception $e) {
            $this->conn->rollBack();
            error_log("Complete upgrade error: " . $e->getMessage());
            return false;
        }
    }
    
    private function updateResourceProduction($village_id, $building_type, $new_level) {
        $production_increase = 5; // Base increase per level
        
        $query = "UPDATE resources SET ";
        
        switch($building_type) {
            case 'woodcutter':
                $query .= "wood_production = wood_production + :increase";
                break;
            case 'clay_pit':
                $query .= "clay_production = clay_production + :increase";
                break;
            case 'iron_mine':
                $query .= "iron_production = iron_production + :increase";
                break;
            case 'farm':
                $query .= "crop_production = crop_production + :increase";
                break;
            default:
                return;
        }
        
        $query .= " WHERE village_id = :village_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':increase', $production_increase);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
    }
    
    public function calculatePopulation($village_id) {
        $query = "SELECT building_type, level FROM buildings WHERE village_id = :village_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        
        $total_population = 0;
        while($building = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $weight = isset($this->population_weights[$building['building_type']]) 
                      ? $this->population_weights[$building['building_type']] 
                      : 1;
            $total_population += $building['level'] * $weight;
        }
        
        // Minimum population is 10
        return max(10, $total_population);
    }
    
    public function updateVillagePopulation($village_id) {
        $new_population = $this->calculatePopulation($village_id);
        
        $query = "UPDATE villages SET population = :population WHERE id = :village_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':population', $new_population);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        
        // Update player stats
        $query = "UPDATE player_stats ps 
                  SET total_population = (
                      SELECT SUM(population) FROM villages WHERE user_id = (SELECT user_id FROM villages WHERE id = :village_id)
                  )
                  WHERE user_id = (SELECT user_id FROM villages WHERE id = :village_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        
        return $new_population;
    }
    
    public function getPopulationBreakdown($village_id) {
        $query = "SELECT building_type, level FROM buildings WHERE village_id = :village_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':village_id', $village_id);
        $stmt->execute();
        
        $breakdown = [];
        $total = 0;
        
        while($building = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $weight = isset($this->population_weights[$building['building_type']]) 
                      ? $this->population_weights[$building['building_type']] 
                      : 1;
            $contribution = $building['level'] * $weight;
            $total += $contribution;
            $breakdown[] = [
                'type' => $building['building_type'],
                'level' => $building['level'],
                'weight' => $weight,
                'contribution' => $contribution
            ];
        }
        
        return [
            'total' => max(10, $total),
            'breakdown' => $breakdown
        ];
    }
    
    public function getBuildingDescription($building_type) {
        return isset($this->building_data[$building_type]['description']) 
            ? $this->building_data[$building_type]['description'] 
            : 'A useful building for your village.';
    }
    
    public function getBuildingName($building_type) {
        return isset($this->building_data[$building_type]['name']) 
            ? $this->building_data[$building_type]['name'] 
            : ucfirst(str_replace('_', ' ', $building_type));
    }
    
    public function getAllBuildingTypes() {
        return array_keys($this->building_data);
    }
    
    public function getPrerequisites($building_type) {
        return isset($this->prerequisites[$building_type]) 
            ? $this->prerequisites[$building_type] 
            : [];
    }
}
?>