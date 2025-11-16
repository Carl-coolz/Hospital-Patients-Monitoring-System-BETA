<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get selected month and year from GET parameters
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Validate month and year
if ($selected_month < 1 || $selected_month > 12) {
    $selected_month = date('n');
}
if ($selected_year < 2000 || $selected_year > 2100) {
    $selected_year = date('Y');
}

// Month names
$month_names = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

$month_name = $month_names[$selected_month];

// Get patients for selected month
$start_date = sprintf('%04d-%02d-01', $selected_year, $selected_month);
$end_date = date('Y-m-t', strtotime($start_date)); // Last day of the month

$query = "SELECT * FROM patients 
          WHERE DATE(date_admitted) >= '$start_date' 
          AND DATE(date_admitted) <= '$end_date'
          ORDER BY date_admitted ASC, time_in ASC";

$patients_result = mysqli_query($conn, $query);
$total_patients = mysqli_num_rows($patients_result);

// Set headers for Excel download
$filename = "Monthly_Report_{$month_name}_{$selected_year}.xls";
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Start output
echo "<html><head><meta charset='UTF-8'></head><body>";
echo "<table border='1'>";

// Header row
echo "<tr style='background-color: #c40202; color: white; font-weight: bold;'>";
echo "<th>#</th>";
echo "<th>Patient Name</th>";
echo "<th>Age</th>";
echo "<th>Gender</th>";
echo "<th>Student ID</th>";
echo "<th>Course</th>";
echo "<th>Year Level</th>";
echo "<th>Patient Type</th>";
echo "<th>Emergency Status</th>";
echo "<th>Medical Condition</th>";
echo "<th>Date Admitted</th>";
echo "<th>Time In</th>";
echo "<th>Time Out</th>";
echo "<th>Doctor Assigned</th>";
echo "<th>Status</th>";
echo "</tr>";

// Data rows
$counter = 1;
while($patient = mysqli_fetch_assoc($patients_result)) {
    // Format time in
    $time_in_display = 'Unknown';
    if (!empty($patient['time_in'])) {
        if (is_numeric($patient['time_in'])) {
            $time_in_display = date('Y-m-d h:i A', (int)$patient['time_in']);
        } elseif (strtotime($patient['time_in']) !== false) {
            $time_in_display = date('Y-m-d h:i A', strtotime($patient['time_in']));
        }
    }
    
    // Format time out
    $time_out_display = 'N/A';
    if (!empty($patient['time_out']) && $patient['time_out'] != 0) {
        if (is_numeric($patient['time_out'])) {
            $time_out_display = date('Y-m-d h:i A', (int)$patient['time_out']);
        } elseif (strtotime($patient['time_out']) !== false) {
            $time_out_display = date('Y-m-d h:i A', strtotime($patient['time_out']));
        }
    }
    
    // Status
    $status = ($patient['time_out'] == 0) ? 'In Queue' : 'Checked Out';
    
    // Get emergency status and patient type
    $emergency_status = isset($patient['emergency_status']) ? $patient['emergency_status'] : 'Non-Emergency';
    $patient_type = isset($patient['patient_type']) ? $patient['patient_type'] : 'Non-Employee';
    
    echo "<tr>";
    echo "<td>" . $counter++ . "</td>";
    echo "<td>" . htmlspecialchars($patient['name']) . "</td>";
    echo "<td>" . $patient['age'] . "</td>";
    echo "<td>" . htmlspecialchars($patient['gender']) . "</td>";
    echo "<td>" . htmlspecialchars($patient['student_id']) . "</td>";
    echo "<td>" . htmlspecialchars($patient['student_course']) . "</td>";
    echo "<td>" . htmlspecialchars($patient['student_year']) . "</td>";
    echo "<td>" . htmlspecialchars($patient_type) . "</td>";
    echo "<td>" . htmlspecialchars($emergency_status) . "</td>";
    echo "<td>" . htmlspecialchars($patient['condition_text']) . "</td>";
    echo "<td>" . date('Y-m-d', strtotime($patient['date_admitted'])) . "</td>";
    echo "<td>" . $time_in_display . "</td>";
    echo "<td>" . $time_out_display . "</td>";
    echo "<td>" . htmlspecialchars($patient['doctor_assigned']) . "</td>";
    echo "<td>" . $status . "</td>";
    echo "</tr>";
}

// Get statistics
$emergency_count = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM patients 
     WHERE DATE(date_admitted) >= '$start_date' 
     AND DATE(date_admitted) <= '$end_date' 
     AND emergency_status = 'Emergency'"))['count'];

$employee_count = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM patients 
     WHERE DATE(date_admitted) >= '$start_date' 
     AND DATE(date_admitted) <= '$end_date' 
     AND patient_type = 'Employee'"))['count'];

$non_employee_count = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as count FROM patients 
     WHERE DATE(date_admitted) >= '$start_date' 
     AND DATE(date_admitted) <= '$end_date' 
     AND patient_type = 'Non-Employee'"))['count'];

// Summary row
echo "<tr style='background-color: #f0f0f0; font-weight: bold;'>";
echo "<td colspan='15' style='text-align: center; padding: 10px;'>";
echo "Monthly Report Summary for " . $month_name . " " . $selected_year;
echo "</td>";
echo "</tr>";

// Statistics row
echo "<tr style='background-color: #e8e8e8;'>";
echo "<td colspan='3'><strong>Total Patients:</strong></td>";
echo "<td><strong>" . $total_patients . "</strong></td>";
echo "<td colspan='2'><strong>Emergency Cases:</strong></td>";
echo "<td><strong>" . $emergency_count . "</strong></td>";
echo "<td colspan='2'><strong>Employees:</strong></td>";
echo "<td><strong>" . $employee_count . "</strong></td>";
echo "<td colspan='2'><strong>Non-Employees:</strong></td>";
echo "<td><strong>" . $non_employee_count . "</strong></td>";
echo "<td colspan='3'></td>";
echo "</tr>";

echo "</table>";
echo "</body></html>";
exit;
?>

