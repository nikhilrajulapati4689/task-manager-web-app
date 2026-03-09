<?php
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($email) && !empty($password)) {

        // Check if email already exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $checkResult = $check->get_result();

        if ($checkResult->num_rows > 0) {
            $error = "Email already registered";
        } else {

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert user (SECURE)
            $stmt = $conn->prepare(
                "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
            );
            $stmt->bind_param("sss", $username, $email, $hashedPassword);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $error = "Registration failed. Try again.";
            }

            $stmt->close();
        }

        $check->close();
    } else {
        $error = "Please fill all fields";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account | Task Manager</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">
    <div class="login-card">

        <div class="logo"></div>

        <h1>Create Account</h1>
        <p class="subtitle">Register to start managing your tasks</p>

        <?php if (!empty($error)) : ?>
            <p class="error-msg"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST" autocomplete="off">

            <label>Username</label>
            <div class="input-box">
                <input type="text" name="username" placeholder="Enter username" required>
            </div>

            <label>Email Address</label>
            <div class="input-box">
                <input type="email" name="email" placeholder="Enter email" required>
            </div>

            <label>Password</label>
            <div class="input-box">
                <input type="password" name="password" placeholder="Create password" required>
            </div>

            <button type="submit" class="login-btn">
                Create Account →
            </button>
        </form>

        <p class="register-text">
            Already have an account?
            <a href="login.php">Login</a>
        </p>

    </div>
</div>

</body>
</html>