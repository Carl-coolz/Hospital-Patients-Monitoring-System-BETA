<?php
include 'db.php';

echo "<h2>Database Structure Check</h2>";

// Check current patients table structure
$result = mysqli_query($conn, "DESCRIBE patients");
if ($result) {
    echo "<h3>Current Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Error: " . mysqli_error($conn) . "</p>";
}

// Check current data
echo "<h3>Current Patients Data:</h3>";
$patients = mysqli_query($conn, "SELECT * FROM patients");
if (mysqli_num_rows($patients) > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Age</th><th>Gender</th><th>Condition</th><th>Time In</th><th>Time Out</th></tr>";
    while ($patient = mysqli_fetch_assoc($patients)) {
        echo "<tr>";
        echo "<td>" . $patient['id'] . "</td>";
        echo "<td>" . htmlspecialchars($patient['name']) . "</td>";
        echo "<td>" . $patient['age'] . "</td>";
        echo "<td>" . $patient['gender'] . "</td>";
        echo "<td>" . htmlspecialchars($patient['condition_text']) . "</td>";
        echo "<td>" . $patient['time_in'] . "</td>";
        echo "<td>" . $patient['time_out'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No patients found.</p>";
}
?>
