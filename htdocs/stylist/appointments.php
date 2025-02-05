<?php
session_start();
include('db_connection.php');

// Check if stylist is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userID = $_SESSION['user_id'];



// Handle reschedule, complete, or cancel appointment
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $appointmentID = $_POST['appointmentID'];
        $action = $_POST['action'];

        if ($action == 'complete') {
            $updateQuery = "UPDATE Appointment SET Status = 'Completed' WHERE AppointmentID = ?";
        } elseif ($action == 'cancel') {
            $updateQuery = "UPDATE Appointment SET Status = 'Cancelled' WHERE AppointmentID = ?";
        } elseif ($action == 'reschedule') {
            $newDateTime = $_POST['newDateTime'];
            $updateQuery = "UPDATE Appointment SET DateTime = ? WHERE AppointmentID = ?";
        }

        $stmt = $conn->prepare($updateQuery);
        if ($action == 'reschedule') {
            $stmt->bind_param('si', $newDateTime, $appointmentID);
        } else {
            $stmt->bind_param('i', $appointmentID);
        }
        $stmt->execute();

        // Redirect to avoid resubmission
        header('Location: appointments.php');
        exit();
    }
}


// Fetch upcoming appointments
$query = "SELECT a.AppointmentID, a.DateTime, a.Status, c.Name AS CustomerName
          FROM Appointment a
          JOIN Customer c ON a.CustomerID = c.CustomerID
          WHERE a.StylistID = ? AND a.DateTime >= NOW()
          ORDER BY a.DateTime";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userID);
$stmt->execute();
$appointmentsResult = $stmt->get_result();

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

    <h1>Manage Appointments</h1>

    <h2>Upcoming Appointments</h2>
    <table border="1">
        <tr>
            <th>Customer Name</th>
            <th>Appointment Time</th>
            <th>Status</th>
            <th>Stylist Preference</th>
            <th>Action</th>
        </tr>
        <?php
        while ($appointment = $appointmentsResult->fetch_assoc()) {
            echo "<tr>
                <td>{$appointment['CustomerName']}</td>
                <td>{$appointment['DateTime']}</td>
                <td>{$appointment['Status']}</td>
                <td>" . ($appointment['StylistPreference'] ? $appointment['StylistPreference'] : 'None') . "</td>
                <td>
                    <form action='appointments.php' method='POST' style='display:inline-block;'>
                        <input type='hidden' name='appointmentID' value='{$appointment['AppointmentID']}'>
                        <input type='submit' name='action' value='complete'>
                    </form>
                    <form action='appointments.php' method='POST' style='display:inline-block;'>
                        <input type='hidden' name='appointmentID' value='{$appointment['AppointmentID']}'>
                        <input type='submit' name='action' value='cancel'>
                    </form>
                    <form action='appointments.php' method='POST' style='display:inline-block;'>
                        <input type='hidden' name='appointmentID' value='{$appointment['AppointmentID']}'>
                        <label for='newDateTime'>New Date & Time:</label>
                        <input type='datetime-local' name='newDateTime' required>
                        <input type='submit' name='action' value='reschedule'>
                    </form>
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
