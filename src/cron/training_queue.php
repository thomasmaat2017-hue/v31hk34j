<?php

require_once 'c://xampp/projects/travianv2/src/config/database.php';

$database = new Database();
$conn = $database->getConnection();

$query = "SELECT * FROM training_queue WHERE end_time <= NOW() AND processed = 0";
$stmt = $conn->prepare($query);
$stmt->execute();

while($t = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $conn->beginTransaction();
    
    $q = "INSERT INTO troops (village_id, unit_type, quantity) VALUES (:vid, :unit, :qty) 
          ON DUPLICATE KEY UPDATE quantity = quantity + :qty";
    $s = $conn->prepare($q);
    $s->bindParam(':vid', $t['village_id']);
    $s->bindParam(':unit', $t['unit_type']);
    $s->bindParam(':qty', $t['quantity']);
    $s->execute();
    
    $q = "UPDATE training_queue SET processed = 1 WHERE id = :id";
    $s = $conn->prepare($q);
    $s->bindParam(':id', $t['id']);
    $s->execute();
    
    $conn->commit();
    echo "Completed: {$t['quantity']}x {$t['unit_type']}\n";
}

echo "Training queue processed at " . date('Y-m-d H:i:s') . "\n";
?>