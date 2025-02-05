<?php
session_start();
include('db_connection.php');

// Start session to check if Stylist is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Get the User ID from session
$userID = $_SESSION['user_id'];

// Fetch the StylistID based on the logged-in user
$query = "SELECT StylistID FROM Stylist WHERE UserID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

// Check if the Stylist exists
if ($result->num_rows === 0) {
    echo "Stylist not found!";
    exit();
}

$stylist = $result->fetch_assoc();
$stylistID = $stylist['StylistID'];

// Fetch customer details based on the stylist's ID
$query = "
    SELECT c.CustomerID, c.Name AS CustomerName, c.ContactInfo, a.DateTime AS AppointmentTime
    FROM Customer c
    JOIN Appointment a ON c.CustomerID = a.CustomerID
    WHERE a.StylistID = ? AND a.Status = 'Scheduled'
";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $stylistID);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Search/Filter</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="sidebar.css">  <!-- Keep this for sidebar styles -->
</head>

<body>
<!-- Top Bar with Settings Dropdown -->
<div class="top-bar">
        <div class="settings-menu">
            <span class="settings-icon">&#9881;</span> <!-- Settings emoji -->
            <div class="dropdown-content">
                <a href="profile_management.php" class="<?php echo ($currentPage == 'profile_management.php') ? 'active' : ''; ?>">Profile Management</a>
                <a href="logout.php" class="<?php echo ($currentPage == 'logout.php') ? 'active' : ''; ?>">Logout</a>
            </div>
        </div>
    </div>
<?php include('menu.php'); ?>
<div class="main-content">
    <!-- Your page content goes here -->


<h2>Customer Interactions</h2>

<?php
// Display customer interactions or scheduled appointments
if ($result->num_rows > 0) {
    echo "<table border='1'>";
    echo "<tr><th>Customer Name</th><th>Contact Information</th><th>Scheduled Appointment</th><th>Actions</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['CustomerName']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ContactInfo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['AppointmentTime']) . "</td>";
        echo "<td><a href='send_message.php?customerID=" . $row['CustomerID'] . "'>Send Message</a></td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No scheduled appointments found for this stylist.";
}
?>
</div>
</body>
</html>

<?php
// Close connection
$stmt->close();
$conn->close();
?>
