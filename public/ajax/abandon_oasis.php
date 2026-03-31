<?php
session_start();
require_once '../../src/config/database.php';

if(!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

$oasis_id = isset($_POST['oasis_id']) ? intval($_POST['oasis_id']) : 0;

$database = new Database();
$conn = $database->getConnection();

// Verify oasis belongs to user
$query = "SELECT v.id FROM villages v 
          JOIN map_cells mc ON v.id = mc.village_id 
          WHERE v.id = :oasis_id AND v.user_id = :user_id AND mc.oasis_type IS NOT NULL";
$stmt = $conn->prepare($query);
$stmt->bindParam(':oasis_id', $oasis_id);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();

if($stmt->rowCount() == 0) {
    die(json_encode(['success' => false, 'message' => 'You do not own this oasis!']));
}

// Get troops stationed at oasis
$query = "SELECT unit_type, quantity FROM troops WHERE village_id = :oasis_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':oasis_id', $oasis_id);
$stmt->execute();
$troops = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Find nearest village to return troops
$query = "SELECT id FROM villages WHERE user_id = :user_id AND id != :oasis_id LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->bindParam(':oasis_id', $oasis_id);
$stmt->execute();
$village = $stmt->fetch(PDO::FETCH_ASSOC);

if($village) {
    foreach($troops as $troop) {
        if($troop['quantity'] > 0) {
            $query = "UPDATE troops SET quantity = quantity + :quantity 
                      WHERE village_id = :village_id AND unit_type = :unit_type";
            $stmt = $conn->prepare($query);
            $stmt->bindParam(':quantity', $troop['quantity']);
            $stmt->bindParam(':village_id', $village['id']);
            $stmt->bindParam(':unit_type', $troop['unit_type']);
            $stmt->execute();
        }
    }
}

// Remove oasis from map
$query = "UPDATE map_cells SET village_id = NULL, oasis_type = NULL, 
          bonus_wood = 0, bonus_clay = 0, bonus_iron = 0, bonus_crop = 0 
          WHERE village_id = :oasis_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':oasis_id', $oasis_id);
$stmt->execute();

// Delete oasis village
$query = "DELETE FROM villages WHERE id = :oasis_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':oasis_id', $oasis_id);
$stmt->execute();

echo json_encode(['success' => true, 'message' => 'Oasis abandoned successfully!']);
?>