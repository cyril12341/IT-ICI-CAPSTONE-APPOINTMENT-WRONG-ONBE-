<?php
session_start();
include('db_connection.php');

// Start session to check if Stylist is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Get User ID from session and fetch StylistID from the Stylist table
$userID = $_SESSION['user_id'];
$query = "SELECT StylistID FROM Stylist WHERE UserID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userID);
$stmt->execute();
$result = $stmt->get_result();

// Check if stylist exists and get StylistID
if ($result->num_rows === 0) {
    echo "Stylist not found!";
    exit();
}

$stylist = $result->fetch_assoc();
$stylistID = $stylist['StylistID'];

// Initialize query variables
$whereClauses = [];
$params = [];

// Date filter
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $whereClauses[] = "a.DateTime >= ?";
    $params[] = $_GET['start_date'] . " 00:00:00";
}

if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $whereClauses[] = "a.DateTime <= ?";
    $params[] = $_GET['end_date'] . " 23:59:59";
}

// Status filter
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $whereClauses[] = "a.Status = ?";
    $params[] = $_GET['status'];
}

// Customer name filter
if (isset($_GET['customer_name']) && !empty($_GET['customer_name'])) {
    $whereClauses[] = "c.Name LIKE ?";
    $params[] = "%" . $_GET['customer_name'] . "%";
}

// Filter by Stylist
// Using the logged-in StylistID for filtering if no Stylist ID is provided in the query params
if (isset($_GET['stylist_id']) && !empty($_GET['stylist_id'])) {
    $whereClauses[] = "a.StylistID = ?";
    $params[] = $_GET['stylist_id'];
} else {
    // If no Stylist filter is provided, use the logged-in StylistID
    $whereClauses[] = "a.StylistID = ?";
    $params[] = $stylistID;
}

// Build the WHERE clause
$whereQuery = "";
if (!empty($whereClauses)) {
    $whereQuery = "WHERE " . implode(" AND ", $whereClauses);
}

// Query to fetch appointments with the filters
$query = "
    SELECT a.AppointmentID, a.DateTime, a.Status, c.Name AS CustomerName, s.Name AS StylistName 
    FROM Appointment a
    JOIN Customer c ON a.CustomerID = c.CustomerID
    JOIN Stylist s ON a.StylistID = s.StylistID
    $whereQuery
    ORDER BY a.DateTime DESC
";
$stmt = $conn->prepare($query);

// Bind the parameters dynamically based on the filters
if (!empty($params)) {
    $types = str_repeat('s', count($params)); // Assuming all params are strings
    $stmt->bind_param($types, ...$params);
}

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
<div class="container">
<div class="content">
<h2>Appointment Search/Filter</h2>

<!-- Search Form -->
<form method="GET" action="appointment_search.php">
    <label for="start_date">Start Date: </label><input type="date" name="start_date" value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>">
    <label for="end_date">End Date: </label><input type="date" name="end_date" value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>">
    
    <label for="status">Status: </label>
    <select name="status">
        <option value="">All</option>
        <option value="Scheduled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Scheduled') ? 'selected' : ''; ?>>Scheduled</option>
        <option value="Completed" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Completed') ? 'selected' : ''; ?>>Completed</option>
        <option value="Cancelled" <?php echo (isset($_GET['status']) && $_GET['status'] == 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
    </select>

    <label for="customer_name">Customer Name: </label>
    <input type="text" name="customer_name" value="<?php echo isset($_GET['customer_name']) ? $_GET['customer_name'] : ''; ?>">

    <label for="stylist_id">Stylist: </label>
    <select name="stylist_id">
        <option value="">All</option>

        <!-- Fetch stylists for the dropdown -->
        <?php
        $stylistQuery = "SELECT StylistID, Name FROM Stylist";
        $stylistResult = $conn->query($stylistQuery);
        while ($stylist = $stylistResult->fetch_assoc()) {
            echo "<option value='" . $stylist['StylistID'] . "' " . (isset($_GET['stylist_id']) && $_GET['stylist_id'] == $stylist['StylistID'] ? 'selected' : '') . ">" . $stylist['Name'] . "</option>";
        }
        ?>
    </select>

    <button type="submit">Search</button>
</form>

<!-- Display results -->
<?php
if ($result->num_rows > 0) {
    echo "<table>";
    echo "<tr><th>Appointment ID</th><th>Customer Name</th><th>Stylist Name</th><th>Date/Time</th><th>Status</th></tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['AppointmentID'] . "</td>";
        echo "<td>" . $row['CustomerName'] . "</td>";
        echo "<td>" . $row['StylistName'] . "</td>";
        echo "<td>" . $row['DateTime'] . "</td>";
        echo "<td>" . $row['Status'] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "No appointments found for the given filters.";
}
?>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
