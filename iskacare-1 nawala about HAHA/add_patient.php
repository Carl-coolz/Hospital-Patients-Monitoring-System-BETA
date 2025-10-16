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

    $sql = "INSERT INTO patients (name, age, gender, condition_text, date_admitted, doctor_assigned, time_in, time_out) 
            VALUES ('$name', '$age', '$gender', '$condition', '$date_admitted', '$doctor', '$time_in', '$time_out')";

    if (mysqli_query($conn, $sql)) {
        $message = "Patient added successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Patient - Iska-Care</title>
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
            <div class="sidebar-icon active" data-tooltip="Add Patient Record">
                <i class="bx bx-user-plus"></i>
            </div>
            <a href="view_patients.php" class="sidebar-icon" data-tooltip="View Records">
                <i class="bx bx-folder"></i>
            </a>
            <a href="queue.php" class="sidebar-icon" data-tooltip="Patient Queue">
                <i class="bx bx-list-ol"></i>
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
                <div style="background: #d4edda; color: #155724; padding: 15px; margin: 20px; border-radius: 5px;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div style="background: white; margin: 20px; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <form method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Patient Name:</label>
                            <input type="text" name="name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Age:</label>
                            <input type="number" name="age" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Gender:</label>
                            <select name="gender" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 5px; font-weight: bold;">Doctor Assigned:</label>
                            <input type="text" value="<?php echo $_SESSION['user']['username']; ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; background: #f5f5f5;">
                        </div>
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: bold;">Medical Condition/Complaint:</label>
                        <textarea name="condition" required rows="4" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; resize: vertical;"></textarea>
                    </div>

                    <button type="submit" style="background: #c40202; color: white; padding: 12px 30px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px;">
                        Add Patient to Queue
                    </button>
                </form>
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
