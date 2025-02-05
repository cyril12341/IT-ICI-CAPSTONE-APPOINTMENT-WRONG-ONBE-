<?php
session_start(); // Start the session at the very beginning of the file

// Include the database connection and menu files
include('db.php'); // Make sure db.php exists and contains your database connection
// Comment out if menu.php doesn't exist

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's name from UserAccount table
$query_user = "SELECT Username FROM UserAccount WHERE UserID = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_row = $result_user->fetch_assoc();
$username = $user_row['Username']; // Store the username in a variable

// Fetch customer's existing stylist preference
$query = "SELECT * FROM CustomerPreferences WHERE CustomerID = (SELECT CustomerID FROM Customer WHERE UserID = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$customer_preference = $result->fetch_assoc();

// Update preference if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stylist_id = $_POST['stylist_id'];

    // Check if the customer already has a stylist preference
    if ($customer_preference) {
        // Update the existing preference
        $update_query = "UPDATE CustomerPreferences SET StylistID = ? WHERE CustomerID = (SELECT CustomerID FROM Customer WHERE UserID = ?)";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ii", $stylist_id, $user_id);
        $update_stmt->execute();
    } else {
        // Insert a new stylist preference
        $insert_query = "INSERT INTO CustomerPreferences (CustomerID, StylistID) SELECT CustomerID, ? FROM Customer WHERE UserID = ?";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("ii", $stylist_id, $user_id);
        $insert_stmt->execute();
    }

    // Refresh customer preference after updating
    header("Location: service_preferences.php");
    exit();
}

// Fetch available stylists for preference selection
$query_stylists = "SELECT * FROM Stylist";
$stylist_result = $conn->query($query_stylists);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Preferences</title>

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
        <h1>Service Preferences</h1>

        <!-- Personalized greeting -->

        <form method="POST" action="service_preferences.php">
            <label for="stylist_id">Select Preferred Stylist:</label>
            <select name="stylist_id" id="stylist_id" required>
                <option value="">-- Choose Stylist --</option>
                <?php while ($stylist = $stylist_result->fetch_assoc()): ?>
                    <option value="<?php echo $stylist['StylistID']; ?>" 
                        <?php echo $customer_preference && $customer_preference['StylistID'] == $stylist['StylistID'] ? 'selected' : ''; ?>>
                        <?php echo $stylist['Name']; ?> (<?php echo $stylist['Specialization']; ?>)
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Save Preference</button>
        </form>

        <?php if ($customer_preference): ?>
            <p>Your current stylist preference is: <?php echo $customer_preference['StylistID']; ?></p>
        <?php else: ?>
            <p>You have not selected a stylist preference yet.</p>
        <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
