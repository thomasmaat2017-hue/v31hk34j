<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once '../../src/classes/Building.php';
require_once '../../src/classes/User.php';
require_once '../../src/config/database.php';

if(!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

$building_type = isset($_POST['building_type']) ? $_POST['building_type'] : '';
$village_id = isset($_POST['village_id']) ? intval($_POST['village_id']) : 0;

if(empty($building_type) || $village_id == 0) {
    die(json_encode(['success' => false, 'message' => 'Missing parameters']));
}

// Verify village belongs to user
$user = new User();
$user->id = $_SESSION['user_id'];
$villages = $user->getVillages();
$village_ids = array_column($villages, 'id');

if(!in_array($village_id, $village_ids)) {
    die(json_encode(['success' => false, 'message' => 'Invalid village']));
}

$building = new Building();
$result = $building->startUpgrade($village_id, $building_type);

if($result) {
    // Get the upgrade time to display
    $database = new Database();
    $conn = $database->getConnection();
    $query = "SELECT level FROM buildings WHERE village_id = :village_id AND building_type = :building_type";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':village_id', $village_id);
    $stmt->bindParam(':building_type', $building_type);
    $stmt->execute();
    $building_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $time = $building->getUpgradeTime($building_type, $building_data['level']);
    $hours = floor($time / 3600);
    $minutes = floor(($time % 3600) / 60);
    
    echo json_encode([
        'success' => true, 
        'time_formatted' => ($hours > 0 ? $hours . 'h ' : '') . $minutes . 'm'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Cannot upgrade building. Check resources and requirements.']);
}
?>