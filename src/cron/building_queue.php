<?php

// Include required files
require_once 'c://xampp/projects/travianv2/src/config/database.php';
require_once 'c://xampp/projects/travianv2/src/classes/building.php';

// Create database connection
$database = new Database();
$conn = $database->getConnection();

if(!$conn) {
    die("Database connection failed\n");
}

$building = new Building();

// Process completed building upgrades
$query = "SELECT id, village_id, building_type, target_level, end_time FROM building_queue WHERE end_time <= NOW()";
$stmt = $conn->prepare($query);
$stmt->execute();

$processed = 0;
$failed = 0;

echo "Found " . $stmt->rowCount() . " completed upgrades\n";

while($queue = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Processing building ID: {$queue['id']} - {$queue['building_type']} to level {$queue['target_level']}\n";
    
    if($building->completeUpgrade($queue['id'])) {
        $processed++;
        echo "✓ Successfully completed upgrade ID: {$queue['id']}\n";
    } else {
        $failed++;
        echo "✗ Failed to complete upgrade ID: {$queue['id']}\n";
        
        // Check what went wrong
        $check = "SELECT * FROM buildings WHERE village_id = :village_id AND building_type = :building_type";
        $stmt2 = $conn->prepare($check);
        $stmt2->bindParam(':village_id', $queue['village_id']);
        $stmt2->bindParam(':building_type', $queue['building_type']);
        $stmt2->execute();
        $building_data = $stmt2->fetch(PDO::FETCH_ASSOC);
        
        echo "  Current building level: " . ($building_data ? $building_data['level'] : 'not found') . "\n";
        echo "  Is upgrading: " . ($building_data ? ($building_data['is_upgrading'] ? 'yes' : 'no') : 'N/A') . "\n";
    }
}

echo "Building queue processed. Completed: {$processed}, Failed: {$failed} at " . date('Y-m-d H:i:s') . "\n";
?>