<?php
session_start();
require_once '../src/classes/User.php';

if(isset($_SESSION['user_id'])) {
    header('Location: village.php');
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = new User();
    $result = $user->login($_POST['username'], $_POST['password']);
    
    if($result['success']) {
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['tribe'] = $user->tribe;
        header('Location: village.php');
        exit();
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
    <title>Login - Travian Classic</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Login</h1>
            <?php if($error): ?><div class="alert alert-error"><?php echo $error; ?></div><?php endif; ?>
            <form method="POST">
                <div class="form-group"><label>Username</label><input type="text" name="username" required></div>
                <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
                <button type="submit" class="btn btn-primary btn-block">Login</button>
            </form>
            <p class="auth-link">Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>
</body>
</html>