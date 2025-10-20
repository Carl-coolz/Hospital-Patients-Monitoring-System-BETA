<?php
include 'db.php';

echo "<h2>Simple Database Fix</h2>";

// First, let's fix the patients table structure
echo "<p>Fixing patients table...</p>";

// Drop and recreate with proper structure
mysqli_query($conn, "DROP TABLE IF EXISTS patients");

$create_sql = "
CREATE TABLE `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `age` int(3) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `condition_text` text NOT NULL,
  `date_admitted` date NOT NULL,
  `doctor_assigned` varchar(100) NOT NULL,
  `time_in` int(11) NOT NULL,
  `time_out` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if (mysqli_query($conn, $create_sql)) {
    echo "<p style='color: green;'>✓ Table created successfully</p>";
    
    // Add some test data
    $current_time = time();
    $insert_sql = "
    INSERT INTO `patients` (`name`, `age`, `gender`, `condition_text`, `date_admitted`, `doctor_assigned`, `time_in`, `time_out`) VALUES
    ('John Doe', 25, 'Male', 'Headache', '" . date('Y-m-d') . "', 'doctor', $current_time, 0),
    ('Jane Smith', 30, 'Female', 'Fever', '" . date('Y-m-d') . "', 'doctor', " . ($current_time - 3600) . ", 0),
    ('Mike Johnson', 22, 'Male', 'Stomach Pain', '" . date('Y-m-d') . "', 'doctor', " . ($current_time - 7200) . ", " . ($current_time - 1800) . "),
    ('Sarah Wilson', 28, 'Female', 'Cold', '" . date('Y-m-d') . "', 'doctor', " . ($current_time - 10800) . ", 0)";
    
    if (mysqli_query($conn, $insert_sql)) {
        echo "<p style='color: green;'>✓ Test data added successfully</p>";
    } else {
        echo "<p style='color: red;'>Error adding test data: " . mysqli_error($conn) . "</p>";
    }
    
} else {
    echo "<p style='color: red;'>Error creating table: " . mysqli_error($conn) . "</p>";
}

// Show the results
echo "<h3>Current Patients:</h3>";
$result = mysqli_query($conn, "SELECT * FROM patients ORDER BY id");
if ($result && mysqli_num_rows($result) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Age</th><th>Gender</th><th>Condition</th><th>Status</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        $status = $row['time_out'] == 0 ? 'In Queue' : 'Checked Out';
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
        echo "<td>" . $row['age'] . "</td>";
        echo "<td>" . $row['gender'] . "</td>";
        echo "<td>" . htmlspecialchars($row['condition_text']) . "</td>";
        echo "<td>" . $status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No patients found.</p>";
}

echo "<p><a href='dashboard.php' style='background: #c40202; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a></p>";
?>
