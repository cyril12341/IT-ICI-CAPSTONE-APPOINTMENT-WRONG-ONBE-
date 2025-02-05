<?php
session_start();
include('db_connection.php');

// Start session to check if Stylist is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Get User ID from session
$userID = $_SESSION['user_id'];

// Fetch the stylist's current details from the Stylist table
$query = "SELECT * FROM Stylist WHERE UserID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userID);
$stmt->execute();
$result = $stmt->get_result();

// Check if the stylist exists in the database
if ($result->num_rows === 0) {
    echo "Stylist not found!";
    exit();
}

$stylist = $result->fetch_assoc();

// Handle the form submission if it's updating the profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $specialization = $_POST['specialization'];
    $contactInfo = $_POST['contactInfo'];

    // Update the Stylist's details in the database
    $updateQuery = "
        UPDATE Stylist 
        SET Name = ?, Specialization = ?, ContactInfo = ? 
        WHERE UserID = ?
    ";

    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("sssi", $name, $specialization, $contactInfo, $userID);
    if ($updateStmt->execute()) {
        echo "Profile updated successfully!";
        // Optionally, you can redirect to another page or refresh
    } else {
        echo "Error updating profile: " . $conn->error;
    }
    $updateStmt->close();
}

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

<h2>Update Your Profile</h2>

<form action="profile_management.php" method="POST">
    <label for="name">Name:</label><br>
    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($stylist['Name'] ?? ''); ?>" required><br><br>

    <label for="specialization">Specialization:</label><br>
    <input type="text" id="specialization" name="specialization" value="<?php echo htmlspecialchars($stylist['Specialization'] ?? ''); ?>" required><br><br>

    <label for="contactInfo">Contact Information:</label><br>
    <input type="text" id="contactInfo" name="contactInfo" value="<?php echo htmlspecialchars($stylist['ContactInfo'] ?? ''); ?>" required><br><br>

    <button type="submit">Update Profile</button>
</form>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
