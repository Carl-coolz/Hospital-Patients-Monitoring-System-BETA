<?php
session_start();
include 'db.php';

//login
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
//signup
 if (isset($_POST['action']) && $_POST['action'] == 'signup') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']); // no hashing

    // Check if username already exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($check) > 0) {
        $error = "Username already exists!";
    } else {
        $query = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
        if (mysqli_query($conn, $query)) {
            $success = "Account created! You can now sign in.";
        } else {
            $error = "Error creating account!";
        }
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
           <form method="post">
                <h1>Create Account</h1>
            <p>Register with your Credentials to use all of site features</p>
                <div class="social-icons">
                </div>
               <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="hidden" name="action" value="signup">
                <button type="submit">Sign Up</button>
            </form>
        </div>
        <div class="form-container sign-in">
            <form method="post">
                <h1>Sign In</h1>
            <p>Enter your Credentials to use all of site features</p>
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
                    <img src="pup.png" alt="PUP" />
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