<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM patients WHERE id=$id");
$p = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Patient Info</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Patient Information</h2>
    <p><b>Name:</b> <?= htmlspecialchars($p['name']) ?></p>
    <p><b>Age:</b> <?= $p['age'] ?></p>
    <p><b>Condition:</b> <?= htmlspecialchars($p['condition_text']) ?></p>
    <p><b>Time In:</b> <?= $p['time_in'] ?></p>
    <p><b>Time Out:</b> <?= $p['time_out'] ?: '---' ?></p>
    <a href="dashboard.php">⬅ Back</a>
</div>
</body>
</html>
