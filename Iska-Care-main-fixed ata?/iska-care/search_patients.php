<?php
session_start();
include 'db.php';

// Set content type to JSON
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if (empty($search) || strlen($search) < 2) {
    echo json_encode([]);
    exit();
}

// Search patients by name or condition
$searchTerm = mysqli_real_escape_string($conn, $search);
$query = "SELECT id, name, age, gender, condition_text, date_admitted, time_in, time_out 
          FROM patients 
          WHERE name LIKE '%$searchTerm%' 
             OR condition_text LIKE '%$searchTerm%' 
          ORDER BY name ASC 
          LIMIT 10";

$result = mysqli_query($conn, $query);

$patients = [];
while ($row = mysqli_fetch_assoc($result)) {
    $patients[] = $row;
}

echo json_encode($patients);
?>
