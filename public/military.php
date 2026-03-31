<?php
session_start();
require_once '../src/classes/User.php';
require_once '../src/classes/Village.php';
require_once '../src/classes/Battle.php';

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
$troops = $village->getTroops($current_village_id);
$battle = new Battle();
$training_queue = $village->getTrainingQueue($current_village_id);
$queue_count = $village->getTrainingQueueCount($current_village_id);
$total_training_time = $village->getTrainingTimeRemaining($current_village_id);

// Handle troop training
$error = '';
$message = '';

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['train'])) {
    $unit_type = $_POST['unit_type'];
    $quantity = intval($_POST['quantity']);
    
    $cost = $battle->getTrainingCost($unit_type);
    $total_cost = [
        'wood' => $cost['wood'] * $quantity,
        'clay' => $cost['clay'] * $quantity,
        'iron' => $cost['iron'] * $quantity,
        'crop' => $cost['crop'] * $quantity
    ];
    
    if($resources['wood'] >= $total_cost['wood'] && 
       $resources['clay'] >= $total_cost['clay'] && 
       $resources['iron'] >= $total_cost['iron'] && 
       $resources['crop'] >= $total_cost['crop']) {
        
        $training_time = $battle->getTrainingTime($unit_type) * $quantity;
        $village->deductResources($current_village_id, $total_cost);
        $village->addTrainingQueue($current_village_id, $unit_type, $quantity, $training_time);
        
        $message = "✅ Training started! {$quantity}x " . getUnitDisplayName($unit_type) . " will be ready in " . formatTime($training_time);
        $resources = $village->getResources($current_village_id);
        $troops = $village->getTroops($current_village_id);
        $training_queue = $village->getTrainingQueue($current_village_id);
    } else {
        $error = "❌ Not enough resources!";
    }
}

// Helper functions
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

function getUnitDisplayName($unit_type) {
    $names = [
        'legionnaire' => 'Legionnaire', 'praetorian' => 'Praetorian',
        'club_swinger' => 'Club Swinger', 'spearman' => 'Spearman',
        'phalanx' => 'Phalanx', 'swordsman' => 'Swordsman',
        'equites_legati' => 'Equites Legati', 'paladin' => 'Paladin'
    ];
    return $names[$unit_type] ?? ucfirst(str_replace('_', ' ', $unit_type));
}

function formatResourceCost($amount) {
    if($amount >= 1000000) return number_format($amount / 1000000, 1) . 'M';
    if($amount >= 1000) return number_format($amount / 1000, 1) . 'K';
    return number_format($amount);
}

// Available units based on tribe
$available_units = [];
$tribe = $_SESSION['tribe'];
switch($tribe) {
    case 'roman':
        $available_units = [
            'legionnaire' => ['name' => 'Legionnaire', 'attack' => 40, 'defense' => 35, 'speed' => 6, 'carry' => 50],
            'praetorian' => ['name' => 'Praetorian', 'attack' => 30, 'defense' => 65, 'speed' => 5, 'carry' => 30]
        ];
        break;
    case 'teuton':
        $available_units = [
            'club_swinger' => ['name' => 'Club Swinger', 'attack' => 40, 'defense' => 20, 'speed' => 7, 'carry' => 60],
            'spearman' => ['name' => 'Spearman', 'attack' => 10, 'defense' => 35, 'speed' => 7, 'carry' => 40]
        ];
        break;
    case 'gaul':
        $available_units = [
            'phalanx' => ['name' => 'Phalanx', 'attack' => 15, 'defense' => 40, 'speed' => 7, 'carry' => 35],
            'swordsman' => ['name' => 'Swordsman', 'attack' => 65, 'defense' => 35, 'speed' => 6, 'carry' => 45]
        ];
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Military - Travian Classic</title>
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
        .military-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .military-card h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
            color: #333;
        }
        
        /* Unit selector */
        .unit-selector {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .unit-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .unit-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .unit-card.selected {
            border-color: #5cb85c;
            background: #f0f9f0;
        }
        
        .unit-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }
        
        .unit-stats {
            display: flex;
            gap: 15px;
            margin: 10px 0;
            font-size: 12px;
            color: #666;
        }
        
        .unit-stat {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .unit-cost {
            font-size: 11px;
            color: #888;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid #e0e0e0;
        }
        
        /* Training info */
        .training-info {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .training-stats {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #5cb85c;
        }
        
        .stat-label {
            font-size: 11px;
            color: #666;
        }
        
        /* Cost preview */
        .cost-preview {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .cost-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 10px 0;
        }
        
        .cost-item {
            padding: 8px;
            border-radius: 5px;
            text-align: center;
            font-size: 13px;
        }
        
        .cost-item.has-resource {
            background: #dff0d8;
            color: #3c763d;
        }
        
        .cost-item.missing-resource {
            background: #f2dede;
            color: #a94442;
        }
        
        /* Troops table */
        .troops-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .troops-table th,
        .troops-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .troops-table th {
            background: #f5f5f5;
            font-weight: bold;
            color: #333;
        }
        
        .troops-table tr:hover {
            background: #f9f9f9;
        }
        
        .troop-count {
            font-size: 18px;
            font-weight: bold;
            color: #5cb85c;
        }
        
        /* Queue items */
        .queue-item {
            background: #f9f9f9;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 8px;
            border-left: 4px solid #f39c12;
        }
        
        .queue-unit {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .queue-timer {
            font-family: monospace;
            font-size: 12px;
            color: #f39c12;
            margin-top: 5px;
        }
        
        /* Attack form */
        .attack-form {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .coordinate-inputs {
            display: flex;
            gap: 10px;
        }
        
        .coordinate-inputs input {
            width: 50%;
        }
        
        .troop-selector {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .troop-checkbox {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px;
            margin: 5px 0;
            background: #f9f9f9;
            border-radius: 5px;
        }
        
        .troop-checkbox input {
            width: 60px;
            text-align: center;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #5cb85c;
            color: white;
            width: 100%;
        }
        
        .btn-primary:hover:not(:disabled) {
            background: #4cae4c;
            transform: translateY(-1px);
        }
        
        .btn-primary:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
        .btn-danger {
            background: #d9534f;
            color: white;
            width: 100%;
        }
        
        .btn-danger:hover:not(:disabled) {
            background: #c9302c;
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
        
        .quantity-input {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 15px 0;
        }
        
        .quantity-input input {
            width: 100px;
            text-align: center;
            font-size: 16px;
            padding: 8px;
        }
        
        .quantity-input button {
            width: 40px;
            height: 40px;
            font-size: 18px;
            cursor: pointer;
            background: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        .quantity-input button:hover {
            background: #e0e0e0;
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
            <!-- Left Column - Training Section -->
            <div class="left-column">
                <div class="military-card">
                    <h3>⚔️ Train Troops</h3>
                    
                    <?php if($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if($message): ?>
                        <div class="alert alert-success"><?php echo $message; ?></div>
                    <?php endif; ?>
                    
                    <div class="training-info">
                        <div class="training-stats">
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $queue_count; ?></div>
                                <div class="stat-label">Active Trainings</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">
                                    <?php 
                                    if($total_training_time > 0) {
                                        $hours = floor($total_training_time / 3600);
                                        $minutes = floor(($total_training_time % 3600) / 60);
                                        echo $hours . 'h ' . $minutes . 'm';
                                    } else {
                                        echo '0';
                                    }
                                    ?>
                                </div>
                                <div class="stat-label">Time Remaining</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">⚡</div>
                                <div class="stat-label">
                                    <?php 
                                    if($tribe == 'teuton') echo "20% cheaper";
                                    elseif($tribe == 'roman') echo "10% faster";
                                    else echo "5% faster";
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <form method="POST" action="" id="trainingForm">
                        <div class="unit-selector" id="unitSelector">
                            <?php foreach($available_units as $key => $unit): 
                                $cost = $battle->getTrainingCost($key);
                                $time = $battle->getTrainingTime($key);
                            ?>
                                <div class="unit-card" data-unit="<?php echo $key; ?>" onclick="selectUnit('<?php echo $key; ?>')">
                                    <div class="unit-name"><?php echo $unit['name']; ?></div>
                                    <div class="unit-stats">
                                        <span class="unit-stat">⚔️ Attack: <?php echo $unit['attack']; ?></span>
                                        <span class="unit-stat">🛡️ Defense: <?php echo $unit['defense']; ?></span>
                                        <span class="unit-stat">⚡ Speed: <?php echo $unit['speed']; ?></span>
                                        <span class="unit-stat">📦 Carry: <?php echo $unit['carry']; ?></span>
                                    </div>
                                    <div class="unit-cost">
                                        Cost: 🌲<?php echo formatResourceCost($cost['wood']); ?> 
                                        🧱<?php echo formatResourceCost($cost['clay']); ?> 
                                        ⛏️<?php echo formatResourceCost($cost['iron']); ?> 
                                        🌾<?php echo formatResourceCost($cost['crop']); ?> 
                                        | Time: <?php echo formatTime($time); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <input type="hidden" name="unit_type" id="selected_unit" value="">
                        
                        <div class="quantity-input">
                            <button type="button" onclick="changeQuantity(-1)">-</button>
                            <input type="number" name="quantity" id="quantity" min="1" max="1000" value="1" onchange="updateCostPreview()">
                            <button type="button" onclick="changeQuantity(1)">+</button>
                        </div>
                        
                        <div id="costPreview" class="cost-preview" style="display: none;">
                            <strong>💰 Total Cost:</strong>
                            <div class="cost-grid" id="costGrid"></div>
                            <div>⏱️ Total Time: <strong id="totalTime">0</strong></div>
                        </div>
                        
                        <button type="submit" name="train" class="btn btn-primary" id="trainBtn" disabled>Select a unit to train</button>
                    </form>
                </div>
            </div>
            
            <!-- Center Column - Your Troops -->
            <div class="center-column">
                <div class="military-card">
                    <h3>🛡️ Your Army</h3>
                    <?php if(empty($troops)): ?>
                        <p style="text-align: center; padding: 40px; color: #999;">No troops trained yet.<br>Select a unit above to build your army!</p>
                    <?php else: ?>
                        <table class="troops-table">
                            <thead>
                                <tr><th>Unit</th><th>Quantity</th><th>Attack</th><th>Defense</th><th>Speed</th><th>Carry</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($troops as $unit_type => $count): ?>
                                    <?php if($count > 0): ?>
                                        <tr>
                                            <td><strong><?php echo getUnitDisplayName($unit_type); ?></strong></td>
                                            <td class="troop-count"><?php echo number_format($count); ?></td>
                                            <td><?php echo $battle->getUnitStat($unit_type, 'attack'); ?></td>
                                            <td><?php echo $battle->getUnitStat($unit_type, 'defense_infantry'); ?></td>
                                            <td><?php echo $battle->getUnitStat($unit_type, 'speed'); ?></td>
                                            <td><?php echo $battle->getUnitStat($unit_type, 'carry'); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Right Column - Queue & Attack -->
            <div class="right-column">
                <div class="military-card">
                    <h3>📋 Training Queue</h3>
                    <?php if(empty($training_queue)): ?>
                        <p style="text-align: center; padding: 20px; color: #999;">No units in training</p>
                    <?php else: ?>
                        <?php foreach($training_queue as $item): ?>
                            <div class="queue-item">
                                <div class="queue-unit">🔨 <?php echo $item['quantity']; ?>x <?php echo getUnitDisplayName($item['unit_type']); ?></div>
                                <div class="queue-timer" data-endtime="<?php echo strtotime($item['end_time']); ?>">Loading...</div>
                                <small>Started: <?php echo date('H:i:s', strtotime($item['start_time'])); ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div class="military-card">
                    <h3>⚔️ Launch Attack</h3>
                    <form id="attackForm">
                        <div class="form-group">
                            <label>Target Coordinates:</label>
                            <div class="coordinate-inputs">
                                <input type="number" name="target_x" placeholder="X" required>
                                <input type="number" name="target_y" placeholder="Y" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Select Troops:</label>
                            <div class="troop-selector">
                                <?php if(empty($troops)): ?>
                                    <p style="color: #999;">No troops available</p>
                                <?php else: ?>
                                    <?php foreach($troops as $unit_type => $count): ?>
                                        <?php if($count > 0): ?>
                                            <div class="troop-checkbox">
                                                <input type="number" name="units[<?php echo $unit_type; ?>]" 
                                                       min="0" max="<?php echo $count; ?>" value="0" style="width: 70px;">
                                                <span><?php echo getUnitDisplayName($unit_type); ?></span>
                                                <span style="margin-left: auto;">(<?php echo number_format($count); ?> available)</span>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-danger" <?php echo empty($troops) ? 'disabled' : ''; ?>>
                            ⚔️ Send Attack
                        </button>
                    </form>
                </div>
                
                <div class="military-card">
                    <h3>🗺️ Navigation</h3>
                    <a href="village.php" style="display: block; padding: 8px 0; color: #667eea; text-decoration: none;">🏠 Back to Village</a>
                    <a href="map.php" style="display: block; padding: 8px 0; color: #667eea; text-decoration: none;">🗺️ World Map</a>
                    <a href="alliance.php" style="display: block; padding: 8px 0; color: #667eea; text-decoration: none;">🤝 Alliance</a>
                    <a href="messages.php" style="display: block; padding: 8px 0; color: #667eea; text-decoration: none;">📬 Messages</a>
                    <a href="rankings.php" style="display: block; padding: 8px 0; color: #667eea; text-decoration: none;">🏆 Rankings</a>
                </div>
            </div>
        </div>
    </div>
    
    <div id="notification" class="game-notification"></div>
    
    <script>
        let currentUnit = null;
        const unitData = <?php echo json_encode($available_units); ?>;
        const currentResources = {
            wood: <?php echo floor($resources['wood']); ?>,
            clay: <?php echo floor($resources['clay']); ?>,
            iron: <?php echo floor($resources['iron']); ?>,
            crop: <?php echo floor($resources['crop']); ?>
        };
        
        function selectUnit(unitKey) {
            currentUnit = unitKey;
            document.getElementById('selected_unit').value = unitKey;
            
            // Update UI
            document.querySelectorAll('.unit-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.querySelector(`[data-unit="${unitKey}"]`).classList.add('selected');
            
            // Enable train button
            document.getElementById('trainBtn').disabled = false;
            document.getElementById('trainBtn').textContent = 'Train Units';
            
            // Update cost preview
            updateCostPreview();
        }
        
        function changeQuantity(delta) {
            const input = document.getElementById('quantity');
            let newVal = parseInt(input.value) + delta;
            if(newVal < 1) newVal = 1;
            if(newVal > 1000) newVal = 1000;
            input.value = newVal;
            updateCostPreview();
        }
        
        function updateCostPreview() {
            if(!currentUnit) return;
            
            const quantity = parseInt(document.getElementById('quantity').value) || 0;
            
            fetch('ajax/get_training_cost.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `unit_type=${currentUnit}&quantity=${quantity}&village_id=<?php echo $current_village_id; ?>`
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    const preview = document.getElementById('costPreview');
                    const costGrid = document.getElementById('costGrid');
                    const totalTime = document.getElementById('totalTime');
                    
                    preview.style.display = 'block';
                    
                    costGrid.innerHTML = `
                        <div class="cost-item ${data.has_wood ? 'has-resource' : 'missing-resource'}">
                            🌲 Wood: ${formatNumber(data.cost.wood)}<br>
                            <small>Have: ${formatNumber(data.resources.wood)}</small>
                        </div>
                        <div class="cost-item ${data.has_clay ? 'has-resource' : 'missing-resource'}">
                            🧱 Clay: ${formatNumber(data.cost.clay)}<br>
                            <small>Have: ${formatNumber(data.resources.clay)}</small>
                        </div>
                        <div class="cost-item ${data.has_iron ? 'has-resource' : 'missing-resource'}">
                            ⛏️ Iron: ${formatNumber(data.cost.iron)}<br>
                            <small>Have: ${formatNumber(data.resources.iron)}</small>
                        </div>
                        <div class="cost-item ${data.has_crop ? 'has-resource' : 'missing-resource'}">
                            🌾 Crop: ${formatNumber(data.cost.crop)}<br>
                            <small>Have: ${formatNumber(data.resources.crop)}</small>
                        </div>
                    `;
                    
                    totalTime.textContent = data.time_formatted;
                    
                    const trainBtn = document.getElementById('trainBtn');
                    if(data.has_wood && data.has_clay && data.has_iron && data.has_crop && quantity > 0) {
                        trainBtn.disabled = false;
                        trainBtn.textContent = `Train ${quantity}x ${data.unit_name}`;
                    } else {
                        trainBtn.disabled = true;
                        trainBtn.textContent = 'Insufficient Resources';
                    }
                }
            });
        }
        
        function formatNumber(num) {
            if(num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
            if(num >= 1000) return (num / 1000).toFixed(1) + 'K';
            return num.toString();
        }
        
        function showNotification(message, type) {
            const notification = document.getElementById('notification');
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
        
        // Update timers
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
        
        // Attack form submission
        document.getElementById('attackForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('village_id', <?php echo $current_village_id; ?>);
            formData.append('action', 'attack');
            
            let hasTroops = false;
            for (let [key, value] of formData.entries()) {
                if(key.startsWith('units[') && parseInt(value) > 0) {
                    hasTroops = true;
                    break;
                }
            }
            
            if(!hasTroops) {
                showNotification('Please select at least one troop to send!', 'error');
                return;
            }
            
            showNotification('Sending attack...', 'success');
            
            fetch('ajax/send_attack.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    showNotification(`✅ Attack sent! Arrival in ${Math.floor(data.travel_time / 3600)}h ${Math.floor((data.travel_time % 3600) / 60)}m`, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showNotification('❌ ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('❌ Error sending attack', 'error');
            });
        });
        
        // Auto-refresh resources
        function refreshResources() {
            fetch('ajax/resources.php?village_id=<?php echo $current_village_id; ?>')
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        document.getElementById('wood-resource').textContent = Math.floor(data.resources.wood);
                        document.getElementById('clay-resource').textContent = Math.floor(data.resources.clay);
                        document.getElementById('iron-resource').textContent = Math.floor(data.resources.iron);
                        document.getElementById('crop-resource').textContent = Math.floor(data.resources.crop);
                        
                        currentResources.wood = data.resources.wood;
                        currentResources.clay = data.resources.clay;
                        currentResources.iron = data.resources.iron;
                        currentResources.crop = data.resources.crop;
                        
                        if(currentUnit) updateCostPreview();
                    }
                });
        }
        
        // Initialize
        setInterval(updateTimers, 1000);
        setInterval(refreshResources, 30000);
        updateTimers();
        
        // Quantity input listener
        document.getElementById('quantity').addEventListener('input', function() {
            updateCostPreview();
        });
    </script>
</body>
</html>