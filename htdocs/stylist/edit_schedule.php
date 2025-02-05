<?php
session_start();
include('db_connection.php');

// Check if stylist is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

$userID = $_SESSION['user_id'];

if (isset($_GET['id'])) {
    $scheduleID = $_GET['id'];

    // Fetch the current schedule data for editing
    $query = "SELECT * FROM StylistSchedule WHERE ScheduleID = ? AND StylistID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $scheduleID, $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "Schedule not found.";
        exit();
    }

    $schedule = $result->fetch_assoc();

    // Handle the update form submission
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $dayOfWeek = $_POST['dayOfWeek'];
        $startTime = $_POST['startTime'];
        $endTime = $_POST['endTime'];
        $blockTime = isset($_POST['blockTime']) ? 1 : 0; // Block time if checked

        // Update the schedule in the database
        $updateQuery = "UPDATE StylistSchedule SET DayOfWeek = ?, StartTime = ?, EndTime = ?, Blocked = ? WHERE ScheduleID = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param('ssssi', $dayOfWeek, $startTime, $endTime, $blockTime, $scheduleID);
        $updateStmt->execute();

        // Redirect after updating
        header('Location: manage_schedule.php');
        exit();
    }
} else {
    echo "Invalid schedule ID.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Schedule</title>
    <link rel="stylesheet" href="style.css">
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
    <h1>Edit Schedule</h1>

    <form action="edit_schedule.php?id=<?php echo $schedule['ScheduleID']; ?>" method="POST">
        <label for="dayOfWeek">Day of the Week:</label>
        <select name="dayOfWeek" id="dayOfWeek" required>
            <option value="Monday" <?php echo ($schedule['DayOfWeek'] == 'Monday') ? 'selected' : ''; ?>>Monday</option>
            <option value="Tuesday" <?php echo ($schedule['DayOfWeek'] == 'Tuesday') ? 'selected' : ''; ?>>Tuesday</option>
            <option value="Wednesday" <?php echo ($schedule['DayOfWeek'] == 'Wednesday') ? 'selected' : ''; ?>>Wednesday</option>
            <option value="Thursday" <?php echo ($schedule['DayOfWeek'] == 'Thursday') ? 'selected' : ''; ?>>Thursday</option>
            <option value="Friday" <?php echo ($schedule['DayOfWeek'] == 'Friday') ? 'selected' : ''; ?>>Friday</option>
            <option value="Saturday" <?php echo ($schedule['DayOfWeek'] == 'Saturday') ? 'selected' : ''; ?>>Saturday</option>
            <option value="Sunday" <?php echo ($schedule['DayOfWeek'] == 'Sunday') ? 'selected' : ''; ?>>Sunday</option>
        </select>
        <br><br>

        <label for="startTime">Start Time:</label>
        <input type="time" name="startTime" value="<?php echo $schedule['StartTime']; ?>" required>
        <br><br>

        <label for="endTime">End Time:</label>
        <input type="time" name="endTime" value="<?php echo $schedule['EndTime']; ?>" required>
        <br><br>

        <label for="blockTime">Block this time (unavailable):</label>
        <input type="checkbox" name="blockTime" <?php echo ($schedule['Blocked'] == 1) ? 'checked' : ''; ?>>
        <br><br>

        <input type="submit" value="Update Schedule">
    </form>
</div>
</body>
</html>
