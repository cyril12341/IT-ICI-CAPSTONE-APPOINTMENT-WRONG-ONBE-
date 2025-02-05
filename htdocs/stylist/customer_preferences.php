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

// Fetch customer preferences based on the stylist's ID
$query = "
    SELECT cp.PreferenceID, c.Name AS CustomerName, s.Name AS StylistName, cp.StylistID, cp.Note 
    FROM CustomerPreferences cp
    JOIN Customer c ON cp.CustomerID = c.CustomerID
    JOIN Stylist s ON cp.StylistID = s.StylistID
    WHERE cp.StylistID = ?
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
<div class="main-content">
<?php include('menu.php'); ?>

<h2>Customer Preferences</h2>

<?php
// Display customer preferences
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Customer Name</th><th>Preferred Stylist</th><th>Notes</th><th>Actions</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['CustomerName'] . "</td>";
        echo "<td>" . $row['StylistName'] . "</td>";
        echo "<td>" . $row['Note'] . "</td>";
        echo "<td><a href='edit_preference.php?id=" . $row['PreferenceID'] . "'>Edit</a></td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No preferences found for this stylist.";
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
