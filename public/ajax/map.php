<?php
session_start();
require_once '../../src/config/database.php';

if(!isset($_SESSION['user_id'])) {
    die('Not logged in');
}

$database = new Database();
$conn = $database->getConnection();

$center_x = isset($_GET['x']) ? intval($_GET['x']) : 0;
$center_y = isset($_GET['y']) ? intval($_GET['y']) : 0;
$radius = 5; // Show 5x5 grid around center

// Get user's villages for highlighting
$query = "SELECT id, x, y FROM villages WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$user_villages = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $user_villages[$row['x']][$row['y']] = $row['id'];
}

// Get map cells
$min_x = $center_x - $radius;
$max_x = $center_x + $radius;
$min_y = $center_y - $radius;
$max_y = $center_y + $radius;

$query = "SELECT mc.*, v.id as village_id, v.name as village_name, v.user_id as owner_id, 
          u.username as owner_name
          FROM map_cells mc
          LEFT JOIN villages v ON mc.village_id = v.id
          LEFT JOIN users u ON v.user_id = u.id
          WHERE mc.x BETWEEN :min_x AND :max_x 
          AND mc.y BETWEEN :min_y AND :max_y
          ORDER BY mc.y, mc.x";

$stmt = $conn->prepare($query);
$stmt->bindParam(':min_x', $min_x);
$stmt->bindParam(':max_x', $max_x);
$stmt->bindParam(':min_y', $min_y);
$stmt->bindParam(':max_y', $max_y);
$stmt->execute();

$map_cells = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $map_cells[$row['y']][$row['x']] = $row;
}

// Generate map HTML
?>
<div class="map-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding: 10px; background: rgba(0,0,0,0.3); border-radius: 5px;">
    <button onclick="window.parent.moveMap(0, -1)" class="map-nav-btn" style="background: #4a6a3e; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">▲ Up</button>
    <span style="color: white; font-weight: bold;">Coordinates: (<?php echo $center_x; ?>, <?php echo $center_y; ?>)</span>
    <button onclick="window.parent.moveMap(0, 1)" class="map-nav-btn" style="background: #4a6a3e; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">Down ▼</button>
</div>

<div class="map-grid" style="display: inline-block; background: #3d6b3a; border: 2px solid #2c5a2e;">
    <!-- Header row with X coordinates -->
    <div class="map-row" style="display: flex;">
        <div class="map-cell corner" style="width: 70px; height: 70px; border: 1px solid #2c5a2e; background: #2c5a2e; display: flex; align-items: center; justify-content: center;"></div>
        <?php for($x = $min_x; $x <= $max_x; $x++): ?>
            <div class="map-cell map-header-cell" style="width: 70px; height: 70px; border: 1px solid #2c5a2e; display: flex; align-items: center; justify-content: center; background: #2c5a2e; color: #ffd966; font-weight: bold;">
                <?php echo $x; ?>
            </div>
        <?php endfor; ?>
    </div>
    
    <!-- Map rows -->
    <?php for($y = $min_y; $y <= $max_y; $y++): ?>
        <div class="map-row" style="display: flex;">
            <!-- Y coordinate header -->
            <div class="map-cell map-header-cell" style="width: 70px; height: 70px; border: 1px solid #2c5a2e; display: flex; align-items: center; justify-content: center; background: #2c5a2e; color: #ffd966; font-weight: bold;">
                <?php echo $y; ?>
            </div>
            
            <!-- Map cells -->
            <?php for($x = $min_x; $x <= $max_x; $x++): ?>
                <?php 
                $cell = isset($map_cells[$y][$x]) ? $map_cells[$y][$x] : null;
                $is_center = ($x == $center_x && $y == $center_y);
                $is_user_village = isset($user_villages[$x][$y]);
                
                // Determine cell style
                $cell_style = 'width: 70px; height: 70px; border: 1px solid #2c5a2e; display: flex; align-items: center; justify-content: center; text-align: center; cursor: pointer; transition: all 0.2s;';
                
                if($is_center) {
                    $cell_style .= ' background: #ffd966; border: 2px solid #ffaa00;';
                } elseif($cell && $cell['village_id']) {
                    $cell_style .= ' background: #6b4c3b;';
                } elseif($cell && $cell['oasis_type']) {
                    $cell_style .= ' background: #4a7a45;';
                } else {
                    $cell_style .= ' background: #5a8a55;';
                }
                ?>
                <div class="map-cell" style="<?php echo $cell_style; ?>" onclick="window.parent.showVillageInfo(<?php echo $x; ?>, <?php echo $y; ?>)">
                    <?php if($cell && $cell['village_id']): ?>
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 2px; font-size: 20px;">
                            🏠
                            <?php if($is_user_village): ?>
                                <div style="font-size: 10px; background: #4cae4c; padding: 2px 4px; border-radius: 3px; color: white; white-space: nowrap;">Your Village</div>
                            <?php else: ?>
                                <div style="font-size: 10px; background: #d9534f; padding: 2px 4px; border-radius: 3px; color: white; white-space: nowrap;">
                                    <?php echo htmlspecialchars(substr($cell['village_name'], 0, 8)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php elseif($cell && $cell['oasis_type']): ?>
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 2px; font-size: 20px;">
                            <?php
                            $oasis_icon = '';
                            switch($cell['oasis_type']) {
                                case 'wood_oasis': $oasis_icon = '🌲'; break;
                                case 'clay_oasis': $oasis_icon = '🧱'; break;
                                case 'iron_oasis': $oasis_icon = '⛏️'; break;
                                case 'crop_oasis': $oasis_icon = '🌾'; break;
                                default: $oasis_icon = '🌿';
                            }
                            echo $oasis_icon;
                            ?>
                            <div style="font-size: 8px;">Oasis</div>
                        </div>
                    <?php else: ?>
                        <div style="font-size: 24px;">🌍</div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    <?php endfor; ?>
</div>

<div class="map-footer" style="display: flex; justify-content: center; gap: 15px; margin-top: 15px; padding: 10px;">
    <button onclick="window.parent.moveMap(-1, 0)" class="map-nav-btn" style="background: #4a6a3e; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">◀ Left</button>
    <button onclick="window.parent.centerOnMyVillage()" class="map-nav-btn" style="background: #4a6a3e; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">🏠 My Village</button>
    <button onclick="window.parent.moveMap(1, 0)" class="map-nav-btn" style="background: #4a6a3e; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">Right ▶</button>
</div>

<style>
@media (max-width: 768px) {
    .map-cell {
        width: 40px !important;
        height: 40px !important;
        font-size: 10px !important;
    }
    .map-cell div {
        font-size: 16px !important;
    }
    .map-cell div div {
        font-size: 8px !important;
    }
}
</style>
<?php
?>