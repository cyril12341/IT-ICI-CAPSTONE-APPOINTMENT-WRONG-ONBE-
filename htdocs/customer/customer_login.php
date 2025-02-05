<?php
// Include the database connection
include('db.php');

// Start the session
session_start();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to the book_appointment.php page if the user is already logged in
    header('Location: book_appointment.php');
    exit();
}

// Check if form is submitted
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    // Check if the username and password match a Customer in the UserAccount table
    $query = "SELECT * FROM UserAccount WHERE Role = 'Customer' AND Username = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['Password'])) {
            // Successful login
            $_SESSION['user_id'] = $row['UserID'];  // Store user ID in session
            $_SESSION['username'] = $row['Username']; // Store username in session

            // Get the CustomerID from the Customer table
            $customerQuery = "SELECT CustomerID FROM Customer WHERE UserID = " . $row['UserID'];
            $customerResult = $conn->query($customerQuery);
            if ($customerResult->num_rows > 0) {
                $customerRow = $customerResult->fetch_assoc();
                $_SESSION['customer_id'] = $customerRow['CustomerID']; // Store customer ID in session
            }

            // Redirect to book_appointment.php
            header('Location: book_appointment.php');
            exit();
        } else {
            echo "Invalid password!";
        }
    } else {
        echo "No customer found with this username.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login</title>
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
        <h1>Log in</h1>

        <!-- Customer Login Form -->
        <form method="POST" action="customer_login.php">
            Username: <input type="text" name="username" required><br>
            Password: <input type="password" name="password" required><br>
            <button type="submit" name="login">Login</button>
        </form>

        <!-- "Create an Account" Link -->
        <p class="signup-link">Create an account? <a href="customer_register.php" class="signup-link">Sign up</a></p>
    </div>
</body>
</html>
