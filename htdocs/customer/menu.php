<?php
// menu.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav>
<div class="sidebar-header">
        Customer
    </div>
    <ul>
        <li><a href="service_preferences.php" class="<?php echo ($currentPage == 'service_preferences.php') ? 'active' : ''; ?>">Preference</a></li>
        <li><a href="book_appointment.php" class="<?php echo ($currentPage == 'book_appointment.php') ? 'active' : ''; ?>">Book Appointment</a></li>
        <li><a href="manage_customer_appointments.php" class="<?php echo ($currentPage == 'manage_customer_appointments.php') ? 'active' : ''; ?>">View Appointments</a></li>
        <li><a href="booking_history.php" class="<?php echo ($currentPage == 'booking_history.php') ? 'active' : ''; ?>">History</a></li>
    </ul>
</nav>
