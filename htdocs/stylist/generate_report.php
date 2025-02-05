<?php
include('db_connection.php');

// Check if Stylist is logged in
session_start();
if (!isset($_SESSION['stylist_id'])) {
    header("Location: login.php");
    exit();
}

// Get Stylist ID from session
$stylistID = $_SESSION['stylist_id'];

// Get date range from the form submission
$startDate = $_GET['start_date'];
$endDate = $_GET['end_date'];

// Query for Appointment Statistics within the specified date range
$reportQuery = "
    SELECT 
        COUNT(*) AS TotalAppointments,
        SUM(CASE WHEN Status = 'Cancelled' THEN 1 ELSE 0 END) AS CancelledAppointments,
        SUM(CASE WHEN Status = 'Completed' THEN 1 ELSE 0 END) AS CompletedAppointments
    FROM Appointment
    WHERE StylistID = ? AND DateTime BETWEEN ? AND ?
";

$stmt = $conn->prepare($reportQuery);
$stmt->bind_param("iss", $stylistID, $startDate, $endDate);
$stmt->execute();
$reportResult = $stmt->get_result();
$reportStats = $reportResult->fetch_assoc();

// Query for Busiest Time Slots within the specified date range
$busiestTimeReportQuery = "
    SELECT DATE_FORMAT(DateTime, '%H:%i') AS TimeSlot, COUNT(*) AS AppointmentCount
    FROM Appointment
    WHERE StylistID = ? AND DateTime BETWEEN ? AND ?
    GROUP BY TimeSlot
    ORDER BY AppointmentCount DESC
    LIMIT 5
";

$busiestTimeStmt = $conn->prepare($busiestTimeReportQuery);
$busiestTimeStmt->bind_param("iss", $stylistID, $startDate, $endDate);
$busiestTimeStmt->execute();
$busiestTimeResult = $busiestTimeStmt->get_result();

?>
<div class="main-content">
<h2>Performance Report</h2>

<!-- Display Report Statistics -->
<div>
    <h3>Total Appointments: <?php echo $reportStats['TotalAppointments']; ?></h3>
    <h3>Cancelled Appointments: <?php echo $reportStats['CancelledAppointments']; ?></h3>
    <h3>Completed Appointments: <?php echo $reportStats['CompletedAppointments']; ?></h3>
</div>

<hr>

<h2>Busiest Time Slots (Within Date Range)</h2>
<div>
    <table>
        <tr>
            <th>Time Slot</th>
            <th>Number of Appointments</th>
        </tr>
        <?php
        while ($row = $busiestTimeResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['TimeSlot'] . "</td>";
            echo "<td>" . $row['AppointmentCount'] . "</td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>
</div>
<?php
$stmt->close();
$busiestTimeStmt->close();
$conn->close();
?>
