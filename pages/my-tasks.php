<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

include "db.php";
$user = $_SESSION['user'];

/* UPDATE STATUS */
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if (in_array($action, ['Ongoing', 'Completed'])) {
        $stmt = $conn->prepare(
            "UPDATE tasks SET status=? WHERE id=? AND user=?"
        );
        $stmt->bind_param("sis", $action, $id, $user);
        $stmt->execute();
        $stmt->close();
    }
}

/* DELETE TASK */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id=? AND user=?");
    $stmt->bind_param("is", $id, $user);
    $stmt->execute();
    $stmt->close();
}

/* FETCH ONLY PENDING TASKS */
$tasks = mysqli_query(
    $conn,
    "SELECT * FROM tasks 
     WHERE user='$user' AND status='Pending'
     ORDER BY due_datetime ASC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My-Task Manager</title>
<link rel="stylesheet" href="dashboard.css">
</head>

<body>

<div class="app">

<!-- SIDEBAR -->
<aside class="sidebar">
    <h2>My-Task Manager</h2>
    <a href="dashboard.php">Dashboard</a>
    <a class="active">My Tasks</a>
   
    <a href="logout.php">Logout</a>
</aside>

<!-- MAIN -->
<main>

<h2>My Pending Tasks 🕒</h2>

<div class="card">

<?php if (mysqli_num_rows($tasks) > 0): ?>
<?php while ($row = mysqli_fetch_assoc($tasks)): ?>

<div class="task pending">
    <b><?= htmlspecialchars($row['task_name']) ?></b><br>

    <small>
        <?= htmlspecialchars($row['priority']) ?> |
        <?= date("d M Y, h:i A", strtotime($row['due_datetime'])) ?>
    </small>

    <div class="actions">
        <a href="?action=Ongoing&id=<?= $row['id'] ?>">Ongoing</a>
        <a href="?action=Completed&id=<?= $row['id'] ?>">Completed</a>
        <a href="?delete=<?= $row['id'] ?>"
           onclick="return confirm('Delete this task?')">Delete</a>
    </div>
</div>

<?php endwhile; ?>
<?php else: ?>
<p>No pending tasks 🎉</p>
<?php endif; ?>

</div>

</main>
</div>

</body>
</html>
