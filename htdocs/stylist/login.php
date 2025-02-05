<?php
session_start();
include('db_connection.php'); // Include your database connection

// Check if form is submitted
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and execute the query to check if the username exists
    $query = "SELECT * FROM UserAccount WHERE Username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists and password matches
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password using password_hash verification (you need to hash passwords during registration)
        if (password_verify($password, $user['Password'])) {
            // Check if the user is a stylist
            if ($user['Role'] == 'Stylist') {
                $_SESSION['user_id'] = $user['UserID']; // Store the user ID in the session
                $_SESSION['username'] = $user['Username']; // Store the username in the session
                header('Location: stylist_dashboard.php'); // Redirect to stylist dashboard
                exit();
            } else {
                $error = "Only stylists can access this page.";
            }
        } else {
            $error = "Invalid credentials.";
        }
    } else {
        $error = "No user found with that username.";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stylist Login</title>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.5)), url('image/yawa.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
            position: relative;
        }

        /* Add overlay effect to darken the background */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* Dark overlay */
        }

        /* Container for the Login Form */
        .login-container {
            position: relative;
            background-color: rgba(0, 0, 0, 0.7); /* Dark transparent background */
            padding: 40px;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4); /* Add shadow for depth */
            animation: fadeIn 1s ease-out;
        }

        /* Fade-in animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Form Title */
        h1 {
            font-size: 36px;
            margin-bottom: 20px;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 2px;
            animation: slideIn 1s ease-out;
        }

        /* Slide-in animation for the title */
        @keyframes slideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Input Fields */
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            background-color: #fff;
            color: #333;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #ff4d4d; /* Highlight border on focus */
        }

        /* Button */
        button[type="submit"] {
            padding: 12px;
            width: 100%;
            background-color: #ff4d4d;
            color: #fff;
            font-size: 18px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #e63946;
        }

        /* "Create an Account" text */
        .signup-link {
            margin-top: 20px;
            color: #fff;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
            transition: color 0.3s ease;
        }

        .signup-link:hover {
            color: #ff4d4d;
        }

        /* Error Message */
        .error-message {
            color: #ff4d4d;
            font-size: 14px;
            margin-top: 10px;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            body {
                padding: 20px;
            }

            .login-container {
                padding: 20px;
                width: 100%;
                max-width: 300px;
            }

            h1 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
    <h1>Stylist Login</h1>
    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit" name="login">Login</button>
        
    </form>
    <p class="signup-link">Create an account? <a href="register.php" class="signup-link">Sign up</a></p>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
</body>
</html>
