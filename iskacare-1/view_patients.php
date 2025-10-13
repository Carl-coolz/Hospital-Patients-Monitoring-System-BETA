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

            <div style="background: white; margin: 20px; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <h2 style="margin-bottom: 20px; color: #c40202;">All Patient Records (<?php echo mysqli_num_rows($patients); ?> total)</h2>
                
                <?php if (mysqli_num_rows($patients) > 0): ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #f8f9fa;">
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Patient Name</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Age/Gender</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Condition</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Date Admitted</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Status</th>
                                    <th style="padding: 15px; text-align: left; border-bottom: 2px solid #ddd;">Doctor</th>
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
                                            <?php echo htmlspecialchars($patient['condition_text']); ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php echo date('M j, Y', strtotime($patient['date_admitted'])); ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php if ($patient['time_out'] == 0): ?>
                                                <span style="background: #ffc107; color: #000; padding: 5px 10px; border-radius: 15px; font-size: 12px;">
                                                    In Queue
                                                </span>
                                            <?php else: ?>
                                                <span style="background: #28a745; color: white; padding: 5px 10px; border-radius: 15px; font-size: 12px;">
                                                    Checked Out
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding: 15px;">
                                            <?php echo htmlspecialchars($patient['doctor_assigned']); ?>
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
    </script>
</body>
</html>
