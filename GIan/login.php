<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $result = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND password='$password'");
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid login!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="login.css">
    <title>Iska-Care</title>
</head>

<body>

    <div class="container" id="container">
        <div class="form-container sign-up">
            <form>
                <h1>Create Account</h1>
            <p>Register with your PUPSIS account to use all of site features</p>
                <div class="social-icons">
                </div>
                <input type="user" placeholder="Username">
                <input type="password" placeholder="Password">
                <button>Sign Up</button>
            </form>
        </div>
        <div class="form-container sign-in">
            <form method="post">
                <h1>Sign In</h1>
            <p>Enter your PUPSIS account to use all of site features</p>
                <div class="social-icons">
                </div>
                 <input type="text" name="username" placeholder="Username" required><br>
                 <input type="password" name="password" placeholder="Password" required><br>
                 <button type="submit">Sign in</button>
            </form>
        </div>
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <img src="pup.png" alt="Doctor Profile" />
                    <h1>Welcome back to Iska-Care!</h1>
                    <p>"Modern Care for the Modern Iskolar ng Bayan"</p>
                    <button class="hidden" id="login">Sign In</button>
                    <p>Iska-Care Beta v0.1</p>
                </div>
                <div class="toggle-panel toggle-right">
                    <img src="pup.png" alt="Doctor Profile" />
                    <h1>Welcome to</h1>
                    <h1>Iska-care!</h1>
                    <p>"Modern Care for the Modern Iskolar ng Bayan"</p>
                    <button class="hidden" id="register">Sign Up</button>
                    <p>Iska-Care Beta v0.1</p>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>