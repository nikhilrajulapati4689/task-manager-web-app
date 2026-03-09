<?php
session_start();
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {

        // PREPARED STATEMENT (SECURE)
        $stmt = $conn->prepare("SELECT username, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user['username'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password";
        }

        $stmt->close();
    } else {
        $error = "Please fill all fields";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Task Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">
    <div class="login-card">

        <div class="logo"></div>

        <h1>Welcome Back</h1>
        <p class="subtitle">Sign in to continue to your account</p>

        <?php if (!empty($error)) : ?>
            <p class="error-msg"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <label>Email Address</label>
            <div class="input-box">
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <label>Password</label>
            <div class="input-box">
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>

            <div class="forgot">
                <a href="#">Forgot password?</a>
            </div>

            <button type="submit" class="login-btn">
                Sign In →
            </button>
        </form>

        <p class="register-text">
            Don’t have an account?
            <a href="register.php">Create one</a>
        </p>

    </div>
</div>

</body>
</html>