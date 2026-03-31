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
$user_alliance = $alliance->getUserAlliance($_SESSION['user_id']);
$is_member = !!$user_alliance;
$is_leader = $is_member && $user_alliance['leader_id'] == $_SESSION['user_id'];

$error = '';
$success = '';

// Handle form submissions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['create_alliance'])) {
        $name = trim($_POST['name']);
        $tag = strtoupper(trim($_POST['tag']));
        $description = trim($_POST['description']);
        
        if($alliance->createAlliance($_SESSION['user_id'], $name, $tag, $description)) {
            $success = "✅ Alliance created successfully!";
            $user_alliance = $alliance->getUserAlliance($_SESSION['user_id']);
            $is_member = true;
            $is_leader = true;
        } else {
            $error = "❌ Alliance name or tag already exists!";
        }
    } elseif(isset($_POST['join_alliance'])) {
        $alliance_id = intval($_POST['alliance_id']);
        if($alliance->joinAlliance($_SESSION['user_id'], $alliance_id)) {
            $success = "✅ Successfully joined the alliance!";
            $user_alliance = $alliance->getUserAlliance($_SESSION['user_id']);
            $is_member = true;
            $is_leader = false;
        } else {
            $error = "❌ Could not join alliance. You might already be in one.";
        }
    } elseif(isset($_POST['leave_alliance'])) {
        if($alliance->leaveAlliance($_SESSION['user_id'])) {
            $success = "✅ You have left the alliance.";
            $user_alliance = null;
            $is_member = false;
            $is_leader = false;
        } else {
            $error = "❌ Could not leave alliance. You might be the leader.";
        }
    } elseif(isset($_POST['kick_member']) && $is_leader) {
        $member_id = intval($_POST['member_id']);
        if($alliance->kickMember($user_alliance['id'], $member_id)) {
            $success = "✅ Member has been kicked from the alliance.";
        } else {
            $error = "❌ Could not kick member.";
        }
    } elseif(isset($_POST['send_message']) && $is_member) {
        $subject = trim($_POST['subject']);
        $content = trim($_POST['content']);
        if($alliance->sendAllianceMessage($user_alliance['id'], $_SESSION['user_id'], $subject, $content)) {
            $success = "✅ Message sent to all alliance members!";
        } else {
            $error = "❌ Could not send message.";
        }
    } elseif(isset($_POST['update_description']) && $is_leader) {
        $description = trim($_POST['description']);
        if($alliance->updateDescription($user_alliance['id'], $description)) {
            $success = "✅ Alliance description updated!";
        } else {
            $error = "❌ Could not update description.";
        }
    }
}

// Get data
$alliance_details = null;
$members = [];
$messages = [];
$alliances = [];

if($is_member) {
    $alliance_details = $alliance->getAllianceDetails($user_alliance['id']);
    $members = $alliance->getAllianceMembers($user_alliance['id']);
    $messages = $alliance->getAllianceMessages($user_alliance['id']);
} else {
    $alliances = $alliance->getAlliances();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Alliance - Travian Classic</title>
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
        
        .alliance-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .alliance-card h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
            color: #333;
        }
        
        /* Alliance Header */
        .alliance-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .alliance-tag {
            font-size: 32px;
            font-weight: bold;
            font-family: monospace;
        }
        
        .alliance-name {
            font-size: 20px;
            margin-top: 8px;
        }
        
        .alliance-description {
            margin-top: 15px;
            padding: 12px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            line-height: 1.4;
            font-size: 13px;
        }
        
        /* Member List */
        .member-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .member-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
            transition: background 0.2s;
        }
        
        .member-item:hover {
            background: #f8f9fa;
        }
        
        .member-info {
            flex: 1;
        }
        
        .member-name {
            font-weight: bold;
            color: #333;
            font-size: 14px;
        }
        
        .member-rank {
            font-size: 10px;
            color: #888;
            margin-top: 2px;
        }
        
        .leader-badge {
            background: #ffd700;
            color: #333;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            margin-left: 6px;
        }
        
        .kick-btn {
            background: #d9534f;
            color: white;
            border: none;
            padding: 4px 10px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
            transition: all 0.2s;
        }
        
        .kick-btn:hover {
            background: #c9302c;
        }
        
        /* Messages */
        .message-list {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .message-item {
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .message-item:hover {
            background: #f8f9fa;
        }
        
        .message-subject {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            font-size: 13px;
        }
        
        .message-meta {
            font-size: 10px;
            color: #888;
            margin-bottom: 6px;
        }
        
        .message-content {
            font-size: 12px;
            color: #666;
            line-height: 1.4;
        }
        
        /* Form Styling */
        .form-group {
            margin-bottom: 12px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 4px;
            font-weight: bold;
            color: #333;
            font-size: 13px;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 13px;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #5cb85c;
            color: white;
            width: 100%;
        }
        
        .btn-primary:hover {
            background: #4cae4c;
        }
        
        .btn-danger {
            background: #d9534f;
            color: white;
            width: 100%;
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
        
        .btn-secondary {
            background: #5bc0de;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #46b8da;
        }
        
        .btn-small {
            padding: 4px 10px;
            font-size: 11px;
        }
        
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 13px;
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
        
        /* Alliance Cards for non-members */
        .alliance-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 12px;
            transition: all 0.2s;
        }
        
        .alliance-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .alliance-item h4 {
            margin-bottom: 6px;
            color: #333;
            font-size: 14px;
        }
        
        .alliance-stats {
            display: flex;
            gap: 12px;
            margin: 8px 0;
            font-size: 11px;
            color: #666;
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
            font-size: 13px;
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
            font-size: 12px;
        }
        
        @media (max-width: 768px) {
            .game-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="game-container">
        <div class="game-content">
            <!-- Left Column - Alliance Info & Actions -->
            <div class="left-column">
                <?php if($is_member && $alliance_details): ?>
                    <div class="alliance-card">
                        <div class="alliance-tag">[<?php echo htmlspecialchars($alliance_details['tag']); ?>]</div>
                        <div class="alliance-name"><?php echo htmlspecialchars($alliance_details['name']); ?></div>
                        <div class="alliance-description">
                            <?php echo nl2br(htmlspecialchars($alliance_details['description'])); ?>
                        </div>
                        <?php if($is_leader): ?>
                            <div style="margin-top: 12px;">
                                <button onclick="showEditDescription()" class="btn btn-secondary btn-small" style="width: 100%;">✏️ Edit Description</button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="alliance-card">
                        <h3>⚙️ Alliance Actions</h3>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to leave this alliance?')">
                            <button type="submit" name="leave_alliance" class="btn btn-danger">🚪 Leave Alliance</button>
                        </form>
                    </div>
                    
                    <div class="alliance-card">
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
                <?php else: ?>
                    <div class="alliance-card">
                        <h3>🤝 Join an Alliance</h3>
                        <p style="font-size: 13px; color: #666; line-height: 1.4;">Join an existing alliance or create your own to coordinate attacks and dominate together!</p>
                    </div>
                    
                    <div class="alliance-card">
                        <h3>🏛️ Create New Alliance</h3>
                        <form method="POST">
                            <div class="form-group">
                                <label>Alliance Name</label>
                                <input type="text" name="name" required maxlength="100" placeholder="e.g., Roman Empire">
                            </div>
                            <div class="form-group">
                                <label>Alliance Tag (3-10 chars)</label>
                                <input type="text" name="tag" required maxlength="10" placeholder="e.g., ROM">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" rows="3" placeholder="Describe your alliance..."></textarea>
                            </div>
                            <button type="submit" name="create_alliance" class="btn btn-primary">✨ Create Alliance</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Center Column - Members or Alliances List -->
            <div class="center-column">
                <?php if($is_member && $alliance_details): ?>
                    <div class="alliance-card">
                        <h3>👥 Alliance Members (<?php echo count($members); ?>)</h3>
                        <div class="member-list">
                            <?php foreach($members as $member): ?>
                                <div class="member-item">
                                    <div class="member-info">
                                        <div class="member-name">
                                            <?php echo htmlspecialchars($member['username']); ?>
                                            <?php if($member['user_id'] == $alliance_details['leader_id']): ?>
                                                <span class="leader-badge">LEADER</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="member-rank">Rank: <?php echo ucfirst($member['rank']); ?></div>
                                    </div>
                                    <?php if($is_leader && $member['user_id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" style="margin: 0;" onsubmit="return confirm('Kick this member from the alliance?')">
                                            <input type="hidden" name="member_id" value="<?php echo $member['user_id']; ?>">
                                            <button type="submit" name="kick_member" class="kick-btn">Kick</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alliance-card">
                        <h3>📋 Existing Alliances</h3>
                        <?php if(empty($alliances)): ?>
                            <p style="text-align: center; padding: 40px; color: #999;">No alliances available.<br>Be the first to create one!</p>
                        <?php else: ?>
                            <?php foreach($alliances as $ally): ?>
                                <div class="alliance-item">
                                    <h4>[<?php echo htmlspecialchars($ally['tag']); ?>] <?php echo htmlspecialchars($ally['name']); ?></h4>
                                    <p style="font-size: 12px; color: #666;"><?php echo htmlspecialchars(substr($ally['description'], 0, 80)); ?>...</p>
                                    <div class="alliance-stats">
                                        <span>👥 Members: <?php echo $ally['member_count']; ?></span>
                                        <span>👑 Leader: <?php echo htmlspecialchars($ally['leader_name']); ?></span>
                                    </div>
                                    <form method="POST" style="margin-top: 8px;">
                                        <input type="hidden" name="alliance_id" value="<?php echo $ally['id']; ?>">
                                        <button type="submit" name="join_alliance" class="btn btn-primary btn-small" style="width: 100%;">Join Alliance</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Right Column - Messages & Navigation -->
            <div class="right-column">
                <?php if($is_member && $alliance_details): ?>
                    <div class="alliance-card">
                        <h3>💬 Alliance Messages</h3>
                        <button onclick="showMessageForm()" class="btn btn-primary btn-small" style="margin-bottom: 12px; width: 100%;">✍️ Send Message</button>
                        
                        <div id="messageForm" style="display: none; margin-bottom: 15px; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                            <form method="POST">
                                <div class="form-group">
                                    <label>Subject</label>
                                    <input type="text" name="subject" placeholder="Message subject..." required>
                                </div>
                                <div class="form-group">
                                    <label>Message</label>
                                    <textarea name="content" rows="2" placeholder="Type your message here..." required></textarea>
                                </div>
                                <button type="submit" name="send_message" class="btn btn-primary btn-small">Send</button>
                                <button type="button" onclick="hideMessageForm()" class="btn btn-warning btn-small">Cancel</button>
                            </form>
                        </div>
                        
                        <div class="message-list">
                            <?php if(empty($messages)): ?>
                                <p style="text-align: center; padding: 30px; color: #999; font-size: 12px;">No messages yet.<br>Be the first to send a message!</p>
                            <?php else: ?>
                                <?php foreach($messages as $msg): ?>
                                    <div class="message-item">
                                        <div class="message-subject">📧 <?php echo htmlspecialchars($msg['subject']); ?></div>
                                        <div class="message-meta">
                                            From: <strong><?php echo htmlspecialchars($msg['username']); ?></strong> | 
                                            <?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?>
                                        </div>
                                        <div class="message-content"><?php echo nl2br(htmlspecialchars(substr($msg['content'], 0, 100))); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="alliance-card">
                    <h3>🗺️ Navigation</h3>
                    <div class="nav-links">
                        <a href="village.php" class="nav-link">🏠 Back to Village</a>
                        <a href="map.php" class="nav-link">🗺️ World Map</a>
                        <a href="military.php" class="nav-link">⚔️ Military</a>
                        <a href="messages.php" class="nav-link">📬 Messages</a>
                        <a href="rankings.php" class="nav-link">🏆 Rankings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php if($is_leader): ?>
    <div id="editDescriptionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; padding: 25px; max-width: 450px; width: 90%; border-radius: 10px;">
            <h3 style="margin-bottom: 15px;">✏️ Edit Alliance Description</h3>
            <form method="POST">
                <div class="form-group">
                    <textarea name="description" rows="5" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;"><?php echo htmlspecialchars($alliance_details['description']); ?></textarea>
                </div>
                <button type="submit" name="update_description" class="btn btn-primary">Update</button>
                <button type="button" onclick="hideEditDescription()" class="btn btn-warning">Cancel</button>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        function showMessageForm() {
            document.getElementById('messageForm').style.display = 'block';
        }
        
        function hideMessageForm() {
            document.getElementById('messageForm').style.display = 'none';
        }
        
        <?php if($is_leader): ?>
        function showEditDescription() {
            document.getElementById('editDescriptionModal').style.display = 'flex';
        }
        
        function hideEditDescription() {
            document.getElementById('editDescriptionModal').style.display = 'none';
        }
        <?php endif; ?>
        
        window.onclick = function(event) {
            const modal = document.getElementById('editDescriptionModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>