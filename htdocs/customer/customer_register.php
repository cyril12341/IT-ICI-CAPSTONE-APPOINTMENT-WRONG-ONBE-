<?php
// Include the database connection
include('db.php');

// Initialize message variable
$message = "";
$messageClass = "";

// Check if form is submitted
if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $contactInfo = mysqli_real_escape_string($conn, $_POST['contact_info']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password before saving

    // Insert into UserAccount table with 'Customer' role first
    $role = 'Customer';
    $userQuery = "INSERT INTO UserAccount (Role, Username, Password) VALUES ('$role', '$username', '$password')";

    if ($conn->query($userQuery) === TRUE) {
        $userID = $conn->insert_id; // Get the last inserted UserID

        // Now insert into Customer table
        $query = "INSERT INTO Customer (UserID, Name, ContactInfo) VALUES ('$userID', '$name', '$contactInfo')";
        if ($conn->query($query) === TRUE) {
            $message = "✅  registered successfully!";
            $messageClass = "success-message";
        } else {
            $message = "❌ Error registering : " . $conn->error;
            $messageClass = "error-message";
        }
    } else {
        $message = "❌ Error registering user in UserAccount: " . $conn->error;
        $messageClass = "error-message";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration</title>
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
        <h1>Registration</h1>

        <!-- Display message if set -->
        <?php if (!empty($message)): ?>
            <div class="message-box <?php echo $messageClass; ?> show-message">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Customer Registration Form -->
        <form method="POST" action="">
            Name: <input type="text" name="name" required><br>
            Contact Info: <input type="text" name="contact_info" required><br>
            Username: <input type="text" name="username" required><br>
            Password: <input type="password" name="password" required><br>
            <button type="submit" name="register">Register</button>
        </form>

        <!-- Already have an account link -->
        <div class="login-link">
            Already have an account? <a href="customer_login.php">Login here</a>
        </div>
    </div>
</body>
</html>
