<?php
include("connect.php");

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Check if customer email is provided in URL
if (isset($_GET['cust_email'])) {
    $cust_email = trim($_GET['cust_email']);

    // Prepare the DELETE statement
    $stmt = $connect->prepare("DELETE FROM customer WHERE cust_email = ?");
    $stmt->bind_param("s", $cust_email);

    // Execute the statement
    if ($stmt->execute()) {
        // ✅ Redirect to target page after successful delete
        header("Location: customer_list.php?deleted=success");
        exit();
    } else {
        // ❌ If deletion fails, show error
        echo "❌ Failed to delete customer: " . $stmt->error;
    }

    $stmt->close();
    $connect->close();
} else {
    echo "❌ No customer email provided.";
}
	