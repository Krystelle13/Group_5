<?php
require_once 'db_connect.php'; session_start();
$set = $conn->query("SELECT * FROM settings LIMIT 1")->fetch();
if (isset($_POST['login'])) {
    if ($_POST['user'] === "aura" && $_POST['pass'] === "auraG5") {
        $_SESSION['authenticated'] = true; header("Location: dashboard.php"); exit();
    } else { $error = "Invalid Username or Password!"; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Island Aura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #c4cedd; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 400px; background: white; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); padding: 40px; text-align: center; }
        .logo-img { width: 120px; margin-bottom: 20px; } /* Removed any background container */
    </style>
</head>
<body>
<div class="login-card">
    <img src="uploads/<?= $set['hotel_logo'] ?>" class="logo-img">
    <h4 class="fw-bold mb-4">Admin Login</h4>
    <form method="POST">
        <div class="mb-3 text-start"><label class="small fw-bold">Username</label><input type="text" name="user" class="form-control py-2" required></div>
        <div class="mb-4 text-start"><label class="small fw-bold">Password</label><input type="password" name="pass" class="form-control py-2" required></div>
        <button type="submit" name="login" class="btn btn-primary w-100 py-2 fw-bold">Login</button>
        <?php if(isset($error)) echo "<p class='text-danger small mt-3'>$error</p>"; ?>
    </form>
</div>
</body>
</html>