<?php
session_start();
require_once '../../src/classes/Battle.php';
require_once '../../src/classes/Village.php';

if(!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Not logged in']));
}

$unit_type = isset($_POST['unit_type']) ? $_POST['unit_type'] : '';
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
$village_id = isset($_POST['village_id']) ? intval($_POST['village_id']) : 0;

if(empty($unit_type)) {
    die(json_encode(['success' => false, 'message' => 'No unit selected']));
}

if($village_id == 0 && isset($_SESSION['current_village_id'])) {
    $village_id = $_SESSION['current_village_id'];
}

$battle = new Battle();
$cost = $battle->getTrainingCost($unit_type);
$time = $battle->getTrainingTime($unit_type);

// Get current resources
$village = new Village();
$resources = $village->getResources($village_id);

$total_cost = [
    'wood' => $cost['wood'] * $quantity,
    'clay' => $cost['clay'] * $quantity,
    'iron' => $cost['iron'] * $quantity,
    'crop' => $cost['crop'] * $quantity
];

function formatTime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    if($hours > 0) {
        return $hours . 'h ' . $minutes . 'm';
    }
    return $minutes . 'm';
}

function getUnitDisplayName($unit_type) {
    $names = [
        'legionnaire' => 'Legionnaire',
        'praetorian' => 'Praetorian',
        'club_swinger' => 'Club Swinger',
        'spearman' => 'Spearman',
        'phalanx' => 'Phalanx',
        'swordsman' => 'Swordsman'
    ];
    return $names[$unit_type] ?? ucfirst(str_replace('_', ' ', $unit_type));
}

echo json_encode([
    'success' => true,
    'cost' => $total_cost,
    'resources' => [
        'wood' => $resources['wood'],
        'clay' => $resources['clay'],
        'iron' => $resources['iron'],
        'crop' => $resources['crop']
    ],
    'has_wood' => $resources['wood'] >= $total_cost['wood'],
    'has_clay' => $resources['clay'] >= $total_cost['clay'],
    'has_iron' => $resources['iron'] >= $total_cost['iron'],
    'has_crop' => $resources['crop'] >= $total_cost['crop'],
    'time_seconds' => $time * $quantity,
    'time_formatted' => formatTime($time * $quantity),
    'unit_name' => getUnitDisplayName($unit_type)
]);
?>