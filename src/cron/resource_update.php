<?php
require_once 'c://xampp/projects/travianv2/src/config/database.php';

$database = new Database();
$conn = $database->getConnection();

$query = "SELECT village_id, last_update, wood_production, clay_production, iron_production, crop_production FROM resources";
$stmt = $conn->prepare($query);
$stmt->execute();

while($res = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $last = strtotime($res['last_update']);
    $now = time();
    $seconds = $now - $last;
    
    if($seconds > 0) {
        $wood_gain = ($res['wood_production'] * $seconds) / 3600;
        $clay_gain = ($res['clay_production'] * $seconds) / 3600;
        $iron_gain = ($res['iron_production'] * $seconds) / 3600;
        $crop_gain = ($res['crop_production'] * $seconds) / 3600;
        
        $q = "SELECT level FROM buildings WHERE village_id = :vid AND building_type = 'warehouse'";
        $s = $conn->prepare($q);
        $s->bindParam(':vid', $res['village_id']);
        $s->execute();
        $wh = $s->fetch(PDO::FETCH_ASSOC);
        $storage = 5000 + (($wh ? $wh['level'] : 0) * 2000);
        
        $q = "SELECT level FROM buildings WHERE village_id = :vid AND building_type = 'granary'";
        $s = $conn->prepare($q);
        $s->bindParam(':vid', $res['village_id']);
        $s->execute();
        $gr = $s->fetch(PDO::FETCH_ASSOC);
        $granary = 5000 + (($gr ? $gr['level'] : 0) * 2000);
        
        $q = "UPDATE resources SET wood = LEAST(wood + :w, :s), clay = LEAST(clay + :c, :s), 
              iron = LEAST(iron + :i, :s), crop = LEAST(crop + :cr, :g), last_update = NOW() 
              WHERE village_id = :vid";
        $s = $conn->prepare($q);
        $s->bindParam(':w', $wood_gain);
        $s->bindParam(':c', $clay_gain);
        $s->bindParam(':i', $iron_gain);
        $s->bindParam(':cr', $crop_gain);
        $s->bindParam(':s', $storage);
        $s->bindParam(':g', $granary);
        $s->bindParam(':vid', $res['village_id']);
        $s->execute();
    }
}

echo "Resources updated at " . date('Y-m-d H:i:s') . "\n";
?>