<?php
session_start();
include('db_connection.php');

// Check if stylist is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

$userID = $_SESSION['user_id'];

// Handle form submission to update schedule
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle availability update
    $dayOfWeek = $_POST['dayOfWeek'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $blockTime = isset($_POST['blockTime']) ? 1 : 0; // Whether the time is blocked

    // Insert or update the schedule
    $checkScheduleQuery = "SELECT * FROM StylistSchedule WHERE StylistID = ? AND DayOfWeek = ?";
    $stmt = $conn->prepare($checkScheduleQuery);
    $stmt->bind_param('is', $userID, $dayOfWeek);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing schedule
        $updateQuery = "UPDATE StylistSchedule SET StartTime = ?, EndTime = ?, Blocked = ? WHERE StylistID = ? AND DayOfWeek = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('ssiii', $startTime, $endTime, $blockTime, $userID, $dayOfWeek);
        $updateStmt->execute();
    } else {
        // Insert new schedule
        $insertQuery = "INSERT INTO StylistSchedule (StylistID, DayOfWeek, StartTime, EndTime, Blocked) VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param('isssi', $userID, $dayOfWeek, $startTime, $endTime, $blockTime);
        $insertStmt->execute();
    }

    // Redirect to avoid resubmission
    header('Location: manage_schedule.php');
    exit();
}

// Fetch current schedule for the stylist
$query = "SELECT * FROM StylistSchedule WHERE StylistID = ? ORDER BY FIELD(DayOfWeek, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userID);
$stmt->execute();
$scheduleResult = $stmt->get_result();
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
    <h1>Manage Your Schedule</h1>

    <h2>Set Availability</h2>
    <form action="manage_schedule.php" method="POST">
        <label for="dayOfWeek">Day of the Week:</label>
        <select name="dayOfWeek" id="dayOfWeek" required>
            <option value="Monday">Monday</option>
            <option value="Tuesday">Tuesday</option>
            <option value="Wednesday">Wednesday</option>
            <option value="Thursday">Thursday</option>
            <option value="Friday">Friday</option>
            <option value="Saturday">Saturday</option>
            <option value="Sunday">Sunday</option>
        </select>
        <br><br>

        <label for="startTime">Start Time:</label>
        <input type="time" name="startTime" required>
        <br><br>

        <label for="endTime">End Time:</label>
        <input type="time" name="endTime" required>
        <br><br>

        <label for="blockTime">Block this time (unavailable):</label>
        <input type="checkbox" name="blockTime">
        <br><br>

        <input type="submit" value="Save Availability">
    </form>

    <h2>Current Schedule</h2>
    <table border="1">
        <tr>
            <th>Day</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php
        while ($schedule = $scheduleResult->fetch_assoc()) {
            echo "<tr>
                <td>{$schedule['DayOfWeek']}</td>
                <td>{$schedule['StartTime']}</td>
                <td>{$schedule['EndTime']}</td>
                <td>" . ($schedule['Blocked'] ? 'Blocked' : 'Available') . "</td>
                <td>
                    <a href='edit_schedule.php?id={$schedule['ScheduleID']}'>Edit</a> | 
                    <a href='delete_schedule.php?id={$schedule['ScheduleID']}'>Delete</a>
                </td>
            </tr>";
        }
        ?>
    </table>
</div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
