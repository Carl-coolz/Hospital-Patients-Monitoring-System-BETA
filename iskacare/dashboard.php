<?php
session_start();
include 'db.php';

// Logout action
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Iska-Care | University Clinic Dashboard</title>
  <link rel="stylesheet" href="dash.css" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"/>
</head>
<body>
  <div class="dashboard">
    <!-- SIDEBAR -->
    <div class="sidebar">
      <div class="sidebar-icon active" data-tooltip="Dashboard">
        <i class="bx bx-pulse"></i>
      </div>
      <div class="sidebar-icon" data-tooltip="Add Patient Record">
        <i class="bx bx-user-plus"></i>
      </div>
      <div class="sidebar-icon" data-tooltip="Patient Queue">
        <i class="bx bx-list-ol"></i>
      </div>
      <div class="sidebar-icon" data-tooltip="View Records">
        <i class="bx bx-folder"></i>
      </div>
      <div class="sidebar-icon" data-tooltip="Linked Patient Records">
        <i class="bx bx-link-alt"></i>
      </div>
      <div class="sidebar-icon" data-tooltip="Time-In / Time-Out">
        <i class="bx bx-time-five"></i>
      </div>
      <div class="sidebar-icon" data-tooltip="Settings">
        <i class="bx bx-cog"></i>
      </div>
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
          <input type="text" placeholder="Search student record..." />
        </div>
      </div>

      <!-- STATS -->
      <div class="stats">
        <div class="stat-card">
          <h3>Total Students Monitored</h3>
          <div class="stat-value">
            128 <span>+10 Today</span>
          </div>
        </div>
        <div class="stat-card">
          <h3>Currently in Queue</h3>
          <div class="stat-value">
            6 <span>+2</span>
          </div>
        </div>
        <div class="stat-card">
          <h3>Checked-Out Students</h3>
          <div class="stat-value">
            89 <span>+5</span>
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
            <h3>Patient Queue Overview</h3>
            <div class="more-button">⋮</div>
          </div>
          <canvas id="queueChart"></canvas>
        </div>

        <div class="card">
          <div class="card-header">
            <h3>Visits per Department</h3>
            <div class="more-button">⋮</div>
          </div>
          <canvas id="departmentChart"></canvas>
        </div>
      </div>
    </div>

    <!-- RIGHT SIDEBAR -->
    <div class="right-sidebar">
      <div class="profile">
        <div class="profile-image">
          <img src="pup.png" alt="Doctor Profile" />
        </div>
        <div class="profile-info">
          <h3>Dr. test</h3>
          <p>University Physician</p>
        </div>
      </div>

      <div class="activity-list">
        <h3>Recent Clinic Visits</h3>
        <div class="activity-item">
          <div class="activity-image">
            <img src="pup.png" alt="Student" />
          </div>
          <div class="activity-info">
            <h4>test</h4>
            <p>BSIT — Headache</p>
            <p>5 mins ago</p>
          </div>
        </div>
        <div class="activity-item">
          <div class="activity-image">
            <img src="pup.png" alt="Student" />
          </div>
          <div class="activity-info">
            <h4>test2</h4>
            <p>BSED — Stomach Pain</p>
            <p>15 mins ago</p>
          </div>
        </div>
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

    // Patients Checked-In Trend
    new Chart(patientsCtx, {
      type: "line",
      data: {
        labels: ["Mon", "Tue", "Wed", "Thu", "Fri"],
        datasets: [{
          label: "Patients Checked-In",
          data: [5, 9, 12, 7, 10],
          borderColor: "#c40202",
          backgroundColor: "rgba(196, 2, 2, 0.2)",
          fill: true,
          tension: 0.4
        }]
      },
      options
    });

    // Queue Overview
    new Chart(queueCtx, {
      type: "bar",
      data: {
        labels: ["Morning", "Afternoon", "Evening"],
        datasets: [{
          data: [4, 6, 2],
          backgroundColor: "#8f0000",
          borderRadius: 8
        }]
      },
      options
    });

    // Department Visits
    new Chart(departmentCtx, {
  type: "doughnut",
  data: {
    labels: ["TEST", "TEST2"],
    datasets: [{
      data: [2,5],
      backgroundColor: ["#8f0000","#c40202","#f87171","#fca5a5","#fee2e2"],
      borderWidth: 0,
      hoverOffset: 4,
    }]
  },
  options: { 
    cutout: "75%",
    plugins: {
      legend: {
        display: true,
        position: "right",  // Moves legend to the right
        labels: {
          boxWidth: 20,   // Size of the color box
          padding: 15,    // Space between labels
        }
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
</script>
</body>
</html>
