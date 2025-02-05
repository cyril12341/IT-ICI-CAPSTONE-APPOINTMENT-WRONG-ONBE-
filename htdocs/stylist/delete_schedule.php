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

    // Delete the schedule from the database
    $deleteQuery = "DELETE FROM StylistSchedule WHERE ScheduleID = ? AND StylistID = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param('ii', $scheduleID, $userID);
    $deleteStmt->execute();

    // Redirect after deleting
    header('Location: manage_schedule.php');
    exit();
} else {
    echo "Invalid schedule ID.";
    exit();
}
?>
