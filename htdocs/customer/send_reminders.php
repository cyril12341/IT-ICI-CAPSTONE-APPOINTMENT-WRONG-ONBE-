<?php
session_start();
require_once 'db_connection.php'; // Database connection

// Function to send email reminder (example, customize as needed)
function sendEmailReminder($to, $subject, $message) {
    // Using PHP's mail function (or you could use a mail library like PHPMailer)
    mail($to, $subject, $message, "From: no-reply@yourdomain.com");
}

// Get current date and time
$currentDateTime = date('Y-m-d H:i:s');

// Query to find appointments that are scheduled within the next 24 hours
$sql = "SELECT a.AppointmentID, a.CustomerID, a.DateTime, c.Name AS CustomerName, c.ContactInfo AS CustomerContact, s.Name AS StylistName
        FROM Appointment a
        JOIN Customer c ON a.CustomerID = c.CustomerID
        JOIN Stylist s ON a.StylistID = s.StylistID
        WHERE a.Status = 'Scheduled' AND a.ReminderSent = 0 AND a.DateTime > NOW() AND a.DateTime <= DATE_ADD(NOW(), INTERVAL 24 HOUR)";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Loop through each appointment to send reminder
    while ($row = $result->fetch_assoc()) {
        $appointmentID = $row['AppointmentID'];
        $customerName = $row['CustomerName'];
        $customerContact = $row['CustomerContact'];
        $stylistName = $row['StylistName'];
        $appointmentDateTime = $row['DateTime'];

        // Email content
        $subject = "Reminder: Upcoming Appointment with $stylistName";
        $message = "Dear $customerName,\n\nThis is a reminder about your upcoming appointment with $stylistName on $appointmentDateTime.\n\nPlease let us know if you need to reschedule or cancel.\n\nThank you!";
        
        // Send email reminder
        sendEmailReminder($customerContact, $subject, $message);

        // Update the reminder sent status to avoid resending
        $updateSql = "UPDATE Appointment SET ReminderSent = 1 WHERE AppointmentID = $appointmentID";
        $conn->query($updateSql);

        // Log the reminder notification
        $notificationSql = "INSERT INTO AppointmentNotifications (AppointmentID, NotificationType, SentTime) VALUES ($appointmentID, 'Reminder', NOW())";
        $conn->query($notificationSql);
    }
} else {
    echo "No appointments to send reminders for.";
}

// Close the database connection
$conn->close();
?>
