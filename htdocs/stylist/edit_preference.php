<?php
include('db_connection.php');

// Check if Stylist is logged in
session_start();
if (!isset($_SESSION['stylist_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// Check if preference ID is passed in URL
if (isset($_GET['id'])) {
    $preferenceID = $_GET['id'];

    // Fetch current preference details
    $query = "SELECT cp.Note, c.Name AS CustomerName, s.Name AS StylistName 
              FROM CustomerPreferences cp 
              JOIN Customer c ON cp.CustomerID = c.CustomerID
              JOIN Stylist s ON cp.StylistID = s.StylistID
              WHERE cp.PreferenceID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $preferenceID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $note = $row['Note'];
        $customerName = $row['CustomerName'];
        $stylistName = $row['StylistName'];
    } else {
        echo "Preference not found.";
        exit();
    }
} else {
    echo "No preference ID provided.";
    exit();
}

// Update preference note
if (isset($_POST['update_note'])) {
    $newNote = $_POST['note'];

    // Update note in the CustomerPreferences table
    $updateQuery = "UPDATE CustomerPreferences SET Note = ? WHERE PreferenceID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('si', $newNote, $preferenceID);
    if ($stmt->execute()) {
        echo "Note updated successfully.";
    } else {
        echo "Error updating note.";
    }
}

?>

<form method="POST">
    <h2>Edit Preference for <?php echo $customerName; ?> (Stylist: <?php echo $stylistName; ?>)</h2>
    <label for="note">Note:</label><br>
    <textarea name="note" rows="4" cols="50"><?php echo htmlspecialchars($note); ?></textarea><br>
    <button type="submit" name="update_note">Update Note</button>
</form>

<a href="customer_preferences.php">Back to Preferences</a>
