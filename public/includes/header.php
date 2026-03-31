<?php
if(!isset($_SESSION['user_id'])) return;

require_once __DIR__ . '/../../src/classes/User.php';
require_once __DIR__ . '/../../src/classes/Village.php';
require_once __DIR__ . '/../../src/classes/Message.php';

$user = new User();
$user->id = $_SESSION['user_id'];
$villages = $user->getVillages();

$current_village_id = isset($_GET['village']) ? intval($_GET['village']) : 
                      (isset($_SESSION['current_village_id']) ? $_SESSION['current_village_id'] : 
                      (!empty($villages) ? $villages[0]['id'] : 0));

if($current_village_id > 0) {
    $_SESSION['current_village_id'] = $current_village_id;
}

$resources = null;
if($current_village_id > 0) {
    $village = new Village();
    $village->updateResources($current_village_id);
    $resources = $village->getResources($current_village_id);
}

$message = new Message();
$unread_count = $message->getUnreadCount($_SESSION['user_id']);
?>

<div class="top-bar">
    <div class="resources">
        <div class="resource wood">
            <span class="resource-icon">🌲</span>
            <span class="resource-value" id="wood-resource"><?php echo $resources ? floor($resources['wood']) : 0; ?></span>
        </div>
        <div class="resource clay">
            <span class="resource-icon">🧱</span>
            <span class="resource-value" id="clay-resource"><?php echo $resources ? floor($resources['clay']) : 0; ?></span>
        </div>
        <div class="resource iron">
            <span class="resource-icon">⛏️</span>
            <span class="resource-value" id="iron-resource"><?php echo $resources ? floor($resources['iron']) : 0; ?></span>
        </div>
        <div class="resource crop">
            <span class="resource-icon">🌾</span>
            <span class="resource-value" id="crop-resource"><?php echo $resources ? floor($resources['crop']) : 0; ?></span>
        </div>
    </div>
    
    <div class="village-selector">
        <select id="villageSelect" onchange="changeVillage()">
            <?php foreach($villages as $v): ?>
                <option value="<?php echo $v['id']; ?>" <?php echo $v['id'] == $current_village_id ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($v['name']); ?> (<?php echo $v['x']; ?>, <?php echo $v['y']; ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="user-menu">
        <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
        <a href="messages.php" class="message-link">
            📬 <?php if($unread_count > 0): ?><span class="unread-badge"><?php echo $unread_count; ?></span><?php endif; ?>
        </a>
        <a href="logout.php" class="btn-small">Logout</a>
    </div>
</div>

<script>
function changeVillage() {
    const select = document.getElementById('villageSelect');
    const url = new URL(window.location.href);
    url.searchParams.set('village', select.value);
    window.location.href = url;
}

function updateResources() {
    const villageId = document.getElementById('villageSelect')?.value;
    if(!villageId) return;
    
    fetch('ajax/resources.php?village_id=' + villageId)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                document.getElementById('wood-resource').textContent = Math.floor(data.resources.wood);
                document.getElementById('clay-resource').textContent = Math.floor(data.resources.clay);
                document.getElementById('iron-resource').textContent = Math.floor(data.resources.iron);
                document.getElementById('crop-resource').textContent = Math.floor(data.resources.crop);
            }
        });
}

setInterval(updateResources, 30000);
</script>

<style>
.top-bar {
    background: #2c3e50;
    color: white;
    padding: 10px;
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
}
.resources { display: flex; gap: 15px; flex-wrap: wrap; }
.resource { display: flex; align-items: center; gap: 5px; background: rgba(255,255,255,0.1); padding: 5px 10px; border-radius: 5px; }
.resource-value { font-weight: bold; min-width: 60px; }
.village-selector select { padding: 5px 10px; border-radius: 5px; background: #34495e; color: white; border: none; cursor: pointer; }
.user-menu { display: flex; align-items: center; gap: 10px; }
.message-link { color: white; text-decoration: none; font-size: 1.2rem; position: relative; }
.unread-badge { position: absolute; top: -8px; right: -12px; background: #e74c3c; color: white; border-radius: 50%; padding: 2px 6px; font-size: 10px; }
.btn-small { background: #e74c3c; color: white; padding: 5px 10px; border-radius: 3px; text-decoration: none; }
@media (max-width: 768px) {
    .top-bar { flex-direction: column; align-items: stretch; }
    .resources { justify-content: center; }
    .village-selector select { width: 100%; }
    .user-menu { justify-content: center; }
}
</style>