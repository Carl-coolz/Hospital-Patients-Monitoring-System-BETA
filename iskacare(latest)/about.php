<?php
session_start();
include 'db.php';

// Logout action
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    session_destroy();
    header("Location: index.php");
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Iska-Care</title>
    <link rel="stylesheet" href="dash.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet"/>
    <style>
        .about-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .about-hero {
            text-align: center;
            margin-bottom: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 60px 40px;
            border-radius: 20px;
            box-shadow: var(--shadow-lg);
        }
        
        .about-hero h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #fff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .creative-team-name {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        
        .team-tagline {
            font-size: 1.3rem;
            opacity: 0.9;
            margin-bottom: 30px;
        }
        
        .mission-statement {
            font-size: 1.1rem;
            line-height: 1.8;
            max-width: 600px;
            margin: 0 auto;
            opacity: 0.95;
        }
        
        .team-section {
            margin-bottom: 60px;
        }
        
        .section-title {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 50px;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            border-radius: 2px;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .member-card {
            background: var(--bg-card);
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .member-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }
        
        .member-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin: 0 auto 25px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            font-weight: 700;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            box-shadow: 0 8px 20px rgba(196, 2, 2, 0.3);
        }
        
        .member-name {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 8px;
        }
        
        .member-role {
            font-size: 1.1rem;
            color: var(--primary-color);
            font-weight: 500;
            margin-bottom: 15px;
        }
        
        .member-skill {
            display: inline-block;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        .member-bio {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-primary);
            opacity: 0.8;
        }
        
        .stats-section {
            background: var(--bg-card);
            border-radius: 20px;
            padding: 50px;
            box-shadow: var(--shadow-md);
            margin-bottom: 50px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 40px;
            text-align: center;
        }
        
        .stat-item {
            padding: 20px;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color);
            display: block;
            margin-bottom: 10px;
        }
        
        .stat-label {
            font-size: 1.1rem;
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .values-section {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-radius: 20px;
            padding: 50px;
            margin-bottom: 50px;
        }
        
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }
        
        .value-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
        }
        
        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        
        .value-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 1.5rem;
            color: white;
        }
        
        .value-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 15px;
        }
        
        .value-description {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--text-primary);
            opacity: 0.8;
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--primary-color);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-sm);
        }
        
        .back-button:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .quick-access-section {
            background: var(--bg-card);
            border-radius: 20px;
            padding: 50px;
            box-shadow: var(--shadow-md);
            margin-bottom: 50px;
        }
        
        .access-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-top: 40px;
        }
        
        .access-card {
            background: linear-gradient(135deg, #f8fafc, #e2e8f0);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            text-decoration: none;
            color: var(--text-primary);
            transition: all 0.3s ease;
            border: 2px solid transparent;
            box-shadow: var(--shadow-sm);
        }
        
        .access-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
            background: linear-gradient(135deg, #fff, #f1f5f9);
        }
        
        .access-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2rem;
            color: white;
            box-shadow: 0 8px 20px rgba(196, 2, 2, 0.3);
        }
        
        .access-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 12px;
        }
        
        .access-description {
            font-size: 1rem;
            line-height: 1.5;
            color: var(--text-primary);
            opacity: 0.8;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .about-hero h1 {
                font-size: 2.5rem;
            }
            
            .creative-team-name {
                font-size: 2rem;
            }
            
            .team-grid {
                grid-template-columns: 1fr;
            }
            
            .about-container {
                padding: 20px 10px;
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
            <a href="about.php" class="sidebar-icon active" data-tooltip="About Us">
                <i class="bx bx-info-circle"></i>
            </a>
            <a href="?logout=true" class="sidebar-icon" data-tooltip="Logout">
            <i class="bx bx-log-out"></i>
            </a>
        </div>

        <!-- MAIN CONTENT -->
        <div class="main-content">
            <div class="about-container">
                <!-- Hero Section -->
                <div class="about-hero">
                    <h1>About Us</h1>
                    <div class="creative-team-name">Creatve Code_</div>
                    <div class="team-tagline">Project Group</div>
                    <p class="mission-statement">
                       We are the Creatve Code_ Project Group aims to create a simple 
                       and easy to use healthcare system for the modern Iskolar ng Bayan
                       and to show that even the person who struggle at coding can create
                       functional and useful system!.
                    </p>
                </div>

                <!-- Team Section -->
                <div class="team-section">
                    <h2 class="section-title">Meet Our Team</h2>
                    <div class="team-grid">
                        <div class="member-card">
                            <div class="member-avatar">J</div>
                            <h3 class="member-name">Jon Carlo</h3>
                            <p class="member-role">Lead Developer / UI/UX Designer</p>
                            <span class="member-skill">"The Overthinker"</span>
                            <p class="member-bio">
                                Jon Carlo A. Marasigan is a 19 year-old student of PUP-LQ
                                studying Bachelor of Science in Information Technology and the lead developer of the project.
                            </p>
                        </div>

                        <div class="member-card">
                            <div class="member-avatar">G</div>
                            <h3 class="member-name">Gian</h3>
                            <p class="member-role">Backend Developer</p>
                            <span class="member-skill">"The Ayos Natoh!"</span>
                            <p class="member-bio">
                             Gian Errol V. Labso is a 19 year-old student of PUP-LQ
                             studying Bachelor of Science in Information Technology and the backend developer of the project.
                            </p>
                        </div>

                        <div class="member-card">
                            <div class="member-avatar">P</div>
                            <h3 class="member-name">Paulo Xezar</h3>
                            <p class="member-role">Backend Developer/Moral Support</p>
                            <span class="member-skill">"The Di ko alam pano to gawin?"</span>
                            <p class="member-bio">
                                Paulo Xezar F. Basanta is a 20 year-old student of PUP-LQ
                                studying Bachelor of Science in Information Technology and the backend developer and a big moral support of the project.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Access Section -->
                <div class="quick-access-section">
                    <h2 class="section-title">Quick Access</h2>
                    <div class="access-grid">
                        <a href="dashboard.php" class="access-card">
                            <div class="access-icon">
                                <i class="bx bx-pulse"></i>
                            </div>
                            <h3 class="access-title">Dashboard</h3>
                            <p class="access-description">View system overview and statistics</p>
                        </a>
                        
                        <a href="add_patient.php" class="access-card">
                            <div class="access-icon">
                                <i class="bx bx-user-plus"></i>
                            </div>
                            <h3 class="access-title">Add Patient</h3>
                            <p class="access-description">Register new patient records</p>
                        </a>
                        
                        <a href="queue.php" class="access-card">
                            <div class="access-icon">
                                <i class="bx bx-list-ol"></i>
                            </div>
                            <h3 class="access-title">Patient Queue</h3>
                            <p class="access-description">Manage patient waiting list</p>
                        </a>
                        
                        <a href="view_patients.php" class="access-card">
                            <div class="access-icon">
                                <i class="bx bx-folder"></i>
                            </div>
                            <h3 class="access-title">View Records</h3>
                            <p class="access-description">Browse patient database</p>
                        </a>
                    </div>
                </div>
    </script>
</body>
</html>
