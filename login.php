<?php
session_start();
include "config.php";
include "function.php";

$error_msg = "";

if(isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Check user
    $sql = "SELECT * FROM `hr_user` WHERE `username`='".$username."'";
    $user = select_row($sql);
    
    if($user) {
        if($user['password'] == $password) {
            $_SESSION['userdata'] = json_encode($user);
            setcookie('userdata', json_encode($user), time() + (86400 * 90), "/");
            
            // Note: If superadmin doesn't have a salon_id, logic to assign first one comes here
            // But for now, we just redirect.
            if($user['user_type'] == 1 && empty($user['salon_id'])) {
                $first_salon = select_row("SELECT salon_id FROM hr_salon LIMIT 1");
                if($first_salon) {
                    $user['salon_id'] = $first_salon['salon_id'];
                    $_SESSION['userdata'] = json_encode($user);
                    setcookie('userdata', json_encode($user), time() + (86400 * 90), "/");
                }
            }

            header("Location: index.php");
            exit;
        } else {
            $error_msg = "Invalid password.";
        }
    } else {
        $error_msg = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Salon App V3</title>
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="ph-fill ph-scissors"></i>
            </div>
            <h1>Salon OS</h1>
            <p>Welcome back! Please enter your details.</p>
        </div>
        
        <?php if($error_msg != ""): ?>
            <div class="alert alert-danger">
                <i class="ph ph-warning-circle" style="font-size: 20px;"></i>
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            
            <button type="submit" name="login" class="btn-primary">Sign In</button>
        </form>
    </div>
</body>
</html>
