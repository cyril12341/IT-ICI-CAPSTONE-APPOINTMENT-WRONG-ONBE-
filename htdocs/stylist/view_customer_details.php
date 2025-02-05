<?php
include('db_connection.php');

// Check if Stylist is logged in
session_start();
if (!isset($_SESSION['stylist_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Get Customer ID from URL
if (isset($_GET['id'])) {
    $customerID = $_GET['id'];

    // Fetch customer details and past appointment history
    $query = "
        SELECT c.Name, c.ContactInfo, a.DateTime, a.Status 
        FROM Customer c
        LEFT JOIN Appointment a ON c.CustomerID = a.CustomerID
        WHERE c.CustomerID = ?
        ORDER BY a.DateTime DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $customerID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $customer = $result->fetch_assoc();
        echo "<h2>Customer Details for " . $customer['Name'] . "</h2>";
        echo "<p><strong>Contact Info:</strong> " . $customer['ContactInfo'] . "</p>";

        echo "<h3>Past Appointment History</h3>";
        echo "<table>";
        echo "<tr><th>Date/Time</th><th>Status</th></tr>";

        do {
            echo "<tr>";
            echo "<td>" . $customer['DateTime'] . "</td>";
            echo "<td>" . $customer['Status'] . "</td>";
            echo "</tr>";
        } while ($customer = $result->fetch_assoc());

        echo "</table>";
    } else {
        echo "No past appointments found for this customer.";
    }

    // Add Notes section
    if (isset($_POST['add_note'])) {
        $note = $_POST['note'];

        // Insert a note for the customer in the CustomerNotes table (you need to create this table if it's not already available)
        $noteQuery = "INSERT INTO CustomerNotes (CustomerID, StylistID, Note) VALUES (?, ?, ?)";
        $noteStmt = $conn->prepare($noteQuery);
        $noteStmt->bind_param('iis', $customerID, $_SESSION['stylist_id'], $note);
        if ($noteStmt->execute()) {
            echo "<p>Note added successfully.</p>";
        } else {
            echo "<p>Error adding note.</p>";
        }
    }
} else {
    echo "No customer ID provided.";
}

$stmt->close();
$conn->close();
?>

<form method="POST">
    <h3>Add Note for Customer</h3>
    <textarea name="note" rows="4" cols="50" placeholder="Enter note here..."></textarea><br>
    <button type="submit" name="add_note">Add Note</button>
</form>

<a href="customer_interaction.php">Back to Customer List</a>
