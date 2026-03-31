<?php
session_start();
require_once '../src/classes/User.php';
require_once '../src/classes/Village.php';
require_once '../src/classes/Building.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user = new User();
$user->id = $_SESSION['user_id'];
$user->username = $_SESSION['username'];
$user->tribe = $_SESSION['tribe'];

include_once 'includes/header.php';

$current_village_id = $_SESSION['current_village_id'];
$village = new Village();
$resources = $village->getResources($current_village_id);
$buildings = $village->getBuildings($current_village_id);
$queue = $village->getBuildingQueue($current_village_id);
$building = new Building();

function getBuildingName($type) {
    $names = [
        'main_building' => 'Main Building',
        'woodcutter' => 'Woodcutter',
        'clay_pit' => 'Clay Pit',
        'iron_mine' => 'Iron Mine',
        'farm' => 'Farm',
        'warehouse' => 'Warehouse',
        'granary' => 'Granary',
        'barracks' => 'Barracks',
        'stable' => 'Stable',
        'marketplace' => 'Marketplace',
        'embassy' => 'Embassy',
        'smithy' => 'Smithy',
        'academy' => 'Academy',
        'wall' => 'Wall'
    ];
    if(isset($names[$type])) {
        return $names[$type];
    }
    if(empty($type)) {
        return 'Building';
    }
    return ucfirst(str_replace('_', ' ', $type));
}

function getBuildingIcon($type) {
    $icons = [
        'main_building' => '🏛️',
        'woodcutter' => '🌲',
        'clay_pit' => '🧱',
        'iron_mine' => '⛏️',
        'farm' => '🌾',
        'warehouse' => '📦',
        'granary' => '🌽',
        'barracks' => '⚔️',
        'stable' => '🐎',
        'marketplace' => '💰',
        'embassy' => '🤝',
        'smithy' => '🔨',
        'academy' => '📚',
        'wall' => '🧱'
    ];
    return $icons[$type] ?? '🏠';
}

function formatResourceCost($amount) {
    if($amount >= 1000000) return number_format($amount / 1000000, 1) . 'M';
    if($amount >= 1000) return number_format($amount / 1000, 1) . 'K';
    return number_format($amount);
}

function formatTime($seconds) {
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    if($hours > 0) return $hours . 'h ' . $minutes . 'm';
    return $minutes . 'm';
}

function formatTimeRemaining($timestamp) {
    if(!$timestamp) return 'Unknown';
    $now = new DateTime();
    $end = new DateTime($timestamp);
    if($now > $end) return 'Complete';
    $interval = $now->diff($end);
    if($interval->days > 0) return $interval->format('%d days %h hours');
    if($interval->h > 0) return $interval->format('%h hours %i minutes');
    if($interval->i > 0) return $interval->format('%i minutes %s seconds');
    return $interval->format('%s seconds');
}

function getBuildingDescription($type) {
    $descriptions = [
        'main_building' => 'The center of your village. Required for most buildings. Increases construction speed.',
        'woodcutter' => 'Produces wood. Each level increases production by 5 per hour.',
        'clay_pit' => 'Produces clay. Each level increases production by 5 per hour.',
        'iron_mine' => 'Produces iron. Each level increases production by 5 per hour.',
        'farm' => 'Produces crop. Each level increases production by 5 per hour.',
        'warehouse' => 'Increases storage capacity for wood, clay, and iron by 2000 per level.',
        'granary' => 'Increases storage capacity for crop by 2000 per level.',
        'barracks' => 'Allows training of infantry units. Higher levels train troops faster.',
        'stable' => 'Allows training of cavalry units. Faster movement on the world map.',
        'marketplace' => 'Allows trading resources with other players. Each level increases merchant capacity.',
        'embassy' => 'Allows creating or joining alliances. Higher levels allow more alliance members.',
        'smithy' => 'Upgrades weapon and armor of troops. Increases attack and defense stats.',
        'academy' => 'Researches technologies and improvements for your village.',
        'wall' => 'Defensive wall that protects your village. Increases defense bonus by 5% per level.'
    ];
    return $descriptions[$type] ?? 'A useful building for your village.';
}

// Get prerequisites from building class
function checkBuildingPrerequisites($building_type, $buildings, $building_class) {
    $prerequisites = [
        'barracks' => ['main_building' => 3],
        'stable' => ['main_building' => 5, 'barracks' => 3],
        'marketplace' => ['main_building' => 3],
        'embassy' => ['main_building' => 1],
        'smithy' => ['main_building' => 3, 'barracks' => 1],
        'academy' => ['main_building' => 3, 'barracks' => 3],
        'wall' => ['main_building' => 1]
    ];
    
    if(!isset($prerequisites[$building_type])) {
        return ['met' => true, 'missing' => []];
    }
    
    $missing = [];
    foreach($prerequisites[$building_type] as $req_building => $req_level) {
        $found_level = 0;
        foreach($buildings as $b) {
            if($b['building_type'] == $req_building) {
                $found_level = $b['level'];
                break;
            }
        }
        if($found_level < $req_level) {
            $missing[] = getBuildingName($req_building) . " (Required: Level " . $req_level . ", Current: Level " . $found_level . ")";
        }
    }
    
    return ['met' => empty($missing), 'missing' => $missing];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Village - Travian Classic</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .game-content {
            display: grid;
            grid-template-columns: 300px 1fr 300px;
            gap: 20px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        /* Cards styling */
        .village-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .village-card h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
            color: #333;
        }
        
        /* Village info */
        .village-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 15px;
        }
        
        .village-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 15px 0;
        }
        
        .stat-box {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        
        /* Production stats */
        .production-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-top: 10px;
        }
        
        .production-item {
            display: flex;
            justify-content: space-between;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .production-value {
            font-weight: bold;
            color: #5cb85c;
        }
        
        /* Storage bars */
        .storage-bar {
            margin: 10px 0;
        }
        
        .storage-label {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .bar-container {
            background: #e0e0e0;
            border-radius: 10px;
            overflow: hidden;
            height: 20px;
        }
        
        .bar-fill {
            background: #5cb85c;
            height: 100%;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 5px;
            color: white;
            font-size: 10px;
        }
        
        /* Buildings grid */
        .buildings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
        }
        
        .building-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .building-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .building-card.upgrading {
            background: #fff3e0;
            border-color: #f39c12;
        }
        
        .building-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }
        
        .building-icon {
            font-size: 2rem;
        }
        
        .building-info {
            flex: 1;
        }
        
        .building-name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        
        .building-level {
            font-size: 12px;
            color: #666;
        }
        
        .building-description {
            font-size: 11px;
            color: #888;
            margin: 8px 0;
            line-height: 1.3;
        }
        
        .upgrade-cost {
            background: #fff;
            padding: 10px;
            border-radius: 6px;
            margin: 10px 0;
            font-size: 12px;
        }
        
        .cost-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 5px;
            margin: 8px 0;
        }
        
        .cost-item {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
        }
        
        .cost-item.has-resource {
            background: #dff0d8;
            color: #3c763d;
        }
        
        .cost-item.missing-resource {
            background: #f2dede;
            color: #a94442;
        }
        
        .build-time {
            font-size: 11px;
            color: #f39c12;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px solid #e0e0e0;
        }
        
        .prerequisites-warning {
            background: #f2dede;
            color: #a94442;
            padding: 8px;
            border-radius: 5px;
            margin: 10px 0;
            font-size: 11px;
        }
        
        .prerequisites-warning strong {
            display: block;
            margin-bottom: 5px;
        }
        
        .btn-upgrade {
            background: #5cb85c;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn-upgrade:hover:not(:disabled) {
            background: #4cae4c;
            transform: translateY(-1px);
        }
        
        .btn-upgrade:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .upgrading-status {
            background: #fff3e0;
            color: #f39c12;
            padding: 8px;
            border-radius: 5px;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        /* Queue items */
        .queue-item {
            background: #f9f9f9;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid #f39c12;
        }
        
        .queue-building {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .queue-timer {
            font-family: monospace;
            font-size: 12px;
            color: #f39c12;
            margin-top: 5px;
        }
        
        /* Navigation links */
        .nav-links {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .nav-link {
            display: block;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            text-decoration: none;
            color: #667eea;
            transition: all 0.3s;
            text-align: center;
        }
        
        .nav-link:hover {
            background: #667eea;
            color: white;
            transform: translateX(5px);
        }
        
        .tribe-bonus {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        
        .alert {
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        
        .alert-error {
            background: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        
        .alert-success {
            background: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        
        @media (max-width: 768px) {
            .game-content {
                grid-template-columns: 1fr;
            }
            
            .cost-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="game-container">
        <div class="game-content">
<!-- Left Column - Village Info -->
<div class="left-column">
    <div class="village-card">
        <h3>🏠 Village Info1</h3>
        <div class="village-name"><?php echo htmlspecialchars($villages[0]['name']); ?></div>
        
        <div class="village-stats">
            <div class="stat-box">
                <div class="stat-value"><?php echo $villages[0]['population']; ?></div>
                <div class="stat-label">Population</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo $villages[0]['loyalty']; ?>%</div>
                <div class="stat-label">Loyalty</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">(<?php echo $villages[0]['x']; ?>, <?php echo $villages[0]['y']; ?>)</div>
                <div class="stat-label">Coordinates</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo $user->tribe; ?></div>
                <div class="stat-label">Tribe</div>
            </div>
        </div>
        
        <?php
        // Get population breakdown
        $pop_breakdown = $building->getPopulationBreakdown($current_village_id);
        ?>
        <details style="margin-top: 15px;">
            <summary style="cursor: pointer; color: #667eea; font-size: 12px;">📊 Population Breakdown</summary>
            <div style="margin-top: 10px; font-size: 11px;">
                <?php foreach($pop_breakdown['breakdown'] as $item): ?>
                    <div style="display: flex; justify-content: space-between; padding: 3px 0;">
                        <span><?php echo getBuildingName($item['type']); ?> Lvl <?php echo $item['level']; ?></span>
                        <span>+<?php echo $item['contribution']; ?></span>
                    </div>
                <?php endforeach; ?>
                <div style="border-top: 1px solid #ddd; margin-top: 5px; padding-top: 5px; font-weight: bold; display: flex; justify-content: space-between;">
                    <span>Total Population</span>
                    <span><?php echo $pop_breakdown['total']; ?></span>
                </div>
            </div>
        </details>
    </div>
    
    <div class="village-card">
        <h3>📊 Production (per hour)</h3>
        <div class="production-grid">
            <div class="production-item">
                <span>🌲 Wood</span>
                <span class="production-value">+<?php echo $resources['wood_production']; ?></span>
            </div>
            <div class="production-item">
                <span>🧱 Clay</span>
                <span class="production-value">+<?php echo $resources['clay_production']; ?></span>
            </div>
            <div class="production-item">
                <span>⛏️ Iron</span>
                <span class="production-value">+<?php echo $resources['iron_production']; ?></span>
            </div>
            <div class="production-item">
                <span>🌾 Crop</span>
                <span class="production-value">+<?php echo $resources['crop_production']; ?></span>
            </div>
        </div>
    </div>
    
    <div class="village-card">
        <h3>📦 Storage</h3>
        <?php
        $warehouse_level = 0;
        $granary_level = 0;
        foreach($buildings as $b) {
            if($b['building_type'] == 'warehouse') $warehouse_level = $b['level'];
            if($b['building_type'] == 'granary') $granary_level = $b['level'];
        }
        $storage_capacity = 5000 + ($warehouse_level * 2000);
        $granary_capacity = 5000 + ($granary_level * 2000);
        $wood_pct = ($resources['wood'] / $storage_capacity) * 100;
        $clay_pct = ($resources['clay'] / $storage_capacity) * 100;
        $iron_pct = ($resources['iron'] / $storage_capacity) * 100;
        $crop_pct = ($resources['crop'] / $granary_capacity) * 100;
        ?>
        <div class="storage-bar">
            <div class="storage-label"><span>🌲 Wood</span><span><?php echo floor($resources['wood']); ?> / <?php echo number_format($storage_capacity); ?></span></div>
            <div class="bar-container"><div class="bar-fill" style="width: <?php echo min(100, $wood_pct); ?>%;"><?php echo floor($wood_pct); ?>%</div></div>
        </div>
        <div class="storage-bar">
            <div class="storage-label"><span>🧱 Clay</span><span><?php echo floor($resources['clay']); ?> / <?php echo number_format($storage_capacity); ?></span></div>
            <div class="bar-container"><div class="bar-fill" style="width: <?php echo min(100, $clay_pct); ?>%;"><?php echo floor($clay_pct); ?>%</div></div>
        </div>
        <div class="storage-bar">
            <div class="storage-label"><span>⛏️ Iron</span><span><?php echo floor($resources['iron']); ?> / <?php echo number_format($storage_capacity); ?></span></div>
            <div class="bar-container"><div class="bar-fill" style="width: <?php echo min(100, $iron_pct); ?>%;"><?php echo floor($iron_pct); ?>%</div></div>
        </div>
        <div class="storage-bar">
            <div class="storage-label"><span>🌾 Crop</span><span><?php echo floor($resources['crop']); ?> / <?php echo number_format($granary_capacity); ?></span></div>
            <div class="bar-container"><div class="bar-fill" style="width: <?php echo min(100, $crop_pct); ?>%;"><?php echo floor($crop_pct); ?>%</div></div>
        </div>
    </div>
</div>
            
            <!-- Center Column - Buildings -->
            <div class="center-column">
                <div class="village-card">
                    <h3>🏗️ Buildings</h3>
                    <div class="buildings-grid">
                        <?php 
                        // Complete building order with ALL buildings
                        $building_order = [
                            'main_building',
                            'woodcutter', 
                            'clay_pit', 
                            'iron_mine', 
                            'farm',
                            'warehouse', 
                            'granary',
                            'barracks',
                            'stable',
                            'smithy',
                            'academy',
                            'marketplace',
                            'embassy',
                            'wall'
                        ];
                        
                        foreach($building_order as $type):
                            $data = null;
                            foreach($buildings as $b) {
                                if($b['building_type'] == $type) {
                                    $data = $b;
                                    break;
                                }
                            }
                            // If building doesn't exist in database, skip it
                            if(!$data) continue;
                            
                            $cost = $building->getUpgradeCost($type, $data['level']);
                            $time = $building->getUpgradeTime($type, $data['level']);
                            $has_resources = ($resources['wood'] >= $cost['wood'] && 
                                             $resources['clay'] >= $cost['clay'] && 
                                             $resources['iron'] >= $cost['iron'] && 
                                             $resources['crop'] >= $cost['crop']);
                            
                            $prereq_check = checkBuildingPrerequisites($type, $buildings, $building);
                            $prerequisites_met = $prereq_check['met'];
                            $missing_prereqs = $prereq_check['missing'];
                        ?>
                            <div class="building-card <?php echo $data['is_upgrading'] ? 'upgrading' : ''; ?>" data-building="<?php echo $type; ?>">
                                <div class="building-header">
                                    <div class="building-icon"><?php echo getBuildingIcon($type); ?></div>
                                    <div class="building-info">
                                        <div class="building-name"><?php echo getBuildingName($type); ?></div>
                                        <div class="building-level">Level <?php echo $data['level']; ?></div>
                                    </div>
                                </div>
                                
                                <div class="building-description"><?php echo getBuildingDescription($type); ?></div>
                                
                                <?php if($data['is_upgrading']): ?>
                                    <div class="upgrading-status">
                                        🔨 Upgrading to level <?php echo $data['level'] + 1; ?>
                                    </div>
                                <?php else: ?>
                                    <?php if(!$prerequisites_met): ?>
                                        <div class="prerequisites-warning">
                                            <strong>❌ Requirements not met:</strong>
                                            <?php foreach($missing_prereqs as $req): ?>
                                                • <?php echo $req; ?><br>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="upgrade-cost">
                                            <strong>💰 Upgrade Cost to Level <?php echo $data['level'] + 1; ?>:</strong>
                                            <div class="cost-grid">
                                                <div class="cost-item <?php echo $resources['wood'] >= $cost['wood'] ? 'has-resource' : 'missing-resource'; ?>">
                                                    🌲 Wood: <?php echo formatResourceCost($cost['wood']); ?>
                                                    <?php if($resources['wood'] < $cost['wood']): ?>
                                                        <br><small>Need: <?php echo formatResourceCost($cost['wood'] - $resources['wood']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="cost-item <?php echo $resources['clay'] >= $cost['clay'] ? 'has-resource' : 'missing-resource'; ?>">
                                                    🧱 Clay: <?php echo formatResourceCost($cost['clay']); ?>
                                                    <?php if($resources['clay'] < $cost['clay']): ?>
                                                        <br><small>Need: <?php echo formatResourceCost($cost['clay'] - $resources['clay']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="cost-item <?php echo $resources['iron'] >= $cost['iron'] ? 'has-resource' : 'missing-resource'; ?>">
                                                    ⛏️ Iron: <?php echo formatResourceCost($cost['iron']); ?>
                                                    <?php if($resources['iron'] < $cost['iron']): ?>
                                                        <br><small>Need: <?php echo formatResourceCost($cost['iron'] - $resources['iron']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="cost-item <?php echo $resources['crop'] >= $cost['crop'] ? 'has-resource' : 'missing-resource'; ?>">
                                                    🌾 Crop: <?php echo formatResourceCost($cost['crop']); ?>
                                                    <?php if($resources['crop'] < $cost['crop']): ?>
                                                        <br><small>Need: <?php echo formatResourceCost($cost['crop'] - $resources['crop']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="build-time">
                                                ⏱️ Construction Time: <?php echo formatTime($time); ?>
                                            </div>
                                        </div>
                                        
                                        <button onclick="upgradeBuilding('<?php echo $type; ?>')" 
                                                class="btn-upgrade" 
                                                <?php echo !$has_resources ? 'disabled' : ''; ?>>
                                            <?php if($has_resources): ?>
                                                Upgrade to Level <?php echo $data['level'] + 1; ?>
                                            <?php else: ?>
                                                Insufficient Resources
                                            <?php endif; ?>
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Queue & Navigation -->
            <div class="right-column">
                <div class="village-card">
                    <h3>🏗️ Construction Queue</h3>
                    <?php if(empty($queue)): ?>
                        <p style="text-align: center; padding: 20px; color: #999;">No buildings in queue</p>
                    <?php else: ?>
                        <?php foreach($queue as $q): ?>
                            <div class="queue-item">
                                <div class="queue-building">🔨 <?php echo getBuildingName($q['building_type']); ?> to level <?php echo $q['target_level']; ?></div>
                                <div class="queue-timer" data-endtime="<?php echo strtotime($q['end_time']); ?>">Loading...</div>
                                <small>Started: <?php echo date('H:i:s', strtotime($q['start_time'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="village-card">
                    <h3>🗺️ Navigation</h3>
                    <div class="nav-links">
                        <a href="map.php" class="nav-link">🗺️ World Map</a>
                        <a href="military.php" class="nav-link">⚔️ Military</a>
                        <a href="alliance.php" class="nav-link">🤝 Alliance</a>
                        <a href="messages.php" class="nav-link">📬 Messages</a>
                        <a href="rankings.php" class="nav-link">🏆 Rankings</a>
                    </div>
                </div>
                
                <div class="village-card">
                    <div class="tribe-bonus">
                        <strong>✨ Tribe Bonus</strong><br>
                        <?php if($user->tribe == 'roman'): ?>
                            🏛️ Romans: Buildings cost 10% less resources
                        <?php elseif($user->tribe == 'teuton'): ?>
                            ⚔️ Teutons: Troops are 20% cheaper to train
                        <?php elseif($user->tribe == 'gaul'): ?>
                            🛡️ Gauls: Troops move 20% faster
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div id="notification" class="game-notification"></div>
    
    <script>
        function upgradeBuilding(buildingType) {
            let button = event?.target;
            if(!button || button.disabled) return;
            
            const originalText = button.textContent;
            button.textContent = '⏳ Upgrading...';
            button.disabled = true;
            
            fetch('ajax/upgrade.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'building_type=' + encodeURIComponent(buildingType) + '&village_id=<?php echo $current_village_id; ?>'
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showNotification('✅ Upgrade started! It will complete in ' + data.time_formatted, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showNotification('❌ ' + (data.message || 'Upgrade failed!'), 'error');
                    button.textContent = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('❌ Network error! Please try again.', 'error');
                button.textContent = originalText;
                button.disabled = false;
            });
        }
        
        function showNotification(message, type) {
            let notification = document.getElementById('notification');
            if(!notification) {
                notification = document.createElement('div');
                notification.id = 'notification';
                notification.className = 'game-notification';
                document.body.appendChild(notification);
            }
            notification.textContent = message;
            notification.className = `game-notification ${type}`;
            notification.style.display = 'block';
            
            setTimeout(() => {
                notification.style.opacity = '0';
                setTimeout(() => {
                    notification.style.display = 'none';
                    notification.style.opacity = '1';
                }, 300);
            }, 3000);
        }
        
        function updateTimers() {
            document.querySelectorAll('.queue-timer').forEach(timer => {
                const endTime = parseInt(timer.dataset.endtime);
                if(!isNaN(endTime)) {
                    const now = Math.floor(Date.now() / 1000);
                    const remaining = endTime - now;
                    
                    if(remaining <= 0) {
                        location.reload();
                    } else {
                        const hours = Math.floor(remaining / 3600);
                        const minutes = Math.floor((remaining % 3600) / 60);
                        const seconds = remaining % 60;
                        timer.textContent = `${hours}h ${minutes}m ${seconds}s`;
                    }
                }
            });
        }
        
        function refreshResources() {
            fetch('ajax/resources.php?village_id=<?php echo $current_village_id; ?>')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        const woodEl = document.getElementById('wood-resource');
                        const clayEl = document.getElementById('clay-resource');
                        const ironEl = document.getElementById('iron-resource');
                        const cropEl = document.getElementById('crop-resource');
                        if(woodEl) woodEl.textContent = Math.floor(data.resources.wood);
                        if(clayEl) clayEl.textContent = Math.floor(data.resources.clay);
                        if(ironEl) ironEl.textContent = Math.floor(data.resources.iron);
                        if(cropEl) cropEl.textContent = Math.floor(data.resources.crop);
                    }
                })
                .catch(error => console.error('Error refreshing resources:', error));
        }
        
        setInterval(updateTimers, 1000);
        setInterval(refreshResources, 30000);
        updateTimers();
    </script>
</body>
</html>