<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

include "db.php";
$user   = $_SESSION['user'];
$filter = $_GET['filter'] ?? 'all';

$where = "user='$user'";
$title = "All Tasks";

if (in_array($filter, ['Pending', 'Ongoing', 'Completed'])) {
    $where .= " AND status='$filter'";
    $title = "$filter Tasks";
}

$tasks = mysqli_query(
    $conn,
    "SELECT task_name, priority, status, due_datetime
     FROM tasks
     WHERE $where
     ORDER BY due_datetime ASC"
);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?= $title ?></title>

<style>
body {
    margin: 0;
    background: #000;
    color: #fff;
    font-family: Arial, sans-serif;
}

.container {
    max-width: 800px;
    margin: 40px auto;
}

h2 {
    text-align: center;
    margin-bottom: 30px;
}

.task {
    background: #111;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
}

.task small {
    color: #aaa;
}

.back {
    display: inline-block;
    margin-bottom: 20px;
    color: #0af;
    text-decoration: none;
}
</style>
</head>

<body>

<div class="container">

<a class="back" href="dashboard.php">← Back to Dashboard</a>

<h2><?= $title ?></h2>

<?php if (mysqli_num_rows($tasks) > 0): ?>
    <?php while ($row = mysqli_fetch_assoc($tasks)): ?>
        <div class="task">
            <b><?= htmlspecialchars($row['task_name']) ?></b><br>
            <small>
                <?= $row['priority'] ?> |
                <?= $row['status'] ?> |
                <?= date("d M Y, h:i A", strtotime($row['due_datetime'])) ?>
            </small>
        </div>
    <?php endwhile; ?>
<?php else: ?>
    <p style="text-align:center;">No tasks found</p>
<?php endif; ?>

</div>

</body>
</html>
