<?php
session_start();
require_once '../../src/classes/Village.php';
require_once '../../src/config/database.php';

if(!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

$village_id = isset($_GET['village_id']) ? intval($_GET['village_id']) : 0;

// If no village_id provided, try to get from session
if($village_id == 0 && isset($_SESSION['current_village_id'])) {
    $village_id = $_SESSION['current_village_id'];
}

if($village_id > 0) {
    $village = new Village();
    $village->updateResources($village_id);
    $resources = $village->getResources($village_id);
    
    if($resources) {
        echo json_encode([
            'success' => true,
            'resources' => [
                'wood' => floor($resources['wood']),
                'clay' => floor($resources['clay']),
                'iron' => floor($resources['iron']),
                'crop' => floor($resources['crop']),
                'wood_production' => $resources['wood_production'],
                'clay_production' => $resources['clay_production'],
                'iron_production' => $resources['iron_production'],
                'crop_production' => $resources['crop_production']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Village not found']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No village specified']);
}
?>