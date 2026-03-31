<?php
require_once 'c://xampp/projects/travianv2/src/config/database.php';
require_once 'c://xampp/projects/travianv2/src/classes/battle.php';

$database = new Database();
$conn = $database->getConnection();
$battle = new Battle();

$query = "SELECT * FROM troop_movements WHERE arrival_time <= NOW() AND processed = 0";
$stmt = $conn->prepare($query);
$stmt->execute();

while($move = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $units = json_decode($move['units'], true);
    
    if($move['movement_type'] == 'attack') {
        // Resolve battle logic here
        $query = "UPDATE troops SET quantity = quantity + :qty WHERE village_id = :vid AND unit_type = :unit";
        // Simplified: return troops to origin
        foreach($units as $unit => $count) {
            $s = $conn->prepare($query);
            $s->bindParam(':qty', $count);
            $s->bindParam(':vid', $move['from_village_id']);
            $s->bindParam(':unit', $unit);
            $s->execute();
        }
    }
    
    $q = "UPDATE troop_movements SET processed = 1 WHERE id = :id";
    $s = $conn->prepare($q);
    $s->bindParam(':id', $move['id']);
    $s->execute();
}

echo "Troop movements processed at " . date('Y-m-d H:i:s') . "\n";
?>