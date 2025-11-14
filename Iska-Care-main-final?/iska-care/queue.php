<?php
session_start();
include 'db.php';

// Logout action
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_destroy();
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Handle time-out action
if (isset($_GET['timeout']) && isset($_GET['id']) && isset($_GET['name'])) {
    $patient_id = intval($_GET['id']);
    $patient_name = mysqli_real_escape_string($conn, $_GET['name']);
    
    // Safety check: Make sure we have valid patient ID and name
    if ($patient_id >= 0 && !empty($patient_name)) {
        // First verify the patient exists and is in queue using both ID and name
        $check_query = mysqli_query($conn, "SELECT name FROM patients WHERE id = $patient_id AND name = '$patient_name' AND time_out = 0");
        if ($check_query && mysqli_num_rows($check_query) > 0) {
            $patient_data = mysqli_fetch_assoc($check_query);
            $patient_name = $patient_data['name'];
            
            // Update the patient's time_out using both ID and name for unique identification
            $time_out = time();
            $update_sql = "UPDATE patients SET time_out = $time_out WHERE id = $patient_id AND name = '$patient_name' AND time_out = 0 LIMIT 1";
            
            if (mysqli_query($conn, $update_sql)) {
                $affected_rows = mysqli_affected_rows($conn);
                if ($affected_rows == 1) {
                    $checkout_message = "Patient '$patient_name' has been successfully checked out.";
                    $checkout_success = true;
                } elseif ($affected_rows == 0) {
                    $checkout_message = "Patient was not checked out. Patient may have already been checked out or not found.";
                    $checkout_success = false;
                } else {
                    $checkout_message = "Multiple patients were affected. Checkout cancelled for safety.";
                    $checkout_success = false;
                }
            } else {
                $checkout_message = "Error checking out patient: " . mysqli_error($conn);
                $checkout_success = false;
            }
        } else {
            $checkout_message = "Patient not found or already checked out.";
            $checkout_success = false;
        }
    } else {
        $checkout_message = "Invalid patient ID or name.";
        $checkout_success = false;
    }
}

// Get patients in queue (time_out = 0)
$queue_patients = mysqli_query($conn, "SELECT * FROM patients WHERE time_out = 0 ORDER BY time_in ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Queue - Iska-Care</title>
    <link rel="stylesheet" href="global.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"/>
</head>
<body>
    <div class="dashboard">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <a href="dashboard.php" class="sidebar-icon" data-tooltip="Dashboard">
                <i class="bx bx-pulse"></i>
            </a>
            <a href="add_patient.php" class="sidebar-icon" data-tooltip="Add Patient Record">
                <i class="bx bx-user-plus"></i>
            </a>
            <div class="sidebar-icon active" data-tooltip="Patient Queue">
                <i class="bx bx-list-ol"></i>
            </div>
            <a href="view_patients.php" class="sidebar-icon" data-tooltip="View Records">
                <i class="bx bx-folder"></i>
            </a>
             <a href="about.php" class="sidebar-icon" data-tooltip="About Us">
                <i class="bx bx-info-circle"></i>
            </a>
            <a href="?logout=true" class="sidebar-icon" data-tooltip="Logout">
                <i class="bx bx-log-out"></i>
            </a>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <div class="header">
                <h1>Patient Queue</h1>
                <div style="display: flex; gap: 10px;">
                    <a href="add_patient.php" style="background: #c40202; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Add Patient</a>
                    <a href="dashboard.php" style="color: #c40202; text-decoration: none; padding: 10px;">← Back to Dashboard</a>
                </div>
            </div>

            <?php if (isset($checkout_message)): ?>
                <div style="background: <?php echo isset($checkout_success) && $checkout_success ? '#d4edda' : '#f8d7da'; ?>; 
                            color: <?php echo isset($checkout_success) && $checkout_success ? '#155724' : '#721c24'; ?>; 
                            padding: 15px; margin: 20px; border-radius: 5px; 
                            border: 1px solid <?php echo isset($checkout_success) && $checkout_success ? '#c3e6cb' : '#f5c6cb'; ?>;">
                    <strong><?php echo isset($checkout_success) && $checkout_success ? '✓ Success:' : '✗ Error:'; ?></strong>
                    <?php echo $checkout_message; ?>
                </div>
            <?php endif; ?>

            <div style="background: white; margin: 20px; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h2 style="margin-bottom: 20px; color: #c40202;">Current Queue (<?php echo mysqli_num_rows($queue_patients); ?> patients)</h2>
                
                <?php if (mysqli_num_rows($queue_patients) > 0): ?>
                    <div style="display: grid; gap: 15px;">
                        <?php while($patient = mysqli_fetch_assoc($queue_patients)): ?>
                            <div style="border: 1px solid #ddd; border-radius: 8px; padding: 20px; background: #f9f9f9;">
                                <div style="display: flex; justify-content: between; align-items: center;">
                                    <div style="flex: 1;">
                                        <h3 style="margin: 0 0 10px 0; color: #c40202;"><?php echo htmlspecialchars($patient['name']); ?></h3>
                                        <p style="margin: 5px 0;"><strong>Age:</strong> <?php echo $patient['age']; ?> | <strong>Gender:</strong> <?php echo $patient['gender']; ?></p>
                                        <p style="margin: 5px 0;"><strong>Condition:</strong> <?php echo htmlspecialchars($patient['condition_text']); ?></p>
                                        <?php
                                            
                                        date_default_timezone_set('Asia/Manila'); //This ensures that i displays Correct time
                                        $timeInDisplay = 'Unknown';
                                        if (!empty($patient['time_in'])) {
                                        // If it's numeric (Unix timestamp)
                                        if (is_numeric($patient['time_in'])) {
                                        $timeInDisplay = date('M j, Y h:i A', (int)$patient['time_in']);
                                        } 
                                        // If it's a valid datetime string (from MySQL)
                                        elseif (strtotime($patient['time_in']) !== false) {
                                        $timeInDisplay = date('M j, Y h:i A', strtotime($patient['time_in']));
                                        }
                                        } 
                                        elseif (!empty($patient['date_admitted']) && $patient['date_admitted'] != '0000-00-00') {
                                        $timeInDisplay = date('M j, Y h:i A', strtotime($patient['date_admitted']));
                                        } 
                                        else {
                                        // If no data, show the current real time (Philippine time)
                                        $timeInDisplay = date('M j, Y h:i A');
                                        }
                                        ?>
                                        <p style="margin: 5px 0;"><strong>Time In:</strong> <?php echo $timeInDisplay; ?></p>
                                        <p style="margin: 5px 0;"><strong>Doctor:</strong> <?php echo htmlspecialchars($patient['doctor_assigned']); ?></p>
                                    </div>
                                    <div style="margin-left: 20px;">
                                        <a href="?timeout=1&id=<?php echo $patient['id']; ?>&name=<?php echo urlencode($patient['name']); ?>" 
                                           style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;"
                                           onclick="return confirm('Mark patient <?php echo htmlspecialchars($patient['name']); ?> as checked out?')">
                                            Check Out
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <i class="bx bx-check-circle" style="font-size: 48px; color: #28a745;"></i>
                        <h3>No patients in queue</h3>
                        <p>All patients have been checked out!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
    document.querySelector('.bx-log-out').parentElement.addEventListener('click', function (e) {
        if (!confirm("Are you sure you want to logout?")) {
            e.preventDefault();
        }
    });
    </script>
</body>
</html>
