<?php
session_start();
require_once '../src/classes/User.php';

if(isset($_SESSION['user_id'])) {
    header('Location: village.php');
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = new User();
    $result = $user->register($_POST['username'], $_POST['password'], $_POST['email'], $_POST['tribe']);
    
    if($result['success']) {
        $success = "Registration successful! You can now login.";
    } else {
        $error = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Travian Classic</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Register</h1>
            <?php if($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
            <?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
            <form method="POST">
                <div class="form-group"><label>Username</label><input type="text" name="username" required minlength="3"></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                <div class="form-group"><label>Password</label><input type="password" name="password" required minlength="6"></div>
                <div class="form-group">
                    <label>Tribe</label>
                    <div class="tribe-selector">
                        <label class="tribe-option"><input type="radio" name="tribe" value="roman" required> 🏛️ Romans - Balanced, strong infantry</label>
                        <label class="tribe-option"><input type="radio" name="tribe" value="teuton"> ⚔️ Teutons - Aggressive, cheap troops</label>
                        <label class="tribe-option"><input type="radio" name="tribe" value="gaul"> 🛡️ Gauls - Defensive, fast units</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
            <p class="auth-link">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>