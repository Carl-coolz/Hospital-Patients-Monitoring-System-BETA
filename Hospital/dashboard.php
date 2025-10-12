<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Handle time-out action
if (isset($_GET['timeout_id'])) {
    $id = $_GET['timeout_id'];
    $time_out = date('Y-m-d H:i:s');
    mysqli_query($conn, "UPDATE patients SET time_out='$time_out' WHERE id='$id'");
    header("Location: dashboard.php");
    exit();
}

// Get all patients
$result = mysqli_query($conn, "SELECT * FROM patients ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hospital Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Patient Dashboard</h2>
    <a href="add_patient.php">+ Add New Patient</a><br><br>

    <table border="1" cellpadding="10">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Age</th>
            <th>Condition</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Action</th>
        </tr>

        <?php while($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['age'] ?></td>
            <td><?= $row['condition_text'] ?></td>
            <td><?= $row['time_in'] ?></td>
            <td><?= $row['time_out'] ?: '<i>Still Admitted</i>' ?></td>
            <td>
                <?php if (!$row['time_out']): ?>
                    <a href="dashboard.php?timeout_id=<?= $row['id'] ?>" 
                       onclick="return confirm('Mark patient as timed out?')">
                        ⏰ Time Out
                    </a>
                <?php else: ?>
                    ✅ Done
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>
</body>
</html>
