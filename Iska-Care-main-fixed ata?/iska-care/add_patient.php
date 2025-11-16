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

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $condition = $_POST['condition'];
    $doctor = $_SESSION['user']['username'];
    $date_admitted = date('Y-m-d');
    $time_in = time();
    $time_out = 0; // 0 means still in queue

    $student_id = isset($_POST['student_id']) ? mysqli_real_escape_string($conn, $_POST['student_id']) : '';
    $student_course = isset($_POST['student_course']) ? mysqli_real_escape_string($conn, $_POST['student_course']) : '';
    $student_year = isset($_POST['student_year']) ? mysqli_real_escape_string($conn, $_POST['student_year']) : '';
    $emergency_status = isset($_POST['emergency_status']) ? mysqli_real_escape_string($conn, $_POST['emergency_status']) : 'Non-Emergency';
    $patient_type = isset($_POST['patient_type']) ? mysqli_real_escape_string($conn, $_POST['patient_type']) : 'Non-Employee';

    // Ensure new columns exist
    $check_sid = mysqli_query($conn, "SHOW COLUMNS FROM patients LIKE 'student_id'");
    if (mysqli_num_rows($check_sid) == 0) {
        mysqli_query($conn, "ALTER TABLE patients ADD COLUMN student_id VARCHAR(50) NOT NULL DEFAULT ''");
    }
    $check_sc = mysqli_query($conn, "SHOW COLUMNS FROM patients LIKE 'student_course'");
    if (mysqli_num_rows($check_sc) == 0) {
        mysqli_query($conn, "ALTER TABLE patients ADD COLUMN student_course VARCHAR(100) NOT NULL DEFAULT ''");
    }
    $check_sy = mysqli_query($conn, "SHOW COLUMNS FROM patients LIKE 'student_year'");
    if (mysqli_num_rows($check_sy) == 0) {
        mysqli_query($conn, "ALTER TABLE patients ADD COLUMN student_year VARCHAR(100) NOT NULL DEFAULT ''");
    }
    $check_emergency = mysqli_query($conn, "SHOW COLUMNS FROM patients LIKE 'emergency_status'");
    if (mysqli_num_rows($check_emergency) == 0) {
        mysqli_query($conn, "ALTER TABLE patients ADD COLUMN emergency_status VARCHAR(20) NOT NULL DEFAULT 'Non-Emergency'");
    }
    $check_patient_type = mysqli_query($conn, "SHOW COLUMNS FROM patients LIKE 'patient_type'");
    if (mysqli_num_rows($check_patient_type) == 0) {
        mysqli_query($conn, "ALTER TABLE patients ADD COLUMN patient_type VARCHAR(20) NOT NULL DEFAULT 'Non-Employee'");
    }

    // Sanitize data
    $name = mysqli_real_escape_string($conn, $name);
    $age = (int)$age;
    $gender = mysqli_real_escape_string($conn, $gender);
    $condition = mysqli_real_escape_string($conn, $condition);

    // ✅ Check if Student ID already exists
    $check_duplicate = mysqli_query($conn, "SELECT * FROM patients WHERE student_id = '$student_id' LIMIT 1");

    if (mysqli_num_rows($check_duplicate) > 0) {
        // Found duplicate Student ID
        $message = "❌ Error: Student ID '$student_id' already exists! Please enter a unique ID.";
    } else {
        // Insert new patient
        $sql = "INSERT INTO patients (name, age, gender, condition_text, date_admitted, doctor_assigned, time_in, time_out, student_id, student_course, student_year, emergency_status, patient_type)
                VALUES ('$name', $age, '$gender', '$condition', '$date_admitted', '$doctor', $time_in, $time_out, '$student_id', '$student_course', '$student_year', '$emergency_status', '$patient_type')";

        if (mysqli_query($conn, $sql)) {
            $message = "✅ Patient added successfully!";
        } else {
            $message = "❌ Error: " . mysqli_error($conn);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Patient - Iska-Care</title>
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
            <div class="sidebar-icon active" data-tooltip="Add Patient Record">
                <i class="bx bx-user-plus"></i>
            </div>
            <a href="queue.php" class="sidebar-icon" data-tooltip="Patient Queue">
                <i class="bx bx-list-ol"></i>
            </a>
             <a href="view_patients.php" class="sidebar-icon" data-tooltip="View Records">
                <i class="bx bx-folder"></i>
            </a>
            <a href="monthly_report.php" class="sidebar-icon" data-tooltip="Monthly Report">
                <i class="bx bx-calendar"></i>
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
                <h1>Add New Patient</h1>
                <a href="dashboard.php" style="color: #c40202; text-decoration: none;">← Back to Dashboard</a>
            </div>

            <?php if ($message): ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; margin: 8px 0; border-radius: 5px;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div style="background: white; margin: 8px 0; padding: 15px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px;">Patient Name:</label>
                            <input type="text" name="name" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px;">Age:</label>
                            <input type="number" name="age" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px;">Student ID:</label>
                            <input type="text" name="student_id" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                        </div>
                        <div style="display: flex; gap: 12px;">
                  <div style="flex: 1;">
                     <label style="display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px;">Student Course</label>
                     <select name="student_course" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                        <option value="">Select Course</option>
                        <option value="BEED">BEED</option>
                        <option value="BPA">BPA</option>
                        <option value="BPA-FA">BPA-FA</option>
                        <option value="BS-Account">BS-Account</option>
                        <option value="BSAM">BSAM</option>
                        <option value="BS-Archi">BS-Archi</option>
                        <option value="BSBA-FM">BSBA-FM</option>
                        <option value="BSBA-MM">BSBA-MM</option>
                        <option value="BS-Bio">BS-Bio</option>
                        <option value="BSCE">BSCE</option>
                        <option value="BSED">BSED-MT</option>
                        <option value="BSEE">BSEE</option>
                        <option value="BSHM">BSHM</option>
                        <option value="BSIT">BSIT</option>
                        <option value="BSND">BSND</option>
                        <option value="BSOA">BSOA</option>
                        <option value="DCVET">DCVET</option>
                        <option value="DCET">DCET</option>
                        <option value="DEET">DEET</option>
                        <option value="DIT">DIT</option>
                        <option value="DOMT-LOM">DOMT-LOM</option>
                        <option value="DOMT-MOM">DOMT-MOM</option>
                    </select>
                </div>

                 <div style="flex: 1;">
                    <label style="display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px;">Year</label>
                    <select name="student_year" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                        <option value="">Select year</option>
                        <option value="1st">1st</option>
                        <option value="2nd">2nd</option>
                        <option value="3rd">3rd</option>
                        <option value="4th">4th</option>
                        <option value="Ladderize">Ladderize</option>
                        <option value="Overstaying">Overstaying</option>
                        </select>
                    </div>
                </div>
            </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px;">Gender:</label>
                            <select name="gender" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px;">Doctor Assigned:</label>
                            <input type="text" value="<?php echo $_SESSION['user']['username']; ?>" readonly style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; background: #f5f5f5; font-size: 14px;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px;">Emergency Status:</label>
                            <select name="emergency_status" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                                <option value="Non-Emergency" selected>Non-Emergency</option>
                                <option value="Emergency">Emergency</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px;">Patient Type:</label>
                            <select name="patient_type" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px;">
                                <option value="Non-Employee" selected>Non-Employee</option>
                                <option value="Employee">Employee</option>
                                <option value="Student">Student</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-bottom: 12px;">
                        <label style="display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px;">Medical Condition/Complaint:</label>
                        <textarea name="condition" required rows="4" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; resize: vertical; font-size: 14px;"></textarea>
                    </div>

                    <button type="submit" style="background: #c40202; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                        Add Patient to Queue
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
    //Display logout confirmation
    document.querySelector('.bx-log-out').parentElement.addEventListener('click', function (e) {
        if (!confirm("Are you sure you want to logout?")) {
            e.preventDefault();
        }
    });
    </script>
</body>
</html>

