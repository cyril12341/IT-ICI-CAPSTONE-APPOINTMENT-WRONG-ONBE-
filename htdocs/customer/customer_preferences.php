<?php
// Include the database connection
include('db.php');

// Start the session to get the logged-in customer
session_start();

// Check if customer is logged in
if (!isset($_SESSION['customer_id'])) {
    header('Location: customer_login.php');
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch all stylists
$stylist_query = "SELECT * FROM Stylist";
$stylist_result = $conn->query($stylist_query);

// Check if form is submitted to save preferences
if (isset($_POST['save_preferences'])) {
    $preferred_stylist = $_POST['preferred_stylist'];

    // Check if the customer already has a preference, if yes update it, else insert a new preference
    $check_existing_preference = "SELECT * FROM CustomerPreferences WHERE CustomerID = '$customer_id'";
    $existing_result = $conn->query($check_existing_preference);

    if ($existing_result->num_rows > 0) {
        // Update the existing preference
        $update_stylist_query = "UPDATE CustomerPreferences SET StylistID = '$preferred_stylist' 
                                 WHERE CustomerID = '$customer_id'";
        $conn->query($update_stylist_query);
    } else {
        // Insert a new preference
        $insert_stylist_query = "INSERT INTO CustomerPreferences (CustomerID, StylistID) 
                                 VALUES ('$customer_id', '$preferred_stylist')";
        $conn->query($insert_stylist_query);
    }

    echo "Preferred stylist updated successfully!";
}
?>


<h2>Manage Your Preferences</h2>

<form method="POST" action="">
    <label for="preferred_stylist">Select Preferred Stylist/Barber:</label>
    <select name="preferred_stylist" id="preferred_stylist">
        <?php while ($stylist = $stylist_result->fetch_assoc()) { ?>
            <option value="<?= $stylist['StylistID'] ?>" 
                <?= (getPreferredStylist($customer_id, $conn) == $stylist['StylistID']) ? 'selected' : '' ?>>
                <?= $stylist['Name'] ?>
            </option>
        <?php } ?>
    </select><br>

    <button type="submit" name="save_preferences">Save Preferences</button>
</form>

<?php
// Function to get the preferred stylist of the customer
function getPreferredStylist($customer_id, $conn) {
    $query = "SELECT StylistID FROM CustomerPreferences WHERE CustomerID = '$customer_id'";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['StylistID'];
    }
    return null;
}
?>
