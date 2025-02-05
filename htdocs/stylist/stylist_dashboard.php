<?php
session_start();
include('db_connection.php');

// Check if stylist is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

$userID = $_SESSION['user_id'];

// Fetch stylist's appointments for today and this week
$query = "
    SELECT a.AppointmentID, a.DateTime, a.Status, a.CustomerID, c.Name AS CustomerName
    FROM Appointment a
    JOIN Customer c ON a.CustomerID = c.CustomerID
    WHERE a.StylistID = ? AND a.DateTime >= CURDATE()
    ORDER BY a.DateTime
";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userID);
$stmt->execute();
$result = $stmt->get_result();

// Fetch stylist's schedule for the week
$scheduleQuery = "
    SELECT DayOfWeek, StartTime, EndTime
    FROM StylistSchedule
    WHERE StylistID = ?
    ORDER BY FIELD(DayOfWeek, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
";
$scheduleStmt = $conn->prepare($scheduleQuery);
$scheduleStmt->bind_param('i', $userID);
$scheduleStmt->execute();
$scheduleResult = $scheduleStmt->get_result();

// Get summary of appointments for the day/week
$scheduledCount = 0;
$completedCount = 0;
$cancelledCount = 0;
$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
    switch ($row['Status']) {
        case 'Scheduled':
            $scheduledCount++;
            break;
        case 'Completed':
            $completedCount++;
            break;
        case 'Cancelled':
            $cancelledCount++;
            break;
    }
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
    <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>

    <h2>Appointment Summary</h2>
    <p><strong>Scheduled:</strong> <?php echo $scheduledCount; ?></p>
    <p><strong>Completed:</strong> <?php echo $completedCount; ?></p>
    <p><strong>Cancelled:</strong> <?php echo $cancelledCount; ?></p>

    <h2>Appointments for Today</h2>
    <table border="1">
        <tr>
            <th>Appointment ID</th>
            <th>Customer</th>
            <th>Time</th>
            <th>Status</th>
        </tr>
        <?php
        foreach ($appointments as $appointment) {
            echo "<tr>
                <td>{$appointment['AppointmentID']}</td>
                <td>{$appointment['CustomerName']}</td>
                <td>" . date('Y-m-d H:i', strtotime($appointment['DateTime'])) . "</td>
                <td>{$appointment['Status']}</td>
            </tr>";
        }
        ?>
    </table>

    <h2>Stylist Schedule</h2>
    <table border="1">
        <tr>
            <th>Day</th>
            <th>Start Time</th>
            <th>End Time</th>
        </tr>
        <?php
        while ($schedule = $scheduleResult->fetch_assoc()) {
            echo "<tr>
                <td>{$schedule['DayOfWeek']}</td>
                <td>{$schedule['StartTime']}</td>
                <td>{$schedule['EndTime']}</td>
            </tr>";
        }
        ?>
    </table>

    <h2>Upcoming Appointments</h2>
    <table border="1">
        <tr>
            <th>Appointment ID</th>
            <th>Customer</th>
            <th>Time</th>
        </tr>
        <?php
        // Fetch upcoming appointments
        $upcomingQuery = "
            SELECT a.AppointmentID, a.DateTime, c.Name AS CustomerName
            FROM Appointment a
            JOIN Customer c ON a.CustomerID = c.CustomerID
            WHERE a.StylistID = ? AND a.DateTime > NOW()
            ORDER BY a.DateTime
        ";
        $upcomingStmt = $conn->prepare($upcomingQuery);
        $upcomingStmt->bind_param('i', $userID);
        $upcomingStmt->execute();
        $upcomingResult = $upcomingStmt->get_result();

        while ($upcoming = $upcomingResult->fetch_assoc()) {
            echo "<tr>
                <td>{$upcoming['AppointmentID']}</td>
                <td>{$upcoming['CustomerName']}</td>
                <td>" . date('Y-m-d H:i', strtotime($upcoming['DateTime'])) . "</td>
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
