<?php
session_start();
require_once '../src/classes/User.php';
require_once '../src/classes/Alliance.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user = new User();
$user->id = $_SESSION['user_id'];
$user->username = $_SESSION['username'];

include_once 'includes/header.php';

$alliance = new Alliance();

// Get active tab from URL
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'players';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Get rankings based on tab
$rankings = [];
$total = 0;
$user_rank = null;
$alliance_rank = null;

if($tab == 'players') {
    $rankings = $user->getPlayerRankings($limit, $offset);
    $total = $user->getTotalPlayers();
    $user_rank = $user->getPlayerRank($_SESSION['user_id']);
} elseif($tab == 'alliances') {
    $rankings = $alliance->getAllianceRankings($limit, $offset);
    $total = $alliance->getTotalAlliances();
    $user_alliance = $alliance->getUserAlliance($_SESSION['user_id']);
    if($user_alliance) {
        $alliance_rank = $alliance->getAllianceRank($user_alliance['id']);
    }
} elseif($tab == 'military') {
    $rankings = $alliance->getMilitaryRankings($limit, $offset);
    $total = $user->getTotalPlayers();
    $user_rank = $user->getPlayerRank($_SESSION['user_id']);
}

$total_pages = ceil($total / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Rankings - Travian Classic</title>
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
        
        .rankings-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .rankings-card h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
            color: #333;
        }
        
        .rank-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .rank-tab {
            padding: 10px 20px;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 16px;
            font-weight: bold;
            color: #666;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .rank-tab.active {
            color: #5cb85c;
            border-bottom: 2px solid #5cb85c;
            margin-bottom: -2px;
        }
        
        .rank-tab:hover {
            color: #4cae4c;
        }
        
        .rankings-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .rankings-table th,
        .rankings-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .rankings-table th {
            background: #f5f5f5;
            font-weight: bold;
            color: #333;
        }
        
        .rankings-table tr:hover {
            background: #f9f9f9;
        }
        
        .rank-1 {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
            font-weight: bold;
        }
        
        .rank-2 {
            background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);
        }
        
        .rank-3 {
            background: linear-gradient(135deg, #cd7f32 0%, #e8a87c 100%);
        }
        
        .your-rank {
            background: #e8f5e9;
            border-left: 4px solid #5cb85c;
        }
        
        .rank-badge {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            text-align: center;
            border-radius: 50%;
            background: #f0f0f0;
            font-weight: bold;
        }
        
        .rank-1 .rank-badge {
            background: #ffd700;
            color: #333;
        }
        
        .rank-2 .rank-badge {
            background: #c0c0c0;
            color: #333;
        }
        
        .rank-3 .rank-badge {
            background: #cd7f32;
            color: white;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        
        .pagination a {
            padding: 8px 12px;
            background: #f5f5f5;
            text-decoration: none;
            color: #667eea;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .pagination a:hover {
            background: #667eea;
            color: white;
        }
        
        .pagination .active {
            background: #5cb85c;
            color: white;
        }
        
        .stats-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 15px;
        }
        
        .stats-number {
            font-size: 28px;
            font-weight: bold;
            color: #667eea;
        }
        
        .stats-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
        
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
        
        @media (max-width: 768px) {
            .game-content {
                grid-template-columns: 1fr;
            }
            
            .rankings-table {
                font-size: 12px;
            }
            
            .rankings-table th,
            .rankings-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="game-container">
        <div class="game-content">
            <!-- Left Column - Stats & Info -->
            <div class="left-column">
                <div class="rankings-card">
                    <h3>📊 Rankings Info</h3>
                    <div class="stats-box">
                        <div class="stats-number">#<?php echo $user_rank ?: '?'; ?></div>
                        <div class="stats-label">Your Rank</div>
                    </div>
                    <div class="stats-box">
                        <div class="stats-number"><?php echo number_format($total); ?></div>
                        <div class="stats-label">Total <?php echo $tab == 'alliances' ? 'Alliances' : 'Players'; ?></div>
                    </div>
                    <?php if($tab == 'alliances' && $alliance_rank): ?>
                    <div class="stats-box">
                        <div class="stats-number">#<?php echo $alliance_rank; ?></div>
                        <div class="stats-label">Your Alliance Rank</div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="rankings-card">
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
            
            <!-- Center Column - Rankings Table -->
            <div class="center-column">
                <div class="rankings-card">
                    <div class="rank-tabs">
                        <a href="?tab=players&page=1" class="rank-tab <?php echo $tab == 'players' ? 'active' : ''; ?>">🏆 Players</a>
                        <a href="?tab=alliances&page=1" class="rank-tab <?php echo $tab == 'alliances' ? 'active' : ''; ?>">🤝 Alliances</a>
                        <a href="?tab=military&page=1" class="rank-tab <?php echo $tab == 'military' ? 'active' : ''; ?>">⚔️ Military</a>
                    </div>
                    
                    <table class="rankings-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th><?php echo $tab == 'alliances' ? 'Alliance' : 'Player'; ?></th>
                                <?php if($tab == 'players'): ?>
                                    <th>Tribe</th>
                                    <th>Villages</th>
                                    <th>Population</th>
                                <?php elseif($tab == 'alliances'): ?>
                                    <th>Tag</th>
                                    <th>Members</th>
                                    <th>Villages</th>
                                    <th>Population</th>
                                <?php elseif($tab == 'military'): ?>
                                    <th>Tribe</th>
                                    <th>Infantry</th>
                                    <th>Cavalry</th>
                                    <th>Total</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $rank_start = $offset + 1;
                            foreach($rankings as $index => $item):
                                $rank = $rank_start + $index;
                                $is_user = ($tab == 'players' && $item['id'] == $_SESSION['user_id']) ||
                                          ($tab == 'alliances' && isset($user_alliance) && $item['id'] == $user_alliance['id']);
                                $row_class = '';
                                if($rank == 1) $row_class = 'rank-1';
                                elseif($rank == 2) $row_class = 'rank-2';
                                elseif($rank == 3) $row_class = 'rank-3';
                                if($is_user) $row_class .= ' your-rank';
                            ?>
                                <tr class="<?php echo $row_class; ?>">
                                    <td><span class="rank-badge"><?php echo $rank; ?></span></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($tab == 'alliances' ? $item['name'] : $item['username']); ?></strong>
                                        <?php if($is_user): ?> <span style="color: #5cb85c;">(You)</span><?php endif; ?>
                                    </td>
                                    <?php if($tab == 'players'): ?>
                                        <td>
                                            <?php 
                                            $tribe_icon = '';
                                            if($item['tribe'] == 'roman') $tribe_icon = '🏛️';
                                            elseif($item['tribe'] == 'teuton') $tribe_icon = '⚔️';
                                            elseif($item['tribe'] == 'gaul') $tribe_icon = '🛡️';
                                            echo $tribe_icon . ' ' . ucfirst($item['tribe']);
                                            ?>
                                        </td>
                                        <td><?php echo number_format($item['total_villages']); ?></td>
                                        <td><?php echo number_format($item['total_population']); ?></td>
                                    <?php elseif($tab == 'alliances'): ?>
                                        <td>[<?php echo htmlspecialchars($item['tag']); ?>]</td>
                                        <td><?php echo number_format($item['total_members']); ?></td>
                                        <td><?php echo number_format($item['total_villages']); ?></td>
                                        <td><?php echo number_format($item['total_population']); ?></td>
                                    <?php elseif($tab == 'military'): ?>
                                        <td>
                                            <?php 
                                            if($item['tribe'] == 'roman') echo '🏛️ Roman';
                                            elseif($item['tribe'] == 'teuton') echo '⚔️ Teuton';
                                            elseif($item['tribe'] == 'gaul') echo '🛡️ Gaul';
                                            ?>
                                        </td>
                                        <td><?php echo number_format($item['infantry']); ?></td>
                                        <td><?php echo number_format($item['cavalry']); ?></td>
                                        <td><strong><?php echo number_format($item['total_troops']); ?></strong></td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                            
                            <?php if(empty($rankings)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px;">
                                        No <?php echo $tab == 'alliances' ? 'alliances' : 'players'; ?> found yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    
                    <?php if($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if($page > 1): ?>
                            <a href="?tab=<?php echo $tab; ?>&page=<?php echo $page-1; ?>">◀ Previous</a>
                        <?php endif; ?>
                        
                        <?php
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        for($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <a href="?tab=<?php echo $tab; ?>&page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                        <?php endfor; ?>
                        
                        <?php if($page < $total_pages): ?>
                            <a href="?tab=<?php echo $tab; ?>&page=<?php echo $page+1; ?>">Next ▶</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Right Column - Navigation -->
            <div class="right-column">
                <div class="rankings-card">
                    <h3>🗺️ Navigation</h3>
                    <div class="nav-links">
                        <a href="village.php" class="nav-link">🏠 Back to Village</a>
                        <a href="map.php" class="nav-link">🗺️ World Map</a>
                        <a href="military.php" class="nav-link">⚔️ Military</a>
                        <a href="alliance.php" class="nav-link">🤝 Alliance</a>
                        <a href="messages.php" class="nav-link">📬 Messages</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>