<?php
session_start();

// Include database connection
include('db.php'); // Make sure this path is correct for your db.php file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: customer_login.php'); // Redirect to login page if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's name from the UserAccount table using the user_id
$query_user = "SELECT Username FROM UserAccount WHERE UserID = ?";
$stmt_user = $conn->prepare($query_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_row = $result_user->fetch_assoc();
$username = $user_row['Username']; // Store the username in a variable

// Fetch customer's stylist preference if available
$query = "SELECT StylistID FROM CustomerPreferences WHERE CustomerID = (SELECT CustomerID FROM Customer WHERE UserID = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$preference = $result->fetch_assoc();
$preferred_stylist_id = $preference ? $preference['StylistID'] : null;

// Fetch available stylists for selection
$query_stylists = "SELECT * FROM Stylist";
$stylist_result = $conn->query($query_stylists);

// Handle appointment booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stylist_id = $_POST['stylist_id'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];

    // Combine the date and time to form a DateTime string
    $appointment_datetime = $appointment_date . ' ' . $appointment_time;

    // Check if the selected stylist is available at the chosen time
    $check_availability = "SELECT * FROM Appointment WHERE StylistID = ? AND DateTime = ? AND Status = 'Scheduled'";
    $check_stmt = $conn->prepare($check_availability);
    $check_stmt->bind_param("is", $stylist_id, $appointment_datetime);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // If an appointment exists, the stylist is already booked at this time
        $error_message = "Sorry, the stylist is already booked at this time. Please choose another time.";
    } else {
        // Insert the appointment if the time is available
        $insert_query = "INSERT INTO Appointment (CustomerID, StylistID, DateTime, Status) 
                         SELECT CustomerID, ?, ?, 'Scheduled' FROM Customer WHERE UserID = ?";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("issi", $stylist_id, $appointment_datetime, $user_id);
        $insert_stmt->execute();

        header("Location: appointment_success.php");
        exit();
    }
}

// Fetch stylist schedule for displaying available times
$stylist_schedule_query = "SELECT * FROM StylistSchedule WHERE StylistID = ?";
$stylist_schedule_stmt = $conn->prepare($stylist_schedule_query);
$stylist_schedule_stmt->bind_param("i", $preferred_stylist_id);
$stylist_schedule_stmt->execute();
$stylist_schedule_result = $stylist_schedule_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="sidebar.css"> <!-- Ensure correct path to your CSS -->
</head>
<body>
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
            <h1>Book an Appointment</h1>

            <!-- Personalized greeting -->
            <p>Hello, <?php echo htmlspecialchars($username); ?>!</p> <!-- Display user's name -->

            <?php if (isset($error_message)): ?>
                <p style="color:red;"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form method="POST" action="book_appointment.php">
                <label for="stylist_id">Select Stylist/Barber:</label>
                <select name="stylist_id" id="stylist_id" required>
                    <option value="">-- Choose Stylist/Barber --</option>
                    <?php while ($stylist = $stylist_result->fetch_assoc()): ?>
                        <option value="<?php echo $stylist['StylistID']; ?>"
                            <?php echo ($preferred_stylist_id && $preferred_stylist_id == $stylist['StylistID']) ? 'selected' : ''; ?>>
                            <?php echo $stylist['Name']; ?> (<?php echo $stylist['Specialization']; ?>)
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="appointment_date">Select Date:</label>
                <input type="date" id="appointment_date" name="appointment_date" required>

                <label for="appointment_time">Select Time:</label>
                <select name="appointment_time" id="appointment_time" required>
                    <option value="">-- Choose Time --</option>
                    <?php while ($schedule = $stylist_schedule_result->fetch_assoc()): ?>
                        <option value="<?php echo $schedule['StartTime']; ?>">
                            <?php echo $schedule['StartTime']; ?> - <?php echo $schedule['EndTime']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button type="submit">Book Appointment</button>
            </form>
        </div>
    </div>
</body>
</html>
