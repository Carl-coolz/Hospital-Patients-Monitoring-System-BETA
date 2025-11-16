<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Get patient ID from URL
$patient_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($patient_id <= 0) {
    die("Invalid patient ID");
}

// Fetch patient data
$query = "SELECT * FROM patients WHERE id = $patient_id LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("Patient not found");
}

$patient = mysqli_fetch_assoc($result);

// Format dates
$date_admitted = date('F j, Y', strtotime($patient['date_admitted']));
$time_in_display = 'Unknown';
if (!empty($patient['time_in'])) {
    if (is_numeric($patient['time_in'])) {
        $time_in_display = date('F j, Y h:i A', (int)$patient['time_in']);
    } elseif (strtotime($patient['time_in']) !== false) {
        $time_in_display = date('F j, Y h:i A', strtotime($patient['time_in']));
    }
}

$time_out_display = 'N/A';
if (!empty($patient['time_out']) && $patient['time_out'] != 0) {
    if (is_numeric($patient['time_out'])) {
        $time_out_display = date('F j, Y h:i A', (int)$patient['time_out']);
    } elseif (strtotime($patient['time_out']) !== false) {
        $time_out_display = date('F j, Y h:i A', strtotime($patient['time_out']));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Record - <?php echo htmlspecialchars($patient['name']); ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }

        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #c40202;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #c40202;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            color: #666;
            font-size: 14px;
        }

        .patient-info {
            margin-bottom: 30px;
        }

        .info-section {
            margin-bottom: 25px;
        }

        .info-section h2 {
            color: #c40202;
            font-size: 18px;
            margin-bottom: 15px;
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
        }

        .info-row {
            display: flex;
            margin-bottom: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .info-label {
            font-weight: bold;
            width: 200px;
            color: #333;
        }

        .info-value {
            flex: 1;
            color: #555;
        }

        .emergency-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
        }

        .emergency-badge.emergency {
            background: #dc3545;
            color: white;
        }

        .emergency-badge.non-emergency {
            background: #28a745;
            color: white;
        }

        .patient-type-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            background: #007bff;
            color: white;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #eee;
            text-align: center;
            color: #666;
            font-size: 12px;
        }

        .print-actions {
            text-align: center;
            margin: 20px 0;
            padding: 20px;
            background: white;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .btn {
            padding: 12px 30px;
            margin: 0 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-print {
            background: #c40202;
            color: white;
        }

        .btn-print:hover {
            background: #a00202;
        }

        .btn-back {
            background: #6c757d;
            color: white;
        }

        .btn-back:hover {
            background: #5a6268;
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .print-actions {
                display: none;
            }

            .print-container {
                box-shadow: none;
                padding: 20px;
            }

            .info-row {
                page-break-inside: avoid;
            }

            @page {
                margin: 1cm;
            }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button onclick="window.print()" class="btn btn-print">🖨️ Print / Save as PDF</button>
        <a href="view_patients.php" class="btn btn-back">← Back to Records</a>
    </div>

    <div class="print-container">
        <div class="header">
            <h1>ISKA-CARE</h1>
            <p>Patient Medical Record</p>
        </div>

        <div class="patient-info">
            <div class="info-section">
                <h2>Personal Information</h2>
                <div class="info-row">
                    <div class="info-label">Patient Name:</div>
                    <div class="info-value"><strong><?php echo htmlspecialchars($patient['name']); ?></strong></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Age:</div>
                    <div class="info-value"><?php echo $patient['age']; ?> years old</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Gender:</div>
                    <div class="info-value"><?php echo htmlspecialchars($patient['gender']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Patient Type:</div>
                    <div class="info-value">
                        <span class="patient-type-badge"><?php echo htmlspecialchars(isset($patient['patient_type']) ? $patient['patient_type'] : 'Non-Employee'); ?></span>
                    </div>
                </div>
            </div>

            <div class="info-section">
                <h2>Student Information</h2>
                <div class="info-row">
                    <div class="info-label">Student ID:</div>
                    <div class="info-value"><?php echo htmlspecialchars($patient['student_id']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Course:</div>
                    <div class="info-value"><?php echo htmlspecialchars($patient['student_course']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Year Level:</div>
                    <div class="info-value"><?php echo htmlspecialchars($patient['student_year']); ?></div>
                </div>
            </div>

            <div class="info-section">
                <h2>Medical Information</h2>
                <div class="info-row">
                    <div class="info-label">Emergency Status:</div>
                    <div class="info-value">
                        <?php 
                        $emergency_status = isset($patient['emergency_status']) ? $patient['emergency_status'] : 'Non-Emergency';
                        $emergency_class = strtolower($emergency_status) == 'emergency' ? 'emergency' : 'non-emergency';
                        ?>
                        <span class="emergency-badge <?php echo $emergency_class; ?>">
                            <?php echo htmlspecialchars($emergency_status); ?>
                        </span>
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-label">Medical Condition:</div>
                    <div class="info-value"><?php echo nl2br(htmlspecialchars($patient['condition_text'])); ?></div>
                </div>
            </div>

            <div class="info-section">
                <h2>Visit Information</h2>
                <div class="info-row">
                    <div class="info-label">Date Admitted:</div>
                    <div class="info-value"><?php echo $date_admitted; ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Time In:</div>
                    <div class="info-value"><?php echo $time_in_display; ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Time Out:</div>
                    <div class="info-value"><?php echo $time_out_display; ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Doctor Assigned:</div>
                    <div class="info-value"><?php echo htmlspecialchars($patient['doctor_assigned']); ?></div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p>This is a computer-generated document. No signature required.</p>
            <p>Generated on: <?php echo date('F j, Y h:i A'); ?></p>
        </div>
    </div>

    <script>
        // Auto-trigger print dialog on page load (optional - commented out)
        // window.onload = function() {
        //     window.print();
        // };
    </script>
</body>
</html>

