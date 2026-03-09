<?php
$host = "127.0.0.1";
$user = "root";
$pass = "";          // default XAMPP root password is empty
$db   = "task_manager";
$port = 3306;        // your MySQL port

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
