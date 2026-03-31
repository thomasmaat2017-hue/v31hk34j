<?php
session_start();
require_once '../src/classes/User.php';
if(!isset($_SESSION['user_id'])) header('Location: login.php');
include_once 'includes/header.php';
$villages=$user->getVillages();
$first=$villages[0]??null;
$center_x=$_GET['x']??($first?$first['x']:0);
$center_y=$_GET['y']??($first?$first['y']:0);
?>
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>World Map</title><link rel="stylesheet" href="css/style.css">
<style>
.map-wrapper{background:#2c5a2e;padding:20px;border-radius:10px}
.map-controls{display:flex;justify-content:space-between;margin-bottom:15px}
.map-nav-btn{background:#4a6a3e;color:#fff;border:none;padding:8px 15px;border-radius:5px;cursor:pointer}
.map-nav-btn:hover{background:#5a7a4e}
.map-grid{display:inline-block;background:#3d6b3a}
.map-row{display:flex}
.map-cell{width:70px;height:70px;border:1px solid #2c5a2e;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#4a7a45;color:#fff}
.map-header-cell{background:#2c5a2e;color:#ffd966}
.center-cell{background:#ffd966;border:2px solid #ffaa00}
.occupied-cell{background:#6b4c3b}
.empty-cell{background:#5a8a55}
.village-list{background:rgba(0,0,0,0.3);padding:10px;border-radius:5px;margin-top:15px}
.village-buttons{display:flex;gap:10px;flex-wrap:wrap}
.village-btn{background:#4a6a3e;color:white;border:none;padding:5px 10px;border-radius:3px;cursor:pointer}
.village-btn:hover{background:#5a7a4e}
.village-btn.active{background:#ffd966;color:#2c5a2e}
.top-nav{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;padding:10px;background:rgba(0,0,0,0.2);border-radius:5px}
.back-btn{background:#5cb85c;color:white;padding:8px 20px;text-decoration:none;border-radius:5px;display:inline-block}
.back-btn:hover{background:#4cae4c}
@media(max-width:768px){.map-cell{width:40px;height:40px}}
</style>
</head>
<body>
<div class="game-container">
    <div class="game-content">
        <!-- Top navigation bar with back button -->
        <div class="top-nav">
            <a href="village.php" class="back-btn">🏠 Back to Village</a>
            <div class="map-title">🗺️ World Map</div>
            <div style="width: 100px;"></div> <!-- Spacer for balance -->
        </div>
        
        <div class="map-wrapper">
            <div id="mapContainer">
                <div style="text-align:center;padding:50px">Loading map...</div>
            </div>
        </div>
        
        <!-- Village quick navigation -->
        <?php if(count($villages) > 1): ?>
        <div class="village-list">
            <h4>🏘️ Your Villages</h4>
            <div class="village-buttons">
                <?php foreach($villages as $v): ?>
                    <button onclick="centerOnVillage(<?php echo $v['x']; ?>, <?php echo $v['y']; ?>)" 
                            class="village-btn <?php echo ($v['x'] == $center_x && $v['y'] == $center_y) ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($v['name']); ?> (<?php echo $v['x']; ?>, <?php echo $v['y']; ?>)
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
let cx=<?php echo $center_x; ?>, cy=<?php echo $center_y; ?>;
const userVillages = <?php echo json_encode($villages); ?>;

function loadMap(){
    fetch(`ajax/map.php?x=${cx}&y=${cy}&t=${Date.now()}`)
        .then(r=>r.text())
        .then(h=>document.getElementById('mapContainer').innerHTML=h);
}

function moveMap(dx,dy){
    cx+=dx;
    cy+=dy;
    loadMap();
}

function centerOnVillage(x,y){
    cx=x;
    cy=y;
    loadMap();
}

function centerOnMyVillage(){
    if(userVillages.length > 0){
        cx=userVillages[0].x;
        cy=userVillages[0].y;
        loadMap();
    }
}

function showVillageInfo(x,y){
    fetch(`ajax/village_info.php?x=${x}&y=${y}`)
        .then(r=>r.text())
        .then(html=>{
            const modal=document.createElement('div');
            modal.style.position='fixed';
            modal.style.top='0';
            modal.style.left='0';
            modal.style.width='100%';
            modal.style.height='100%';
            modal.style.background='rgba(0,0,0,0.5)';
            modal.style.zIndex='1000';
            modal.style.display='flex';
            modal.style.alignItems='center';
            modal.style.justifyContent='center';
            modal.innerHTML=`<div style="background:white;border-radius:10px;max-width:500px;width:90%;max-height:80%;overflow:auto;position:relative;"><div style="position:absolute;top:10px;right:15px;font-size:24px;cursor:pointer;" onclick="this.parentElement.parentElement.remove()">&times;</div><div style="padding:20px;">${html}</div></div>`;
            document.body.appendChild(modal);
        });
}

function sendAttack(villageId){
    window.location.href=`military.php?action=attack&target=${villageId}`;
}

function foundVillage(x,y){
    if(confirm('Found a new village? Cost: 3 Settlers + 750 of each resource')){
        fetch('ajax/found_village.php',{
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded'},
            body:`x=${x}&y=${y}`
        }).then(r=>r.json()).then(d=>{
            if(d.success){
                alert('Village founded!');
                location.reload();
            } else alert(d.message);
        });
    }
}

function attackOasis(oasisId,distance,oasisType){
    if(confirm(`Attack oasis? Distance: ${distance.toFixed(1)} fields`)){
        window.location.href=`military.php?action=attack_oasis&target=${oasisId}`;
    }
}

function occupyOasis(x,y,oasisType,distance){
    if(confirm(`Occupy oasis? Distance: ${distance.toFixed(1)} fields`)){
        window.location.href=`military.php?action=occupy_oasis&x=${x}&y=${y}`;
    }
}

// Keyboard navigation
document.addEventListener('keydown',function(e){
    if(e.key==='ArrowUp'){moveMap(0,-1);}
    else if(e.key==='ArrowDown'){moveMap(0,1);}
    else if(e.key==='ArrowLeft'){moveMap(-1,0);}
    else if(e.key==='ArrowRight'){moveMap(1,0);}
    else if(e.key==='Escape'){
        const modal=document.querySelector('[style*="position: fixed"][style*="z-index: 1000"]');
        if(modal) modal.remove();
    }
});

loadMap();
</script>
</body>
</html>