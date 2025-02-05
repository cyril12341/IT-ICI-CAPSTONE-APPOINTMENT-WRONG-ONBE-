<?php
include('db_connection.php'); // Include your database connection

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    $name = $_POST['name'];
    $specialization = $_POST['specialization'];

    // Check if password and confirm password match
    if ($password != $confirmPassword) {
        $error = "Passwords do not match!";
    } else {
        // Hash the password before saving it
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new stylist into the UserAccount table
        $query = "INSERT INTO UserAccount (Username, Password, Role) VALUES (?, ?, 'Stylist')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $username, $hashedPassword);
        $stmt->execute();

        // Get the newly created UserID
        $userID = $stmt->insert_id;
        $stmt->close();

        // Insert stylist information into the Stylist table
        $query = "INSERT INTO Stylist (UserID, Name, Specialization) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iss', $userID, $name, $specialization);
        $stmt->execute();
        $stmt->close();

        $success = "Registration successful! You can now log in.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stylist Registration</title>
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

        /* Container for the Registration Form */
        .register-container {
            position: relative;
            background-color: rgba(0, 0, 0, 0.7); /* Dark transparent background */
            padding: 40px;
            border-radius: 8px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.4); /* Add shadow for depth */
            animation: fadeIn 1s ease-out;
        }

        /* Success and Error Messages */
        .message-box {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 16px;
            font-weight: bold;
            display: none; /* Initially hidden */
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .success-message {
            background-color: rgba(40, 167, 69, 0.9); /* Green */
            color: white;
            border-left: 5px solid #28a745;
        }

        .error-message {
            background-color: rgba(220, 53, 69, 0.9); /* Red */
            color: white;
            border-left: 5px solid #dc3545;
        }

        /* Animation to fade in message */
        .show-message {
            display: block;
            opacity: 1;
        }

        /* Form Title */
        h1 {
            font-size: 36px;
            margin-bottom: 20px;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 2px;
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

        /* "Already have an account?" Link */
        .login-link {
            margin-top: 15px;
            font-size: 14px;
            color: #fff;
        }

        .login-link a {
            color: #ff4d4d;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    
<div class="register-container">
    <h1>Stylist Registration</h1>
    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br><br>

        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required><br><br>

        <label for="specialization">Specialization:</label>
        <input type="text" id="specialization" name="specialization" required><br><br>

        <button type="submit" name="register">Register</button>
    </form>
    <div class="login-link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    <?php 
    if (isset($error)) echo "<p style='color:red;'>$error</p>"; 
    if (isset($success)) echo "<p style='color:green;'>$success</p>";
    ?>
</div>
</body>
</html>
