<?php
session_start();
require_once '../../src/classes/User.php';

if(!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

$village_id = isset($_POST['village_id']) ? intval($_POST['village_id']) : 0;

if($village_id > 0) {
    // Verify village belongs to user
    $user = new User();
    $user->id = $_SESSION['user_id'];
    $villages = $user->getVillages();
    
    $valid = false;
    foreach($villages as $v) {
        if($v['id'] == $village_id) {
            $valid = true;
            break;
        }
    }
    
    if($valid) {
        $_SESSION['current_village_id'] = $village_id;
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid village']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No village specified']);
}
?>