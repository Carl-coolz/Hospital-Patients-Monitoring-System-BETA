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

// Get selected month and year from GET parameters, default to current month
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Validate month and year
if ($selected_month < 1 || $selected_month > 12) {
    $selected_month = date('n');
}
if ($selected_year < 2000 || $selected_year > 2100) {
    $selected_year = date('Y');
}

// Get patients for selected month
$start_date = sprintf('%04d-%02d-01', $selected_year, $selected_month);
$end_date = date('Y-m-t', strtotime($start_date)); // Last day of the month

$query = "SELECT * FROM patients 
          WHERE DATE(date_admitted) >= '$start_date' 
          AND DATE(date_admitted) <= '$end_date'
          ORDER BY date_admitted ASC, time_in ASC";

$patients_result = mysqli_query($conn, $query);
$total_patients = mysqli_num_rows($patients_result);

// Get statistics for the month
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

// Month names
$month_names = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

$month_name = $month_names[$selected_month];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Report - Iska-Care</title>
    <link rel="stylesheet" href="global.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"/>
    <style>
        .report-header {
            background: white;
            margin: 8px 0;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .filter-section {
            display: flex;
            gap: 12px;
            align-items: end;
            margin-bottom: 15px;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        .filter-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #c40202;
            color: white;
        }

        .btn-primary:hover {
            background: #a00202;
        }

        .btn-print {
            background: #17a2b8;
            color: white;
        }

        .btn-print:hover {
            background: #138496;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 15px;
        }

        .stat-box {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 5px;
            border-left: 4px solid #c40202;
        }

        .stat-box h4 {
            margin: 0 0 5px 0;
            color: #666;
            font-size: 14px;
        }

        .stat-box .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #c40202;
        }

        .patients-table {
            background: white;
            margin: 8px 0;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .print-actions {
            text-align: center;
            margin: 8px 0;
            padding: 12px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        /* Print Styles */
        @media print {
            .sidebar, .print-actions, .filter-section, .btn {
                display: none !important;
            }

            .report-header, .patients-table {
                box-shadow: none;
                margin: 0;
                padding: 20px;
            }

            body {
                background: white;
            }

            .patient-row {
                page-break-inside: avoid;
                border-bottom: 1px solid #ddd;
                padding: 15px 0;
            }

            @page {
                margin: 1cm;
            }
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
            <a href="view_patients.php" class="sidebar-icon" data-tooltip="View Records">
                <i class="bx bx-folder"></i>
            </a>
            <div class="sidebar-icon active" data-tooltip="Monthly Report">
                <i class="bx bx-calendar"></i>
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
                <h1>Monthly Report</h1>
                <a href="view_patients.php" style="color: #c40202; text-decoration: none;">← Back to Records</a>
            </div>

            <div class="report-header">
                <div class="filter-section">
                    <div class="filter-group">
                        <label>Select Month:</label>
                        <select id="monthSelect" onchange="updateReport()">
                            <?php for($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo $selected_month == $m ? 'selected' : ''; ?>>
                                    <?php echo $month_names[$m]; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Select Year:</label>
                        <select id="yearSelect" onchange="updateReport()">
                            <?php 
                            $current_year = date('Y');
                            for($y = $current_year; $y >= $current_year - 5; $y--): 
                            ?>
                                <option value="<?php echo $y; ?>" <?php echo $selected_year == $y ? 'selected' : ''; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <button type="button" onclick="updateReport()" class="btn btn-primary">
                            <i class="bx bx-search"></i> Generate Report
                        </button>
                    </div>
                </div>

                <div class="stats-grid">
                    <div class="stat-box">
                        <h4>Total Patients</h4>
                        <div class="stat-value"><?php echo $total_patients; ?></div>
                    </div>
                    <div class="stat-box">
                        <h4>Emergency Cases</h4>
                        <div class="stat-value"><?php echo $emergency_count; ?></div>
                    </div>
                    <div class="stat-box">
                        <h4>Employees</h4>
                        <div class="stat-value"><?php echo $employee_count; ?></div>
                    </div>
                    <div class="stat-box">
                        <h4>Non-Employees</h4>
                        <div class="stat-value"><?php echo $non_employee_count; ?></div>
                    </div>
                </div>
            </div>

            <div class="print-actions">
                <button onclick="window.print()" class="btn btn-print">
                    <i class="bx bx-printer"></i> Print / Save as PDF
                </button>
                <a href="export_monthly_report.php?month=<?php echo $selected_month; ?>&year=<?php echo $selected_year; ?>" 
                   class="btn" 
                   style="background: #28a745; color: white;">
                    <i class="bx bx-spreadsheet"></i> Export to Excel
                </a>
                <a href="view_patients.php" class="btn" style="background: #6c757d; color: white;">
                    ← Back to Records
                </a>
            </div>

            <div class="patients-table">
                <h2 style="margin-bottom: 12px; color: #c40202; font-size: 20px;">
                    Patient Records for <?php echo $month_name . ' ' . $selected_year; ?>
                    <span style="font-size: 14px; color: #666; font-weight: normal;">
                        (<?php echo $total_patients; ?> records)
                    </span>
                </h2>

                <?php if ($total_patients > 0): ?>
                    <?php 
                    // Reset result pointer
                    mysqli_data_seek($patients_result, 0);
                    $counter = 1;
                    ?>
                    <?php while($patient = mysqli_fetch_assoc($patients_result)): ?>
                        <div class="patient-row" style="border-bottom: 2px solid #eee; padding: 15px 0; margin-bottom: 15px;">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                                <div>
                                    <h3 style="margin: 0 0 8px 0; color: #c40202; font-size: 16px;">
                                        #<?php echo $counter++; ?>. <?php echo htmlspecialchars($patient['name']); ?>
                                    </h3>
                                    <p style="margin: 4px 0; color: #666; font-size: 13px;">
                                        <strong>Age:</strong> <?php echo $patient['age']; ?> years | 
                                        <strong>Gender:</strong> <?php echo htmlspecialchars($patient['gender']); ?>
                                    </p>
                                    <p style="margin: 4px 0; color: #666; font-size: 13px;">
                                        <strong>Student ID:</strong> <?php echo htmlspecialchars($patient['student_id']); ?> | 
                                        <strong>Course:</strong> <?php echo htmlspecialchars($patient['student_course']); ?> | 
                                        <strong>Year:</strong> <?php echo htmlspecialchars($patient['student_year']); ?>
                                    </p>
                                </div>
                                <div style="text-align: right;">
                                    <?php 
                                    $emergency_status = isset($patient['emergency_status']) ? $patient['emergency_status'] : 'Non-Emergency';
                                    $emergency_color = strtolower($emergency_status) == 'emergency' ? '#dc3545' : '#28a745';
                                    $patient_type = isset($patient['patient_type']) ? $patient['patient_type'] : 'Non-Employee';
                                    ?>
                                    <span style="background: <?php echo $emergency_color; ?>; color: white; padding: 5px 15px; border-radius: 15px; font-size: 12px; margin-left: 5px;">
                                        <?php echo htmlspecialchars($emergency_status); ?>
                                    </span>
                                    <span style="background: #007bff; color: white; padding: 5px 15px; border-radius: 15px; font-size: 12px; margin-left: 5px;">
                                        <?php echo htmlspecialchars($patient_type); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div style="background: #f8f9fa; padding: 12px; border-radius: 5px; margin-bottom: 10px;">
                                <p style="margin: 0; font-size: 13px;"><strong>Medical Condition:</strong> <?php echo nl2br(htmlspecialchars($patient['condition_text'])); ?></p>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; font-size: 12px; color: #666;">
                                <div>
                                    <strong>Date Admitted:</strong><br>
                                    <?php echo date('M j, Y', strtotime($patient['date_admitted'])); ?>
                                </div>
                                <div>
                                    <strong>Time In:</strong><br>
                                    <?php 
                                    $time_in_display = 'Unknown';
                                    if (!empty($patient['time_in'])) {
                                        if (is_numeric($patient['time_in'])) {
                                            $time_in_display = date('h:i A', (int)$patient['time_in']);
                                        } elseif (strtotime($patient['time_in']) !== false) {
                                            $time_in_display = date('h:i A', strtotime($patient['time_in']));
                                        }
                                    }
                                    echo $time_in_display;
                                    ?>
                                </div>
                                <div>
                                    <strong>Time Out:</strong><br>
                                    <?php 
                                    $time_out_display = 'N/A';
                                    if (!empty($patient['time_out']) && $patient['time_out'] != 0) {
                                        if (is_numeric($patient['time_out'])) {
                                            $time_out_display = date('h:i A', (int)$patient['time_out']);
                                        } elseif (strtotime($patient['time_out']) !== false) {
                                            $time_out_display = date('h:i A', strtotime($patient['time_out']));
                                        }
                                    }
                                    echo $time_out_display;
                                    ?>
                                </div>
                                <div>
                                    <strong>Doctor:</strong><br>
                                    <?php echo htmlspecialchars($patient['doctor_assigned']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="text-align: center; padding: 20px; color: #666;">
                        <i class="bx bx-calendar-x" style="font-size: 36px; color: #6c757d;"></i>
                        <h3 style="margin: 10px 0 5px 0;">No records found</h3>
                        <p style="margin: 0;">No patients were recorded for <?php echo $month_name . ' ' . $selected_year; ?>.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function updateReport() {
            const month = document.getElementById('monthSelect').value;
            const year = document.getElementById('yearSelect').value;
            window.location.href = 'monthly_report.php?month=' + month + '&year=' + year;
        }

        document.querySelector('.bx-log-out').parentElement.addEventListener('click', function (e) {
            if (!confirm("Are you sure you want to logout?")) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>

