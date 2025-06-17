<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Login SIKORMAN</title>
</head>
<body>
    <h2>Login Sistem SIKORMAN</h2>
    <form method="POST" action="auth/check_login.php">
        <label>Email:</label><br>
        <input type="text" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>
