<?php
session_start();
include 'db.php';
if (!isset($_SESSION['user'])) { header("Location: login.php"); exit(); }

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $condition_text = $_POST['condition_text']; // ✅ fixed here
    $time_in = date('Y-m-d H:i:s');

    mysqli_query($conn, "INSERT INTO patients (name, age, condition_text, time_in) 
                         VALUES ('$name', '$age', '$condition_text', '$time_in')");
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Patient</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h2>Add New Patient</h2>
    <form method="post">
        <input type="text" name="name" placeholder="Name" required><br>
        <input type="number" name="age" placeholder="Age" required><br>
        <input type="text" name="condition_text" placeholder="Condition" required><br>
        <button type="submit">Add Patient</button>
    </form>
</div>
</body>
</html>
