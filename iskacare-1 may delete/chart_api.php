<?php
session_start();
include 'db.php';

// Set content type to JSON
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit();
}

$chart_type = isset($_GET['type']) ? $_GET['type'] : 'weekly';

switch($chart_type) {
    case 'weekly':
        getWeeklyData();
        break;
    case 'queue':
        getQueueData();
        break;
    case 'conditions':
        getConditionsData();
        break;
    default:
        echo json_encode(['error' => 'Invalid chart type']);
        break;
}

function getWeeklyData() {
    global $conn;
    
    $weekly_data = [];
    $labels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    // Get data for each day of the week (Monday = 1, Sunday = 7)
    for ($day_of_week = 1; $day_of_week <= 7; $day_of_week++) {
        $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM patients WHERE DAYOFWEEK(date_admitted) = $day_of_week"))['count'];
        $weekly_data[] = $count;
    }
    
    echo json_encode([
        'labels' => $labels,
        'data' => $weekly_data,
        'timestamp' => time()
    ]);
}

function getMonthlyData() {
    global $conn;
    
    $monthly_data = [];
    $labels = [];
    
    // Get last 30 days data with actual dates
    for ($i = 29; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM patients WHERE DATE(date_admitted) = '$date'"))['count'];
        $monthly_data[] = $count;
        $labels[] = date('M j', strtotime($date)); // e.g., "Jan 15", "Jan 16"
    }
    
    echo json_encode([
        'labels' => $labels,
        'data' => $monthly_data,
        'timestamp' => time()
    ]);
}

function getHourlyData() {
    global $conn;
    
    $hourly_data = [];
    $labels = [];
    
    // Get hourly data for today
    for ($i = 0; $i < 24; $i++) {
        $hour = sprintf('%02d', $i);
        $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM patients WHERE DATE(date_admitted) = CURDATE() AND HOUR(FROM_UNIXTIME(time_in)) = $i"))['count'];
        $hourly_data[] = $count;
        $labels[] = $hour . ':00'; // e.g., "00:00", "01:00", "02:00"
    }
    
    echo json_encode([
        'labels' => $labels,
        'data' => $hourly_data,
        'timestamp' => time()
    ]);
}

function getQueueData() {
    global $conn;
    
    $morning_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM patients WHERE time_out = 0 AND HOUR(FROM_UNIXTIME(time_in)) BETWEEN 6 AND 12"))['count'];
    $afternoon_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM patients WHERE time_out = 0 AND HOUR(FROM_UNIXTIME(time_in)) BETWEEN 12 AND 18"))['count'];
    $evening_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM patients WHERE time_out = 0 AND HOUR(FROM_UNIXTIME(time_in)) BETWEEN 18 AND 23"))['count'];
    
    echo json_encode([
        'labels' => ["Morning", "Afternoon", "Evening"],
        'data' => [$morning_count, $afternoon_count, $evening_count],
        'timestamp' => time()
    ]);
}

function getConditionsData() {
    global $conn;
    
    $condition_data = mysqli_query($conn, "SELECT condition_text, COUNT(*) as count FROM patients GROUP BY condition_text ORDER BY count DESC LIMIT 5");
    
    $condition_labels = [];
    $condition_counts = [];
    
    if (mysqli_num_rows($condition_data) > 0) {
        while($row = mysqli_fetch_assoc($condition_data)) {
            $condition_labels[] = $row['condition_text'];
            $condition_counts[] = $row['count'];
        }
    } else {
        $condition_labels[] = "No Data";
        $condition_counts[] = 1;
    }
    
    echo json_encode([
        'labels' => $condition_labels,
        'data' => $condition_counts,
        'timestamp' => time()
    ]);
}
?>
