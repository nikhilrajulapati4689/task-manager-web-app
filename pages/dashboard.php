<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

include "db.php";
$user = $_SESSION['user'];

/* ================= FILTER LOGIC ================= */
$filter = $_GET['filter'] ?? 'All';
$filterQuery = "";

if (in_array($filter, ['Pending', 'Ongoing', 'Completed'])) {
    $filterQuery = "AND status='$filter'";
}

/* ================= ADD TASK ================= */
if (isset($_POST['addTask'])) {
    $task     = trim($_POST['task']);
    $priority = $_POST['priority'];
    $datetime = $_POST['datetime'];

    if ($task && $datetime) {
        $stmt = $conn->prepare(
            "INSERT INTO tasks (user, task_name, priority, status, due_datetime)
             VALUES (?, ?, ?, 'Pending', ?)"
        );
        $stmt->bind_param("ssss", $user, $task, $priority, $datetime);
        $stmt->execute();
        $stmt->close();
    }
}

/* ================= UPDATE STATUS ================= */
if (isset($_GET['action'], $_GET['id'])) {
    $id = (int)$_GET['id'];
    $action = $_GET['action'];

    if (in_array($action, ['Pending', 'Ongoing', 'Completed'])) {
        $stmt = $conn->prepare(
            "UPDATE tasks SET status=? WHERE id=? AND user=?"
        );
        $stmt->bind_param("sis", $action, $id, $user);
        $stmt->execute();
        $stmt->close();
    }
}

/* ================= DELETE TASK ================= */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id=? AND user=?");
    $stmt->bind_param("is", $id, $user);
    $stmt->execute();
    $stmt->close();
}

/* ================= COUNTS ================= */
$total     = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM tasks WHERE user='$user'"));
$pending   = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM tasks WHERE user='$user' AND status='Pending'"));
$ongoing   = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM tasks WHERE user='$user' AND status='Ongoing'"));
$completed = mysqli_num_rows(mysqli_query($conn,"SELECT id FROM tasks WHERE user='$user' AND status='Completed'"));

/* ================= FETCH TASKS ================= */
$tasks = mysqli_query(
    $conn,
    "SELECT * FROM tasks WHERE user='$user' $filterQuery ORDER BY due_datetime ASC"
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

<aside class="sidebar">
    <h2>My-Task Manager</h2>
    <a class="active">Dashboard</a>
    <a href="my-tasks.php">My Tasks</a>
    <a href="logout.php">Logout</a>
</aside>

<main>

<h2>Welcome, <?= htmlspecialchars($user) ?> 👋</h2>

<div class="stats">
    <a class="stat-card <?= $filter=='All'?'active':'' ?>" href="?filter=All">Total<br><b><?= $total ?></b></a>
    <a class="stat-card <?= $filter=='Pending'?'active':'' ?>" href="?filter=Pending">Pending<br><b><?= $pending ?></b></a>
    <a class="stat-card <?= $filter=='Ongoing'?'active':'' ?>" href="?filter=Ongoing">Ongoing<br><b><?= $ongoing ?></b></a>
    <a class="stat-card <?= $filter=='Completed'?'active':'' ?>" href="?filter=Completed">Completed<br><b><?= $completed ?></b></a>
</div>

<div class="card">
<h3>Add Task</h3>
<form method="POST" class="task-form">
    <input type="text" name="task" placeholder="Task name" required>
    <select name="priority">
        <option>High</option>
        <option>Medium</option>
        <option>Low</option>
    </select>
    <input type="datetime-local" name="datetime" required>
    <button type="submit" name="addTask" class="add-btn">Add</button>
</form>
</div>

<div class="card">
<h3>To-Do List</h3>

<?php while ($row = mysqli_fetch_assoc($tasks)): ?>
<div class="task <?= strtolower($row['status']) ?>">
    <div>
        <b><?= htmlspecialchars($row['task_name']) ?></b><br>
        <small><?= $row['priority'] ?> | <?= date("d M Y, h:i A", strtotime($row['due_datetime'])) ?></small>
    </div>

    <div class="countdown"
         data-deadline="<?= $row['due_datetime'] ?>"
         data-status="<?= $row['status'] ?>">
        --
    </div>

    <div class="actions">
        <a href="?action=Pending&id=<?= $row['id'] ?>">Pending</a>
        <a href="?action=Ongoing&id=<?= $row['id'] ?>">Ongoing</a>
        <a href="?action=Completed&id=<?= $row['id'] ?>">Completed</a>
        <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Delete this task?')">🗑 Delete</a>
    </div>
</div>
<?php endwhile; ?>

</div>
</main>
</div>

<script>
function updateCountdowns() {
    document.querySelectorAll('.countdown').forEach(el => {
        const status = el.dataset.status;
        if (status === 'Completed') {
            el.innerHTML = '✅ Completed';
            el.className = 'countdown completed';
            return;
        }

        const deadline = new Date(el.dataset.deadline.replace(" ", "T")).getTime();
        const now = Date.now();
        const diff = deadline - now;

        if (diff <= 0) {
            el.innerHTML = '⏰ Time Over';
            el.className = 'countdown overdue blink';
            return;
        }

        const h = Math.floor(diff / (1000 * 60 * 60));
        const m = Math.floor((diff / (1000 * 60)) % 60);
        const s = Math.floor((diff / 1000) % 60);

        el.innerHTML = `${h}h ${m}m ${s}s`;

        if (diff <= 3600000) {
            el.className = 'countdown urgent blink';
        } else {
            el.className = 'countdown normal';
        }
    });
}

updateCountdowns();
setInterval(updateCountdowns, 1000);
</script>

</body>
</html>