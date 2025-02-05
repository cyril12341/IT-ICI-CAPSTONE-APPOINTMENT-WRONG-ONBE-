<?php
// Include the database connection
include('db.php');

// Start the session
session_start();

// Ensure the user is logged in as a customer
if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

// Get the customer's details from the session
$customer_id = $_SESSION['customer_id'];

// Fetch the customer's name from the database
$query = "SELECT Name FROM Customer WHERE CustomerID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $customer_id);
$stmt->execute();
$result = $stmt->get_result();

// If customer exists, fetch the name
if ($result->num_rows > 0) {
    $customer_data = $result->fetch_assoc();
    $customer_name = $customer_data['Name'];
} else {
    $customer_name = 'Guest';  // Default to 'Guest' if not found
}

// Fetch all upcoming appointments for the customer
$query = "SELECT a.AppointmentID, a.DateTime, a.Status, st.Name AS StylistName 
          FROM Appointment a
          JOIN Stylist st ON a.StylistID = st.StylistID
          WHERE a.CustomerID = ? AND a.DateTime >= NOW()";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $customer_id);
$stmt->execute();
$appointments_result = $stmt->get_result();

// Handle cancel, reschedule requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['cancel'])) {
        $appointment_id = $_POST['appointment_id'];
        $update_query = "UPDATE Appointment SET Status = 'Cancelled' WHERE AppointmentID = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('i', $appointment_id);
        if ($stmt->execute()) {
            echo "Appointment canceled successfully!";
        } else {
            echo "Error canceling the appointment.";
        }
    }

    if (isset($_POST['reschedule'])) {
        $appointment_id = $_POST['appointment_id'];
        $new_datetime = $_POST['new_datetime'];
        $update_query = "UPDATE Appointment SET DateTime = ? WHERE AppointmentID = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('si', $new_datetime, $appointment_id);
        if ($stmt->execute()) {
            echo "Appointment rescheduled successfully!";
        } else {
            echo "Error rescheduling the appointment.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Appointments</title>
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
    <!-- Personalized greeting -->

<h2>Your Upcoming Appointments</h2>

<table border="1">
    <thead>
        <tr>
            <th>Stylist</th>
            <th>Date/Time</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($appointment = $appointments_result->fetch_assoc()) { ?>
            <tr>
                <td><?= htmlspecialchars($appointment['StylistName']) ?></td>
                <td><?= htmlspecialchars($appointment['DateTime']) ?></td>
                <td><?= htmlspecialchars($appointment['Status']) ?></td>
                <td>
                    <!-- Cancel Appointment -->
                    <form action="manage_customer_appointments.php" method="POST" style="display:inline;">
                        <input type="hidden" name="appointment_id" value="<?= htmlspecialchars($appointment['AppointmentID']) ?>">
                        <button type="submit" name="cancel">Cancel</button>
                    </form>

                    <!-- Reschedule Appointment -->
                    <form action="manage_customer_appointments.php" method="POST" style="display:inline;">
                        <input type="hidden" name="appointment_id" value="<?= htmlspecialchars($appointment['AppointmentID']) ?>">
                        <label for="new_datetime_<?= htmlspecialchars($appointment['AppointmentID']) ?>">New Date/Time:</label>
                        <input type="datetime-local" name="new_datetime" required id="new_datetime_<?= htmlspecialchars($appointment['AppointmentID']) ?>">
                        <button type="submit" name="reschedule">Reschedule</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </tbody>
</table>
</div>
</div>
</div>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
