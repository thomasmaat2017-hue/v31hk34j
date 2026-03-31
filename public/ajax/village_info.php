<?php
session_start();
require_once '../../src/config/database.php';

if(!isset($_SESSION['user_id'])) {
    die('Not logged in');
}

$database = new Database();
$conn = $database->getConnection();

$x = isset($_GET['x']) ? intval($_GET['x']) : 0;
$y = isset($_GET['y']) ? intval($_GET['y']) : 0;

// Get village/oasis info
$query = "SELECT mc.*, v.id as village_id, v.name as village_name, v.user_id as owner_id, 
          v.population, v.loyalty, u.username as owner_name, u.tribe as owner_tribe,
          r.wood, r.clay, r.iron, r.crop
          FROM map_cells mc
          LEFT JOIN villages v ON mc.village_id = v.id
          LEFT JOIN users u ON v.user_id = u.id
          LEFT JOIN resources r ON v.id = r.village_id
          WHERE mc.x = :x AND mc.y = :y";

$stmt = $conn->prepare($query);
$stmt->bindParam(':x', $x);
$stmt->bindParam(':y', $y);
$stmt->execute();
$cell = $stmt->fetch(PDO::FETCH_ASSOC);

// Get player's nearest village for distance calculation
$query = "SELECT id, name, x, y FROM villages WHERE user_id = :user_id LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$player_village = $stmt->fetch(PDO::FETCH_ASSOC);

// Calculate distance
$distance = 0;
if($player_village) {
    $dx = abs($player_village['x'] - $x);
    $dy = abs($player_village['y'] - $y);
    $distance = sqrt($dx * $dx + $dy * $dy);
}

// Helper function for formatting
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
    return $names[$type] ?? ucfirst(str_replace('_', ' ', $type));
}

if($cell && $cell['village_id'] && !$cell['oasis_type']):
    // Normal village
?>
    <style>
        .village-info-modal {
            font-family: Arial, sans-serif;
        }
        .village-info-modal h3 {
            color: #333;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .village-stats {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .stat-row {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .stat-row:last-child {
            border-bottom: none;
        }
        .stat-label {
            font-weight: bold;
            color: #666;
        }
        .stat-value {
            color: #333;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-primary {
            background: #5cb85c;
            color: white;
        }
        .btn-primary:hover {
            background: #4cae4c;
            transform: translateY(-1px);
        }
        .btn-danger {
            background: #d9534f;
            color: white;
        }
        .btn-danger:hover {
            background: #c9302c;
        }
        .btn-warning {
            background: #f0ad4e;
            color: white;
        }
        .btn-warning:hover {
            background: #ec971f;
        }
        .btn-info {
            background: #5bc0de;
            color: white;
        }
        .btn-info:hover {
            background: #46b8da;
        }
        .distance-info {
            font-size: 11px;
            color: #888;
            margin-top: 8px;
            text-align: center;
            padding-top: 8px;
            border-top: 1px solid #e0e0e0;
        }
    </style>
    
    <div class="village-info-modal">
        <h3>🏠 <?php echo htmlspecialchars($cell['village_name']); ?></h3>
        
        <div class="village-stats">
            <div class="stat-row">
                <span class="stat-label">Owner:</span>
                <span class="stat-value">
                    <?php echo htmlspecialchars($cell['owner_name']); ?>
                    <?php if($cell['owner_id'] == $_SESSION['user_id']): ?>
                        <span style="color: #5cb85c;"> (You)</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Tribe:</span>
                <span class="stat-value">
                    <?php 
                    $tribe_icon = '';
                    if($cell['owner_tribe'] == 'roman') $tribe_icon = '🏛️';
                    elseif($cell['owner_tribe'] == 'teuton') $tribe_icon = '⚔️';
                    elseif($cell['owner_tribe'] == 'gaul') $tribe_icon = '🛡️';
                    echo $tribe_icon . ' ' . ucfirst($cell['owner_tribe']);
                    ?>
                </span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Population:</span>
                <span class="stat-value"><?php echo number_format($cell['population']); ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Loyalty:</span>
                <span class="stat-value"><?php echo $cell['loyalty']; ?>%</span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Coordinates:</span>
                <span class="stat-value">(<?php echo $x; ?>, <?php echo $y; ?>)</span>
            </div>
            <?php if($distance > 0): ?>
                <div class="distance-info">
                    📏 Distance from your village: <?php echo round($distance, 1); ?> fields
                </div>
            <?php endif; ?>
        </div>
        
        <?php if($cell['owner_id'] == $_SESSION['user_id']): ?>
            <div class="action-buttons">
                <button onclick="window.location.href='village.php?village=<?php echo $cell['village_id']; ?>'" 
                        class="btn btn-primary">🏠 Go to my village</button>
            </div>
        <?php else: ?>
            <div class="action-buttons">
                <button onclick="sendAttack(<?php echo $cell['village_id']; ?>, <?php echo $distance; ?>)" 
                        class="btn btn-danger">⚔️ Attack</button>
                <button onclick="sendRaid(<?php echo $cell['village_id']; ?>, <?php echo $distance; ?>)" 
                        class="btn btn-warning">💰 Raid</button>
                <button onclick="sendSpy(<?php echo $cell['village_id']; ?>, <?php echo $distance; ?>)" 
                        class="btn btn-info">🕵️ Spy</button>
            </div>
        <?php endif; ?>
    </div>
    
<?php elseif($cell && $cell['oasis_type']):
    // Oasis
    $oasis_names = [
        'wood_oasis' => ['name' => 'Wood Oasis', 'icon' => '🌲', 'bonus' => '+25% Wood Production'],
        'clay_oasis' => ['name' => 'Clay Oasis', 'icon' => '🧱', 'bonus' => '+25% Clay Production'],
        'iron_oasis' => ['name' => 'Iron Oasis', 'icon' => '⛏️', 'bonus' => '+25% Iron Production'],
        'crop_oasis' => ['name' => 'Crop Oasis', 'icon' => '🌾', 'bonus' => '+50% Crop Production']
    ];
    $oasis_info = $oasis_names[$cell['oasis_type']];
    
    // Check if oasis has defenders
    $has_defenders = false;
    $defender_count = 0;
    if($cell['village_id']) {
        $query2 = "SELECT SUM(quantity) as total FROM troops WHERE village_id = :village_id";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bindParam(':village_id', $cell['village_id']);
        $stmt2->execute();
        $result = $stmt2->fetch(PDO::FETCH_ASSOC);
        $defender_count = $result['total'] ? $result['total'] : 0;
        $has_defenders = $defender_count > 0;
    }
    
    // Check if player owns this oasis
    $owns_oasis = ($cell['village_id'] && $cell['owner_id'] == $_SESSION['user_id']);
?>
    <div class="village-info-modal">
        <h3><?php echo $oasis_info['icon']; ?> <?php echo $oasis_info['name']; ?></h3>
        
        <div class="village-stats">
            <div class="stat-row">
                <span class="stat-label">Bonus:</span>
                <span class="stat-value" style="color: #5cb85c;"><?php echo $oasis_info['bonus']; ?></span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Coordinates:</span>
                <span class="stat-value">(<?php echo $x; ?>, <?php echo $y; ?>)</span>
            </div>
            <?php if($cell['village_id']): ?>
                <div class="stat-row">
                    <span class="stat-label">Occupied by:</span>
                    <span class="stat-value">
                        <?php if($owns_oasis): ?>
                            <span style="color: #5cb85c;">✓ You</span>
                        <?php else: ?>
                            <span style="color: #d9534f;"><?php echo htmlspecialchars($cell['owner_name']); ?></span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if($has_defenders && !$owns_oasis): ?>
                    <div class="stat-row">
                        <span class="stat-label">Defenders:</span>
                        <span class="stat-value">⚔️ <?php echo number_format($defender_count); ?> troops</span>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="stat-row">
                    <span class="stat-label">Status:</span>
                    <span class="stat-value" style="color: #5cb85c;">✓ Unoccupied</span>
                </div>
            <?php endif; ?>
            <?php if($distance > 0): ?>
                <div class="distance-info">
                    📏 Distance from your village: <?php echo round($distance, 1); ?> fields
                </div>
            <?php endif; ?>
        </div>
        
        <div class="action-buttons">
            <?php if($owns_oasis): ?>
                <button onclick="abandonOasis(<?php echo $cell['village_id']; ?>)" 
                        class="btn btn-warning">🏃 Abandon Oasis</button>
                <button onclick="viewOasisGarrison(<?php echo $cell['village_id']; ?>)" 
                        class="btn btn-info">👥 View Garrison</button>
                        
            <?php elseif($cell['village_id']): ?>
                <!-- Occupied by someone else -->
                <button onclick="attackOasis(<?php echo $cell['village_id']; ?>, <?php echo $distance; ?>, '<?php echo $cell['oasis_type']; ?>')" 
                        class="btn btn-danger">⚔️ Attack & Occupy</button>
                <button onclick="raidOasis(<?php echo $cell['village_id']; ?>, <?php echo $distance; ?>)" 
                        class="btn btn-warning">💰 Raid Oasis</button>
                        
            <?php else: ?>
                <!-- Unoccupied oasis -->
                <button onclick="occupyOasis(<?php echo $x; ?>, <?php echo $y; ?>, '<?php echo $cell['oasis_type']; ?>', <?php echo $distance; ?>)" 
                        class="btn btn-success">🏕️ Occupy Oasis</button>
            <?php endif; ?>
        </div>
        
        <div class="distance-info" style="margin-top: 10px; background: #e8f5e9; border-radius: 5px; padding: 8px; text-align: center;">
            ✨ Occupying this oasis grants permanent resource bonuses to the controlling village!
        </div>
    </div>
    
<?php else: ?>
    <!-- Empty land -->
    <div class="village-info-modal">
        <h3>🌍 Empty Land</h3>
        
        <div class="village-stats">
            <div class="stat-row">
                <span class="stat-label">Coordinates:</span>
                <span class="stat-value">(<?php echo $x; ?>, <?php echo $y; ?>)</span>
            </div>
            <div class="stat-row">
                <span class="stat-label">Terrain:</span>
                <span class="stat-value"><?php echo ucfirst($cell['terrain_type'] ?? 'Plains'); ?></span>
            </div>
            <?php if($distance > 0): ?>
                <div class="distance-info">
                    📏 Distance from your village: <?php echo round($distance, 1); ?> fields
                </div>
            <?php endif; ?>
        </div>
        
        <div class="action-buttons">
            <button onclick="foundVillage(<?php echo $x; ?>, <?php echo $y; ?>)" 
                    class="btn btn-primary">🏠 Found Village</button>
        </div>
        
        <div class="distance-info" style="margin-top: 10px; background: #fff3e0; border-radius: 5px; padding: 8px; text-align: center; font-size: 11px;">
            💡 Founding a new village requires:<br>
            3 Settlers + 750 Wood + 750 Clay + 750 Iron + 750 Crop
        </div>
    </div>
<?php endif; ?>

<script>
function sendAttack(villageId, distance) {
    if(confirm(`⚔️ Send attack to this village?\n\nDistance: ${distance.toFixed(1)} fields\n\nYour troops will travel and attack the village.`)) {
        window.location.href = `military.php?action=attack&target=${villageId}`;
    }
}

function sendRaid(villageId, distance) {
    if(confirm(`💰 Send raid to steal resources?\n\nDistance: ${distance.toFixed(1)} fields\n\nRaiding parties will steal resources and return.`)) {
        window.location.href = `military.php?action=raid&target=${villageId}`;
    }
}

function sendSpy(villageId, distance) {
    if(confirm(`🕵️ Send spies to gather intelligence?\n\nDistance: ${distance.toFixed(1)} fields\n\nSpies will report enemy troop counts and resources.`)) {
        window.location.href = `military.php?action=spy&target=${villageId}`;
    }
}

function attackOasis(oasisId, distance, oasisType) {
    let bonusText = oasisType === 'crop_oasis' ? '+50% crop production' : '+25% ' + oasisType.replace('_oasis', '') + ' production';
    if(confirm(`⚔️ Attack and occupy this oasis?\n\nDistance: ${distance.toFixed(1)} fields\nBonus: ${bonusText}\n\nYou will need to defeat the current occupier's troops.`)) {
        window.location.href = `military.php?action=attack_oasis&target=${oasisId}`;
    }
}

function raidOasis(oasisId, distance) {
    if(confirm(`💰 Raid this oasis?\n\nDistance: ${distance.toFixed(1)} fields\n\nYour troops will attack and steal resources if successful.`)) {
        window.location.href = `military.php?action=raid_oasis&target=${oasisId}`;
    }
}

function occupyOasis(x, y, oasisType, distance) {
    let bonusText = oasisType === 'crop_oasis' ? '+50% crop production' : '+25% ' + oasisType.replace('_oasis', '') + ' production';
    if(confirm(`🏕️ Occupy this unoccupied oasis?\n\nCoordinates: (${x}, ${y})\nDistance: ${distance.toFixed(1)} fields\nBonus: ${bonusText}\n\nYou will need to send troops to garrison the oasis.`)) {
        window.location.href = `military.php?action=occupy_oasis&x=${x}&y=${y}`;
    }
}

function abandonOasis(oasisId) {
    if(confirm('⚠️ Are you sure you want to abandon this oasis?\n\nYou will lose all resource bonuses and any troops stationed there will return to your village.')) {
        fetch('ajax/abandon_oasis.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'oasis_id=' + oasisId
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('✅ Oasis abandoned successfully!');
                location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        });
    }
}

function viewOasisGarrison(oasisId) {
    window.location.href = `military.php?action=garrison&target=${oasisId}`;
}

function foundVillage(x, y) {
    if(confirm('🏠 Found a new village?\n\nThis will cost:\n• 3 Settlers\n• 750 Wood\n• 750 Clay\n• 750 Iron\n• 750 Crop\n\nAre you sure?')) {
        fetch('ajax/found_village.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `x=${x}&y=${y}`
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('✅ Village founded successfully!');
                location.reload();
            } else {
                alert('❌ ' + data.message);
            }
        });
    }
}

// Close modal function (called from parent)
function closeModal() {
    const modal = document.getElementById('villageModal');
    if(modal) {
        modal.style.display = 'none';
    }
}
</script>