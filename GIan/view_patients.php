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

// Handle checkout action
if (isset($_GET['checkout']) && isset($_GET['id']) && isset($_GET['name'])) {
    $patient_id = intval($_GET['id']);
    $patient_name = mysqli_real_escape_string($conn, $_GET['name']);
    
    if ($patient_id >= 0 && !empty($patient_name)) {
        // Get patient details first using both ID and name for unique identification
        $get_patient = mysqli_query($conn, "SELECT name, time_out FROM patients WHERE id = $patient_id AND name = '$patient_name'");
        
        if ($get_patient && mysqli_num_rows($get_patient) > 0) {
            $patient = mysqli_fetch_assoc($get_patient);
            $patient_name = $patient['name'];
            $is_checked_out = $patient['time_out'] != 0;
            
            if (!$is_checked_out) {
                // Start transaction for safe checkout
                mysqli_begin_transaction($conn);
                
                try {
                    // Update the patient's time_out using both ID and name to ensure only one patient is updated
                    $time_out = time();
                    $update_result = mysqli_query($conn, "UPDATE patients SET time_out = $time_out WHERE id = $patient_id AND name = '$patient_name' AND time_out = 0 LIMIT 1");
                    
                    if (!$update_result) {
                        throw new Exception("Error checking out patient: " . mysqli_error($conn));
                    }
                    
                    // Check if exactly one row was updated
                    $affected_rows = mysqli_affected_rows($conn);
                    if ($affected_rows == 1) {
                        mysqli_commit($conn);
                        $checkout_message = "Patient '$patient_name' has been successfully checked out!";
                        $checkout_success = true;
                    } elseif ($affected_rows == 0) {
                        throw new Exception("No patient was checked out. Patient may have already been checked out or not found.");
                    } else {
                        throw new Exception("Multiple patients were affected. Checkout cancelled for safety.");
                    }
                    
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $checkout_message = $e->getMessage();
                    $checkout_success = false;
                }
            } else {
                $checkout_message = "Patient '$patient_name' is already checked out.";
                $checkout_success = false;
            }
        } else {
            $checkout_message = "Patient not found with the specified ID and name.";
            $checkout_success = false;
        }
    } else {
        $checkout_message = "Invalid patient ID or name.";
        $checkout_success = false;
    }
}

// Handle delete action
if (isset($_GET['delete']) && isset($_GET['id']) && isset($_GET['name'])) {
    $patient_id = intval($_GET['id']);
    $patient_name = mysqli_real_escape_string($conn, $_GET['name']);
    
    if ($patient_id >= 0 && !empty($patient_name)) {
        // Get patient details first using both ID and name for unique identification
        $get_patient = mysqli_query($conn, "SELECT name, time_out FROM patients WHERE id = $patient_id AND name = '$patient_name'");
        
        if ($get_patient && mysqli_num_rows($get_patient) > 0) {
            $patient = mysqli_fetch_assoc($get_patient);
            $patient_name = $patient['name'];
            $is_checked_out = $patient['time_out'] != 0;
            
            // Start transaction for safe deletion
            mysqli_begin_transaction($conn);
            
            try {
                // First, delete any related records in monitoring table using both ID and name
                $delete_delete_table = mysqli_query($conn, "DELETE FROM delete_table WHERE patient_id = $patient_id");
                if (!$delete_delete_table) {
                    throw new Exception("Error deleting delete_table records: " . mysqli_error($conn));
                }
                
                // Then delete the specific patient using both ID and name to ensure only one patient is deleted
                $delete_result = mysqli_query($conn, "DELETE FROM patients WHERE id = $patient_id AND name = '$patient_name' LIMIT 1");
                if (!$delete_result) {
                    throw new Exception("Error deleting patient: " . mysqli_error($conn));
                }
                
                // Check if exactly one row was deleted
                $affected_rows = mysqli_affected_rows($conn);
                if ($affected_rows == 1) {
                    mysqli_commit($conn);
                    $status_text = $is_checked_out ? "checked-out" : "queued";
                    $delete_message = "Patient '$patient_name' ($status_text) deleted successfully!";
                    $delete_success = true;
                } elseif ($affected_rows == 0) {
                    throw new Exception("No patient was deleted. Patient may have already been removed or not found.");
                } else {
                    throw new Exception("Multiple patients were affected. Deletion cancelled for safety.");
                }
                
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $delete_message = $e->getMessage();
                $delete_success = false;
            }
        } else {
            $delete_message = "Patient not found with the specified ID and name.";
            $delete_success = false;
        }
    } else {
        $delete_message = "Invalid patient ID or name.";
        $delete_success = false;
    }
}

// Search functionality
$search = isset($_GET['search']) ? $_GET['search'] : '';
$where_clause = '';
if (!empty($search)) {
    $where_clause = "WHERE name LIKE '%$search%' OR condition_text LIKE '%$search%'";
}

$patients = mysqli_query($conn, "SELECT * FROM patients $where_clause ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patients - Iska-Care</title>
    <link rel="stylesheet" href="dash.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"/>
    <style>
        .delete-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .delete-btn:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .delete-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        .bx-spin {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .message-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
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
            <a href="queue.php" class="sidebar-icon" data-tooltip="Patient Queue">
                <i class="bx bx-list-ol"></i>
            </a>
            <div class="sidebar-icon active" data-tooltip="View Records">
                <i class="bx bx-folder"></i>
            </div>
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
                <h1>Patient Records</h1>
                <div style="display: flex; gap: 10px;">
                    <a href="add_patient.php" style="background: #c40202; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Add Patient</a>
                    <a href="dashboard.php" style="color: #c40202; text-decoration: none; padding: 10px;">← Back to Dashboard</a>
                </div>
            </div>

            <!-- Search Bar -->
            <div style="background: white; margin: 20px; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <form method="GET" style="display: flex; gap: 10px;">
                    <input type="text" name="search" placeholder="Search by name or condition..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <button type="submit" style="background: #c40202; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                        Search
                    </button>
                    <?php if (!empty($search)): ?>
                        <a href="view_patients.php" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">
                            Clear
                        </a>
                    <?php endif; ?>
                </form>
            </div>

            <?php if (isset($checkout_message)): ?>
                <div class="<?php echo isset($checkout_success) && $checkout_success ? 'message-success' : 'message-error'; ?>" 
                     style="padding: 15px; margin: 20px; border-radius: 5px;">
                    <strong><?php echo isset($checkout_success) && $checkout_success ? '✓ Success:' : '✗ Error:'; ?></strong>
                    <?php echo $checkout_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($delete_message)): ?>
                <div class="<?php echo isset($delete_success) && $delete_success ? 'message-success' : 'message-error'; ?>" 
                     style="padding: 15px; margin: 20px; border-radius: 5px;">
                    <strong><?php echo isset($delete_success) && $delete_success ? '✓ Success:' : '✗ Error:'; ?></strong>
                    <?php echo $delete_message; ?>
                </div>
            <?php endif; ?>

            <div style="background: white; margin: 20px; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h2 style="margin-bottom: 20px; color: #c40202;">All Patient Records (<?php echo mysqli_num_rows($patients); ?> total)</h2>
                
                <?php if (mysqli_num_rows($patients) > 0): ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8f9fa;">
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Patient Name</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Age/Gender</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">ID</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Course</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Condition</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Date Admitted</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Status</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Doctor</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($patient = mysqli_fetch_assoc($patients)): ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td style="padding: 15px;">
                                            <strong><?php echo htmlspecialchars($patient['name']); ?></strong>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php echo $patient['age']; ?> / <?php echo $patient['gender']; ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php echo htmlspecialchars($patient['student_id']); ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php echo htmlspecialchars($patient['student_course']); ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php echo htmlspecialchars($patient['condition_text']); ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php echo date('M j, Y', strtotime($patient['date_admitted'])); ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php if ($patient['time_out'] == 0): ?>
                                                <span style="background: #ffc107; color: #000; padding: 5px 10px; border-radius: 15px; font-size: 12px;">
                                                    Queue
                                                </span>
                                            <?php else: ?>
                                                <span style="background: #28a745; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;">
                                                    Out
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php echo htmlspecialchars($patient['doctor_assigned']); ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <button onclick="deletePatient(<?php echo $patient['id']; ?>, '<?php echo htmlspecialchars($patient['name']); ?>', <?php echo $patient['time_out'] != 0 ? 'true' : 'false'; ?>)" 
                                                    class="delete-btn"
                                                    title="Delete Patient">
                                                <i class="bx bx-trash"></i> Del
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px; color: #666;">
                        <i class="bx bx-user-x" style="font-size: 48px; color: #6c757d;"></i>
                        <h3>No patients found</h3>
                        <p><?php echo !empty($search) ? 'Try a different search term.' : 'No patients have been added yet.'; ?></p>
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

    function deletePatient(patientId, patientName, isCheckedOut) {
        // Show loading state
        const deleteBtn = event.target.closest('button');
        const originalText = deleteBtn.innerHTML;
        deleteBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Deleting...';
        deleteBtn.disabled = true;
        
        const statusText = isCheckedOut ? "checked-out" : "queued";
        
        // Double confirmation for safety
        if (confirm(`Are you sure you want to delete ${statusText} patient "${patientName}"?\n\nThis action cannot be undone.`)) {
            if (confirm(`FINAL CONFIRMATION: Delete ${statusText} patient "${patientName}"?\n\nClick OK to proceed with deletion.`)) {
                // Add a small delay to show the loading state
                setTimeout(() => {
                    // Encode the patient name for URL safety
                    const encodedName = encodeURIComponent(patientName);
                    window.location.href = `view_patients.php?delete=1&id=${patientId}&name=${encodedName}`;
                }, 500);
            } else {
                // Restore button if user cancels
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
            }
        } else {
            // Restore button if user cancels
            deleteBtn.innerHTML = originalText;
            deleteBtn.disabled = false;
        }
    }
    </script>
</body>
</html>
