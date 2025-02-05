<?php
session_start();
include('db_connection.php');

// Check if stylist is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

$userID = $_SESSION['user_id'];

// Handle sending a notification (e.g., reminder or confirmation)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_notification'])) {
    $appointmentID = $_POST['appointmentID'];
    $notificationType = $_POST['notification_type'];
    $message = '';

    // Fetch appointment details
    $query = "SELECT a.AppointmentDateTime, c.CustomerName, c.Email
              FROM Appointment a
              JOIN Customer c ON a.CustomerID = c.CustomerID
              WHERE a.AppointmentID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $appointmentID);
    $stmt->execute();
    $appointmentDetails = $stmt->get_result()->fetch_assoc();

    // Prepare message based on notification type
    if ($notificationType == 'reminder') {
        $message = "Hi {$appointmentDetails['CustomerName']}, this is a reminder for your appointment on {$appointmentDetails['AppointmentDateTime']}.";
    } elseif ($notificationType == 'confirmation') {
        $message = "Hi {$appointmentDetails['CustomerName']}, your appointment has been successfully booked for {$appointmentDetails['AppointmentDateTime']}.";
    }

    // Insert notification into the AppointmentNotifications table
    $insertNotificationQuery = "INSERT INTO AppointmentNotifications (AppointmentID, NotificationType, Message, SentDateTime)
                                VALUES (?, ?, ?, NOW())";
    $insertStmt = $conn->prepare($insertNotificationQuery);
    $insertStmt->bind_param('iss', $appointmentID, $notificationType, $message);
    $insertStmt->execute();

    // Optionally send an email to the customer (using PHP mail function)
    if (filter_var($appointmentDetails['Email'], FILTER_VALIDATE_EMAIL)) {
        mail($appointmentDetails['Email'], "Appointment Notification", $message, "From: no-reply@salon.com");
    }

    // Redirect to avoid form resubmission
    header('Location: notifications.php');
    exit();
}

// Fetch appointment notifications
$query = "SELECT n.NotificationID, n.AppointmentID, n.NotificationType, n.Message, n.SentDateTime, a.AppointmentDateTime, c.CustomerName
          FROM AppointmentNotifications n
          JOIN Appointment a ON n.AppointmentID = a.AppointmentID
          JOIN Customer c ON a.CustomerID = c.CustomerID
          WHERE a.StylistID = ?
          ORDER BY n.SentDateTime DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $userID);
$stmt->execute();
$notificationsResult = $stmt->get_result();
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
    <h1>Appointment Notifications</h1>

    <h2>Send Notification</h2>
    <form action="notifications.php" method="POST">
        <label for="appointmentID">Appointment ID:</label>
        <input type="number" name="appointmentID" required><br>

        <label for="notification_type">Notification Type:</label>
        <select name="notification_type" required>
            <option value="reminder">Reminder</option>
            <option value="confirmation">Confirmation</option>
        </select><br>

        <input type="submit" name="send_notification" value="Send Notification">
    </form>

    <h2>Sent Notifications</h2>
    <table border="1">
        <tr>
            <th>Customer Name</th>
            <th>Appointment Date & Time</th>
            <th>Notification Type</th>
            <th>Message</th>
            <th>Sent Date & Time</th>
        </tr>
        <?php
        while ($notification = $notificationsResult->fetch_assoc()) {
            echo "<tr>
                <td>{$notification['CustomerName']}</td>
                <td>{$notification['AppointmentDateTime']}</td>
                <td>{$notification['NotificationType']}</td>
                <td>{$notification['Message']}</td>
                <td>{$notification['SentDateTime']}</td>
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
