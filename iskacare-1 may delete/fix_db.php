<?php
// Database fix script - Run this once to fix your database structure
include 'db.php';

echo "<h2>Fixing Database Structure...</h2>";

// Check if patients table exists and has proper structure
$result = mysqli_query($conn, "SHOW CREATE TABLE patients");
if ($result) {
    $table_info = mysqli_fetch_assoc($result);
    echo "<p>Current table structure found.</p>";
    
    // Check if id is primary key and auto_increment
    $check_primary = mysqli_query($conn, "SHOW COLUMNS FROM patients LIKE 'id'");
    $column_info = mysqli_fetch_assoc($check_primary);
    
    if (strpos($column_info['Extra'], 'auto_increment') === false) {
        echo "<p>Fixing table structure...</p>";
        
        // Backup existing data
        mysqli_query($conn, "CREATE TABLE patients_backup AS SELECT * FROM patients");
        echo "<p>✓ Backed up existing data</p>";
        
        // Drop and recreate table with proper structure
        mysqli_query($conn, "DROP TABLE patients");
        
        $create_table = "
        CREATE TABLE patients (
            id int(11) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            age int(3) NOT NULL,
            gender varchar(10) NOT NULL,
            condition_text text NOT NULL,
            date_admitted date NOT NULL,
            doctor_assigned varchar(100) NOT NULL,
            time_in int(11) NOT NULL,
            time_out int(11) NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
        
        if (mysqli_query($conn, $create_table)) {
            echo "<p>✓ Created new patients table with proper structure</p>";
            
            // Copy data back (excluding id column to let auto_increment work)
            mysqli_query($conn, "
                INSERT INTO patients (name, age, gender, condition_text, date_admitted, doctor_assigned, time_in, time_out)
                SELECT name, age, gender, condition_text, date_admitted, doctor_assigned, time_in, time_out
                FROM patients_backup
            ");
            echo "<p>✓ Restored patient data with new IDs</p>";
            
            // Add some sample data if table is empty
            $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM patients"))['count'];
            if ($count == 0) {
                mysqli_query($conn, "
                    INSERT INTO patients (name, age, gender, condition_text, date_admitted, doctor_assigned, time_in, time_out) VALUES
                    ('John Doe', 25, 'Male', 'Headache', '2024-01-15', 'doctor', " . time() . ", 0),
                    ('Jane Smith', 30, 'Female', 'Fever', '2024-01-15', 'doctor', " . (time() - 3600) . ", 0),
                    ('Mike Johnson', 22, 'Male', 'Stomach Pain', '2024-01-15', 'doctor', " . (time() - 7200) . ", " . (time() - 1800) . "),
                    ('Sarah Wilson', 28, 'Female', 'Cold', '2024-01-15', 'doctor', " . (time() - 10800) . ", 0)
                ");
                echo "<p>✓ Added sample patient data</p>";
            }
            
            echo "<h3 style='color: green;'>✓ Database fixed successfully!</h3>";
            echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";
            
        } else {
            echo "<p style='color: red;'>Error creating table: " . mysqli_error($conn) . "</p>";
        }
    } else {
        echo "<p style='color: green;'>✓ Table structure is already correct!</p>";
        echo "<p><a href='dashboard.php'>Go to Dashboard</a></p>";
    }
} else {
    echo "<p style='color: red;'>Error: Could not check table structure</p>";
}

// Show current patients
echo "<h3>Current Patients:</h3>";
$patients = mysqli_query($conn, "SELECT * FROM patients ORDER BY id");
if (mysqli_num_rows($patients) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Name</th><th>Age</th><th>Gender</th><th>Condition</th><th>Status</th></tr>";
    while ($patient = mysqli_fetch_assoc($patients)) {
        $status = $patient['time_out'] == 0 ? 'In Queue' : 'Checked Out';
        echo "<tr>";
        echo "<td>" . $patient['id'] . "</td>";
        echo "<td>" . htmlspecialchars($patient['name']) . "</td>";
        echo "<td>" . $patient['age'] . "</td>";
        echo "<td>" . $patient['gender'] . "</td>";
        echo "<td>" . htmlspecialchars($patient['condition_text']) . "</td>";
        echo "<td>" . $status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>No patients found.</p>";
}
?>
