<?php
session_start(); // Start the session at the very beginning of the file

// Include database connection
include('db.php'); // Ensure this path is correct

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the user's name from the UserAccount table
$query_user = "SELECT Username FROM UserAccount WHERE UserID = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_row = $result_user->fetch_assoc();
$username = $user_row['Username']; // Store the username in a variable

// Fetch the customer's booking history
$query = "SELECT 
            a.AppointmentID, 
            a.DateTime, 
            a.Status, 
            s.Name AS StylistName, 
            c.Name AS CustomerName
          FROM Appointment a
          JOIN Stylist s ON a.StylistID = s.StylistID
          JOIN Customer c ON a.CustomerID = c.CustomerID
          WHERE c.UserID = ?
          ORDER BY a.DateTime DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="sidebar.css"> <!-- Ensure correct path to your CSS -->
</head>
<body>
<div class="preference-page-container">
    <!-- Top Bar with Settings Dropdown -->
    <div class="top-bar">
        <div class="settings-menu">
            <span class="settings-icon">&#9881;</span> <!-- Settings emoji -->
            <div class="dropdown-content">
                <a href="logout.php" class="<?php echo ($currentPage == 'logout.php') ? 'active' : ''; ?>">Logout</a>
            </div>
        </div>
    </div>
    <?php 
    include('menu.php');
    ?>
    
    <div class="container">
        <div class="content">
        <h1>Your Booking History</h1>

        <!-- Personalized greeting -->

        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Appointment ID</th>
                        <th>Date & Time</th>
                        <th>Stylist/Barber</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['AppointmentID']; ?></td>
                            <td><?php echo date('F j, Y, g:i a', strtotime($row['DateTime'])); ?></td>
                            <td><?php echo $row['StylistName']; ?></td>
                            <td><?php echo $row['Status']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>You have no past appointments.</p>
        <?php endif; ?>
    </div>
    </div>
</div>
</body>
</html>
