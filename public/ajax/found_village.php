<?php
session_start();
require_once '../../src/config/database.php';
require_once '../../src/classes/User.php';
require_once '../../src/classes/Village.php';

if(!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

$x = isset($_POST['x']) ? intval($_POST['x']) : 0;
$y = isset($_POST['y']) ? intval($_POST['y']) : 0;

$database = new Database();
$conn = $database->getConnection();

// Check if cell is empty
$query = "SELECT village_id FROM map_cells WHERE x = :x AND y = :y";
$stmt = $conn->prepare($query);
$stmt->bindParam(':x', $x);
$stmt->bindParam(':y', $y);
$stmt->execute();
$cell = $stmt->fetch(PDO::FETCH_ASSOC);

if($cell && $cell['village_id']) {
    die(json_encode(['success' => false, 'message' => 'This cell is already occupied!']));
}

// Check if user has settlers
$query = "SELECT quantity FROM troops 
          WHERE village_id IN (SELECT id FROM villages WHERE user_id = :user_id) 
          AND unit_type = 'settler'";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$settlers = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$settlers || $settlers['quantity'] < 3) {
    die(json_encode(['success' => false, 'message' => 'You need 3 settlers to found a new village!']));
}

// Get current village to deduct settlers
$query = "SELECT id FROM villages WHERE user_id = :user_id LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$current_village = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$current_village) {
    die(json_encode(['success' => false, 'message' => 'No source village found']));
}

// Remove settlers
$query = "UPDATE troops SET quantity = quantity - 3 
          WHERE village_id = :village_id AND unit_type = 'settler'";
$stmt = $conn->prepare($query);
$stmt->bindParam(':village_id', $current_village['id']);
$stmt->execute();

// Create new village
$village_name = "Colony " . date('Y-m-d H:i:s');
$query = "INSERT INTO villages (user_id, name, x, y, population, loyalty) 
          VALUES (:user_id, :name, :x, :y, 10, 100)";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->bindParam(':name', $village_name);
$stmt->bindParam(':x', $x);
$stmt->bindParam(':y', $y);
$stmt->execute();

$new_village_id = $conn->lastInsertId();

// Update map cell
$query = "UPDATE map_cells SET village_id = :village_id WHERE x = :x AND y = :y";
$stmt = $conn->prepare($query);
$stmt->bindParam(':village_id', $new_village_id);
$stmt->bindParam(':x', $x);
$stmt->bindParam(':y', $y);
$stmt->execute();

// Initialize resources
$query = "INSERT INTO resources (village_id) VALUES (:village_id)";
$stmt = $conn->prepare($query);
$stmt->bindParam(':village_id', $new_village_id);
$stmt->execute();

// Initialize basic buildings
$buildings = ['main_building', 'warehouse', 'granary', 'woodcutter', 'clay_pit', 'iron_mine', 'farm'];
foreach($buildings as $building) {
    $query = "INSERT INTO buildings (village_id, building_type, level) 
              VALUES (:village_id, :building, 0)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':village_id', $new_village_id);
    $stmt->bindParam(':building', $building);
    $stmt->execute();
}

// Initialize troops
$troops = ['legionnaire', 'praetorian', 'club_swinger', 'spearman', 'phalanx', 'swordsman', 'settler'];
foreach($troops as $troop) {
    $query = "INSERT INTO troops (village_id, unit_type, quantity) 
              VALUES (:village_id, :troop, 0)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':village_id', $new_village_id);
    $stmt->bindParam(':troop', $troop);
    $stmt->execute();
}

echo json_encode(['success' => true, 'message' => 'Village founded successfully!']);
?>