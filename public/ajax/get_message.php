<?php
session_start();
require_once '../../src/classes/Message.php';

if(!isset($_SESSION['user_id'])) {
    die('Not logged in');
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = new Message();
$msg = $message->getMessage($id);

if(!$msg || ($msg['to_user_id'] != $_SESSION['user_id'] && $msg['from_user_id'] != $_SESSION['user_id'])) {
    die('Message not found');
}

if($msg['to_user_id'] == $_SESSION['user_id'] && !$msg['is_read']) {
    $message->markAsRead($id, $_SESSION['user_id']);
}
?>
<div style="padding: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h3 style="margin: 0;"><?php echo htmlspecialchars($msg['subject']); ?></h3>
        <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
    </div>
    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
        <div><strong>From:</strong> <?php echo htmlspecialchars($msg['from_username']); ?></div>
        <div><strong>To:</strong> <?php echo htmlspecialchars($msg['to_username']); ?></div>
        <div><strong>Date:</strong> <?php echo date('Y-m-d H:i:s', strtotime($msg['created_at'])); ?></div>
    </div>
    <div style="margin-bottom: 20px; line-height: 1.6; white-space: pre-wrap;">
        <?php echo nl2br(htmlspecialchars($msg['content'])); ?>
    </div>
    <div style="display: flex; gap: 10px;">
        <?php if($msg['from_user_id'] != $_SESSION['user_id']): ?>
            <button onclick="window.location.href='messages.php?reply=<?php echo $msg['id']; ?>'" 
                    style="background: #5cb85c; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer;">
                ↩️ Reply
            </button>
        <?php endif; ?>
        <button onclick="deleteMessage(<?php echo $msg['id']; ?>)" 
                style="background: #d9534f; color: white; border: none; padding: 8px 16px; border-radius: 5px; cursor: pointer;">
            🗑️ Delete
        </button>
    </div>
</div>
<script>
function closeModal() {
    document.getElementById('messageModal').style.display = 'none';
}

function deleteMessage(messageId) {
    if(confirm('Are you sure you want to delete this message?')) {
        window.location.href = 'messages.php?delete=' + messageId;
    }
}
</script>