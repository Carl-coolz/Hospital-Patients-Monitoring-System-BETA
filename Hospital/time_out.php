<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

$id = $_GET['id'];
$time_out = date('Y-m-d H:i:s');
mysqli_query($conn, "UPDATE patients SET time_out='$time_out' WHERE id=$id");
header("Location: dashboard.php");
exit();
?>
