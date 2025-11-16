<?php
session_start();
include 'db.php';

// Logout action
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_destroy();
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

// Get dashboard statistics
$total_patients = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM patients"))['count'];
$current_queue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM patients WHERE time_out = 0"))['count'];
$checked_out = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM patients WHERE time_out != 0"))['count'];

// Get recent activities
$recent_activities = mysqli_query($conn, "SELECT * FROM patients ORDER BY id DESC LIMIT 5");

// Get chart data
$weekly_data = mysqli_query($conn, "SELECT COUNT(*) as count, DATE(date_admitted) as date FROM patients WHERE date_admitted >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(date_admitted) ORDER BY date");

// Get queue data by time periods
$morning_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM patients WHERE time_out = 0 AND HOUR(FROM_UNIXTIME(time_in)) BETWEEN 6 AND 12"))['count'];
$afternoon_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM patients WHERE time_out = 0 AND HOUR(FROM_UNIXTIME(time_in)) BETWEEN 12 AND 18"))['count'];
$evening_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM patients WHERE time_out = 0 AND HOUR(FROM_UNIXTIME(time_in)) BETWEEN 18 AND 23"))['count'];

// Get department/condition data for pie chart
$condition_data = mysqli_query($conn, "SELECT condition_text, COUNT(*) as count FROM patients GROUP BY condition_text ORDER BY count DESC LIMIT 5");

// Weekly trend data (Monday to Sunday)
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$weekly_counts = [];

// Initialize all days to 0
foreach ($days as $day) {
    $weekly_counts[$day] = 0;
}

// Query counts grouped by weekday (MySQL)
$result = mysqli_query($conn, "
    SELECT DAYNAME(date_admitted) AS weekday, COUNT(*) AS count
    FROM patients
    WHERE YEARWEEK(date_admitted, 1) = YEARWEEK(CURDATE(), 1)
    GROUP BY weekday
");

// Fill counts
while ($row = mysqli_fetch_assoc($result)) {
    $weekly_counts[$row['weekday']] = (int)$row['count'];
}

// Converts associative array to ordered numeric array (Mon–Sun)
$ordered_counts = [];
foreach ($days as $day) {
    $ordered_counts[] = $weekly_counts[$day];
}


$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Iska-Care | University Clinic Dashboard</title>
  <link rel="stylesheet" href="global.css" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"/>
</head>
<body>
  <div class="dashboard">
    <!-- SIDEBAR -->
    <div class="sidebar">
      <div class="sidebar-icon active" data-tooltip="Dashboard">
        <i class="bx bx-pulse"></i>
      </div>
      <a href="add_patient.php" class="sidebar-icon" data-tooltip="Add Patient Record">
        <i class="bx bx-user-plus"></i>
      </a>
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
        <h1>Iska-Care Dashboard</h1>
        <div class="search-bar">
          <span>🔍</span>
          <input type="text" placeholder="Search student record..." id="searchInput" onkeyup="searchPatients()" /> <!-- Function of searchPatients() is like to short things and filter it into a single array to search similar names inside the database -->
          <div id="searchResults" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ddd; border-radius: 8px; max-height: 300px; overflow-y: auto; z-index: 1000; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"></div>
        </div>
      </div>

      <!-- STATS -->
      <div class="stats">
        <div class="stat-card">
          <h3>Total Students Monitored</h3>
          <div class="stat-value">
            <?php echo $total_patients; ?> <span>Total</span>
          </div>
        </div>
        <div class="stat-card">
          <h3>Currently in Queue</h3>
          <div class="stat-value">
            <?php echo $current_queue; ?> <span>Active</span>
          </div>
        </div>
        <div class="stat-card">
          <h3>Checked-Out Students</h3>
          <div class="stat-value">
            <?php echo $checked_out; ?> <span>Completed</span>
          </div>
        </div>
      </div>

      <!-- CHART -->
      <div class="chart-container">
        <canvas id="patientsChart"></canvas>
      </div>

      <!-- BOTTOM CARDS -->
      <div class="bottom-cards">
        <div class="card">
          <div class="card-header">
            <h3>Queue by Time Period</h3>
            <div class="more-button" title="Live Data">📊</div>
          </div>
          <canvas id="queueChart"></canvas>
        </div>

        <div class="card">
          <div class="card-header">
            <h3>Top Medical Conditions</h3>
            <div class="more-button" title="Live Data">📊</div>
          </div>
          <canvas id="departmentChart"></canvas>
        </div>
      </div>
    </div>

    <!-- RIGHT SIDEBAR -->
    <div class="right-sidebar">
      <div class="profile">
        <div class="profile-image">
          <img src="images/pup.png" alt="Doctor" />
        </div>
        <div class="profile-info">
        <h3>Dr. <?php echo htmlspecialchars($user['username']); ?></h3>
          <p>Clinic Nurse</p>
        </div>
      </div>

      <div class="activity-list">
        <h3>Recent Clinic Visits</h3>
        <?php 
        if (mysqli_num_rows($recent_activities) > 0) {
            while($activity = mysqli_fetch_assoc($recent_activities)) {
                echo '<div class="activity-item">';
                echo '<div class="activity-image">';
                echo '<img src="images/user.png" alt="Student" />';
                echo '</div>';
                echo '<div class="activity-info">';
                echo '<h4>' . htmlspecialchars($activity['name']) . '</h4>';
                echo '<p>' . htmlspecialchars($activity['condition_text']) . '</p>';
                echo '<p>' . date('M j, Y', strtotime($activity['date_admitted'])) . '</p>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<div class="activity-item">';
            echo '<div class="activity-info">';
            echo '<h4>No recent visits</h4>';
            echo '<p>No patients have visited recently</p>';
            echo '</div>';
            echo '</div>';
        }
        ?>
      </div>
    </div>
  </div>

  <!-- CHARTS -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const patientsCtx = document.getElementById("patientsChart").getContext("2d");
    const queueCtx = document.getElementById("queueChart").getContext("2d");
    const departmentCtx = document.getElementById("departmentChart").getContext("2d");

    const options = {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    };

    // Patients Checked-In Trend (Dynamic)
    new Chart(patientsCtx, {
      type: "line",
      data: {
        labels: <?php echo json_encode($days); ?>,
        datasets: [{
          label: "Patients Checked-In",
          data: <?php echo json_encode($weekly_counts); ?>,
          borderColor: "#c40202",
          backgroundColor: "rgba(196, 2, 2, 0.2)",
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        ...options,
        plugins: {
          legend: { display: true },
          title: {
            display: true,
            text: 'Weekly Patient Admissions'
          }
        }
      }
    });

    // Queue Overview (Dynamic)
    new Chart(queueCtx, {
      type: "bar",
      data: {
        labels: ["Morning", "Afternoon", "Evening"],
        datasets: [{
          label: "Patients in Queue",
          data: [<?php echo $morning_count; ?>, <?php echo $afternoon_count; ?>, <?php echo $evening_count; ?>],
          backgroundColor: ["#8f0000", "#c40202", "#f87171"],
          borderRadius: 8
        }]
      },
      options: {
        ...options,
        plugins: {
          legend: { display: true },
          title: {
            display: true,
            text: 'Queue by Time Period'
          }
        }
      }
    });

    // Condition Distribution (Dynamic)
    <?php
    // Prepare condition data for JavaScript
    $condition_labels = [];
    $condition_counts = [];
    $condition_colors = ["#8f0000", "#c40202", "#f87171", "#fca5a5", "#fee2e2"];
    
    if (mysqli_num_rows($condition_data) > 0) {
        while($row = mysqli_fetch_assoc($condition_data)) {
            $condition_labels[] = $row['condition_text'];
            $condition_counts[] = $row['count'];
        }
    } else {
        $condition_labels[] = "No Data";
        $condition_counts[] = 1;
    }
    ?>
    
    new Chart(departmentCtx, {
      type: "doughnut",
      data: {
        labels: <?php echo json_encode($condition_labels); ?>,
        datasets: [{
          label: "Patient Conditions",
          data: <?php echo json_encode($condition_counts); ?>,
          backgroundColor: <?php echo json_encode(array_slice($condition_colors, 0, count($condition_labels))); ?>,
          borderWidth: 0,
          hoverOffset: 4,
        }]
      },
      options: { 
        cutout: "75%",
        plugins: {
          legend: {
            display: true,
            position: "right",
            labels: {
              boxWidth: 20,
              padding: 15,
              usePointStyle: true
            }
          },
          title: {
            display: true,
            text: 'Top Medical Conditions'
          }
        }
      }
    });
  </script> 
  <script>
document.querySelector('.bx-log-out').parentElement.addEventListener('click', function (e) {
  if (!confirm("Are you sure you want to logout?")) {
    e.preventDefault();
  }
});

function searchPatients() {
  const searchTerm = document.getElementById('searchInput').value.trim();
  const searchResults = document.getElementById('searchResults');
  
  if (searchTerm.length < 2) {
    searchResults.style.display = 'none';
    return;
  }

  // Create XMLHttpRequest for AJAX search
  const xhr = new XMLHttpRequest();
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4 && xhr.status === 200) {
      const results = JSON.parse(xhr.responseText);
      displaySearchResults(results);
    }
  };

  xhr.open('GET', 'search_patients.php?search=' + encodeURIComponent(searchTerm), true);
  xhr.send();
}

function displaySearchResults(results) {
  const searchResults = document.getElementById('searchResults');
  
  if (results.length === 0) {
    searchResults.innerHTML = '<div style="padding: 15px; color: #666; text-align: center;">No patients found</div>';
  } else {
    let html = '';
    results.forEach(patient => {
      html += `
        <div style="padding: 12px; border-bottom: 1px solid #eee; cursor: pointer; transition: background 0.2s;" 
             onmouseover="this.style.background='#f5f5f5'" 
             onmouseout="this.style.background='white'"
             onclick="viewPatient(${patient.id})">
          <div style="font-weight: 600; color: #c40202; margin-bottom: 4px;">${patient.name}</div>
          <div style="font-size: 13px; color: #666;">Age: ${patient.age} | ${patient.gender} | ${patient.condition_text}</div>
          <div style="font-size: 12px; color: #999;">Added: ${new Date(patient.date_admitted).toLocaleDateString()}</div>
        </div>
      `;
    });
    searchResults.innerHTML = html;
  }
  
  searchResults.style.display = 'block';
}

function viewPatient(patientId) {
  window.location.href = 'view_patients.php?id=' + patientId;
}

// Hide search results when clicking outside
document.addEventListener('click', function(e) {
  const searchBar = document.querySelector('.search-bar');
  const searchResults = document.getElementById('searchResults');
  
  if (!searchBar.contains(e.target)) {
    searchResults.style.display = 'none';
  }
});
</script>
</body>
</html>
