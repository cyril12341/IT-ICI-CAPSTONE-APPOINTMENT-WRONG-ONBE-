<?php
// menu.php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<nav>
    <div class="sidebar-header">
        Stylist 
    </div>
    <ul>
        <li><a href="stylist_dashboard.php" class="<?php echo ($currentPage == 'stylist_dashboard.php') ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="appointments.php" class="<?php echo ($currentPage == 'appointments.php') ? 'active' : ''; ?>">Appointment Management</a></li>
        <li><a href="manage_schedule.php" class="<?php echo ($currentPage == 'manage_schedule.php') ? 'active' : ''; ?>">Manage Schedule</a></li>
        <li><a href="customer_preferences.php" class="<?php echo ($currentPage == 'customer_preferences.php') ? 'active' : ''; ?>">Customer Preferences</a></li>
        <li><a href="customer_interaction.php" class="<?php echo ($currentPage == 'customer_interaction.php') ? 'active' : ''; ?>">Customer Interaction</a></li>
        <li><a href="appointment_search.php" class="<?php echo ($currentPage == 'appointment_search.php') ? 'active' : ''; ?>">Appointment Search</a></li>
        <li><a href="reporting.php" class="<?php echo ($currentPage == 'reporting.php') ? 'active' : ''; ?>">Reporting</a></li>

    </ul>
</nav>
