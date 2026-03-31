<?php
session_start();
require_once '../src/classes/User.php';
require_once '../src/classes/Message.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user = new User();
$user->id = $_SESSION['user_id'];
$user->username = $_SESSION['username'];

include_once 'includes/header.php';

$message = new Message();
$error = '';
$success = '';

// Handle sending new message
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $to_username = trim($_POST['to_username']);
    $subject = trim($_POST['subject']);
    $content = trim($_POST['content']);
    
    if(empty($to_username) || empty($subject) || empty($content)) {
        $error = "❌ All fields are required!";
    } else {
        $result = $message->sendMessage($_SESSION['user_id'], $to_username, $subject, $content);
        if($result['success']) {
            $success = "✅ Message sent successfully!";
            $_POST = array();
        } else {
            $error = $result['message'];
        }
    }
}

// Handle reply
$reply_to = '';
$reply_subject = '';
$reply_content = '';
if(isset($_GET['reply']) && is_numeric($_GET['reply'])) {
    $original = $message->getMessage($_GET['reply']);
    if($original && ($original['to_user_id'] == $_SESSION['user_id'] || $original['from_user_id'] == $_SESSION['user_id'])) {
        $reply_to = $original['from_user_id'] == $_SESSION['user_id'] ? $original['to_username'] : $original['from_username'];
        $reply_subject = "Re: " . $original['subject'];
        $reply_content = "\n\n--- Original Message ---\nFrom: " . $original['from_username'] . "\nDate: " . date('Y-m-d H:i', strtotime($original['created_at'])) . "\n\n" . $original['content'];
    }
}

// Handle delete
if(isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if($message->deleteMessage($_GET['delete'], $_SESSION['user_id'])) {
        $success = "✅ Message deleted successfully!";
    } else {
        $error = "❌ Could not delete message!";
    }
}

// Get messages
$inbox = $message->getInbox($_SESSION['user_id']);
$sent = $message->getSentMessages($_SESSION['user_id']);
$unread_count = $message->getUnreadCount($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Messages - Travian Classic</title>
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
        
        .messages-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .messages-card h3 {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
            color: #333;
        }
        
        /* Message Tabs */
        .message-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .tab {
            padding: 8px 16px;
            cursor: pointer;
            background: none;
            border: none;
            font-size: 14px;
            font-weight: bold;
            color: #666;
            transition: all 0.3s;
        }
        
        .tab.active {
            color: #5cb85c;
            border-bottom: 2px solid #5cb85c;
            margin-bottom: -2px;
        }
        
        .tab:hover {
            color: #4cae4c;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Message List */
        .message-list {
            max-height: 450px;
            overflow-y: auto;
        }
        
        .message-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #e0e0e0;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .message-item:hover {
            background: #f8f9fa;
        }
        
        .message-item.unread {
            background: #fff8e7;
            border-left: 3px solid #f0ad4e;
        }
        
        .message-info {
            flex: 1;
        }
        
        .message-subject {
            font-weight: bold;
            color: #333;
            margin-bottom: 4px;
            font-size: 13px;
        }
        
        .message-meta {
            font-size: 10px;
            color: #888;
        }
        
        .message-actions {
            display: flex;
            gap: 6px;
        }
        
        .icon-btn {
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            padding: 4px;
            transition: transform 0.2s;
        }
        
        .icon-btn:hover {
            transform: scale(1.1);
        }
        
        .reply-btn {
            color: #5bc0de;
        }
        
        .delete-btn {
            color: #d9534f;
        }
        
        /* Compose Form */
        .compose-form {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }
        
        .form-group {
            margin-bottom: 12px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 4px;
            font-weight: bold;
            color: #333;
            font-size: 12px;
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
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #999;
        }
        
        .empty-state-icon {
            font-size: 40px;
            margin-bottom: 10px;
        }
        
        .badge {
            display: inline-block;
            background: #f0ad4e;
            color: white;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 6px;
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
            <!-- Left Column - Compose Message -->
            <div class="left-column">
                <div class="messages-card">
                    <h3>✍️ Compose Message</h3>
                    <div class="compose-form">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label>📬 To:</label>
                                <input type="text" name="to_username" placeholder="Enter player name" 
                                       value="<?php echo isset($reply_to) ? htmlspecialchars($reply_to) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>📝 Subject:</label>
                                <input type="text" name="subject" placeholder="Message subject" 
                                       value="<?php echo isset($reply_subject) ? htmlspecialchars($reply_subject) : ''; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>💬 Message:</label>
                                <textarea name="content" rows="5" placeholder="Type your message here..." required><?php echo isset($reply_content) ? htmlspecialchars($reply_content) : ''; ?></textarea>
                            </div>
                            <button type="submit" name="send_message" class="btn btn-primary">✈️ Send Message</button>
                        </form>
                    </div>
                </div>
                
                <div class="messages-card">
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
            
            <!-- Center Column - Message List -->
            <div class="center-column">
                <div class="messages-card">
                    <div class="message-tabs">
                        <button class="tab active" onclick="switchTab('inbox')">
                            📥 Inbox 
                            <?php if($unread_count > 0): ?>
                                <span class="badge"><?php echo $unread_count; ?></span>
                            <?php endif; ?>
                        </button>
                        <button class="tab" onclick="switchTab('sent')">📤 Sent (<?php echo count($sent); ?>)</button>
                    </div>
                    
                    <!-- Inbox Tab -->
                    <div id="inbox-tab" class="tab-content active">
                        <?php if(empty($inbox)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">📭</div>
                                <p>No messages in your inbox</p>
                                <small>Send a message to other players to start a conversation!</small>
                            </div>
                        <?php else: ?>
                            <div class="message-list">
                                <?php foreach($inbox as $msg): ?>
                                    <div class="message-item <?php echo !$msg['is_read'] ? 'unread' : ''; ?>" 
                                         onclick="viewMessage(<?php echo $msg['id']; ?>)">
                                        <div class="message-info">
                                            <div class="message-subject">
                                                <?php echo htmlspecialchars($msg['subject']); ?>
                                                <?php if(!$msg['is_read']): ?>
                                                    <span class="badge">New</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="message-meta">
                                                From: <strong><?php echo htmlspecialchars($msg['from_username']); ?></strong> | 
                                                <?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?>
                                            </div>
                                        </div>
                                        <div class="message-actions" onclick="event.stopPropagation()">
                                            <button class="icon-btn reply-btn" onclick="replyToMessage(<?php echo $msg['id']; ?>, '<?php echo htmlspecialchars($msg['from_username']); ?>', '<?php echo htmlspecialchars($msg['subject']); ?>')" title="Reply">
                                                ↩️
                                            </button>
                                            <button class="icon-btn delete-btn" onclick="deleteMessage(<?php echo $msg['id']; ?>)" title="Delete">
                                                🗑️
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Sent Tab -->
                    <div id="sent-tab" class="tab-content">
                        <?php if(empty($sent)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">📤</div>
                                <p>No sent messages</p>
                                <small>Your sent messages will appear here</small>
                            </div>
                        <?php else: ?>
                            <div class="message-list">
                                <?php foreach($sent as $msg): ?>
                                    <div class="message-item" onclick="viewMessage(<?php echo $msg['id']; ?>)">
                                        <div class="message-info">
                                            <div class="message-subject">
                                                <?php echo htmlspecialchars($msg['subject']); ?>
                                            </div>
                                            <div class="message-meta">
                                                To: <strong><?php echo htmlspecialchars($msg['to_username']); ?></strong> | 
                                                <?php echo date('Y-m-d H:i', strtotime($msg['created_at'])); ?>
                                            </div>
                                        </div>
                                        <div class="message-actions" onclick="event.stopPropagation()">
                                            <button class="icon-btn delete-btn" onclick="deleteMessage(<?php echo $msg['id']; ?>)" title="Delete">
                                                🗑️
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Navigation -->
            <div class="right-column">
                <div class="messages-card">
                    <h3>🗺️ Navigation</h3>
                    <div class="nav-links">
                        <a href="village.php" class="nav-link">🏠 Back to Village</a>
                        <a href="map.php" class="nav-link">🗺️ World Map</a>
                        <a href="military.php" class="nav-link">⚔️ Military</a>
                        <a href="alliance.php" class="nav-link">🤝 Alliance</a>
                        <a href="rankings.php" class="nav-link">🏆 Rankings</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Message View Modal -->
    <div id="messageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; margin: 20px; padding: 0; max-width: 500px; width: 90%; border-radius: 10px; max-height: 80%; overflow-y: auto;">
            <div id="messageContent"></div>
        </div>
    </div>
    
    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            event.target.classList.add('active');
            
            document.getElementById('inbox-tab').classList.remove('active');
            document.getElementById('sent-tab').classList.remove('active');
            document.getElementById(tab + '-tab').classList.add('active');
        }
        
        function viewMessage(messageId) {
            fetch('ajax/get_message.php?id=' + messageId)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('messageContent').innerHTML = html;
                    document.getElementById('messageModal').style.display = 'flex';
                });
        }
        
        function replyToMessage(messageId, fromUsername, subject) {
            window.location.href = 'messages.php?reply=' + messageId;
        }
        
        function deleteMessage(messageId) {
            if(confirm('Are you sure you want to delete this message?')) {
                window.location.href = 'messages.php?delete=' + messageId;
            }
        }
        
        function closeModal() {
            document.getElementById('messageModal').style.display = 'none';
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('messageModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>