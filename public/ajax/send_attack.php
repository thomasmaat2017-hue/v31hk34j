<?php
session_start();
require_once '../../src/classes/Battle.php';
require_once '../../src/config/database.php';

if(!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

$village_id = isset($_POST['village_id']) ? intval($_POST['village_id']) : 0;
$target_x = isset($_POST['target_x']) ? intval($_POST['target_x']) : 0;
$target_y = isset($_POST['target_y']) ? intval($_POST['target_y']) : 0;

// Get target village
$database = new Database();
$conn = $database->getConnection();
$query = "SELECT id FROM villages WHERE x = :x AND y = :y";
$stmt = $conn->prepare($query);
$stmt->bindParam(':x', $target_x);
$stmt->bindParam(':y', $target_y);
$stmt->execute();
$target = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$target) {
    die(json_encode(['success' => false, 'message' => 'Target village not found']));
}

$units = [];
if(isset($_POST['units']) && is_array($_POST['units'])) {
    foreach($_POST['units'] as $unit_type => $count) {
        $count = intval($count);
        if($count > 0) {
            $units[$unit_type] = $count;
        }
    }
}

if(empty($units)) {
    die(json_encode(['success' => false, 'message' => 'No units selected']));
}

$battle = new Battle();
$result = $battle->sendAttack($village_id, $target['id'], $units, $_SESSION['user_id']);

echo json_encode($result);
?>