<?php
// reporting.php
session_start();
include('db_connection.php'); // Include your database connection file

// Fetch appointment statistics from the database
$query = "SELECT 
                COUNT(*) AS total_appointments,
                SUM(CASE WHEN Status = 'Completed' THEN 1 ELSE 0 END) AS completed_appointments,
                SUM(CASE WHEN Status = 'Cancelled' THEN 1 ELSE 0 END) AS cancelled_appointments
          FROM Appointment";  // Changed 'appointments' to 'Appointment' to match your database
$result = mysqli_query($conn, $query);
$stats = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Reporting</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="sidebar.css">
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
    <h2>Appointment Statistics</h2>
    
    <div class="statistics">
        <div class="stat-item">
            <h3>Total Appointments</h3>
            <p><?php echo $stats['total_appointments']; ?></p>
        </div>
        <div class="stat-item">
            <h3>Completed Appointments</h3>
            <p><?php echo $stats['completed_appointments']; ?></p>
        </div>
        <div class="stat-item">
            <h3>Cancelled Appointments</h3>
            <p><?php echo $stats['cancelled_appointments']; ?></p>
        </div>
    </div>

    <div class="report-options">
        <form action="generate_report.php" method="POST">
            <label for="report-type">Generate Report:</label>
            <select name="report_type" id="report-type">
                <option value="monthly">Monthly Report</option>
                <option value="yearly">Yearly Report</option>
            </select>
            <button type="submit">Generate</button>
        </form>
    </div>
</div>
</div>
</body>
</html>

<?php
// Close the database connection
mysqli_close($conn);
?>
