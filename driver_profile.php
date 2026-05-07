<?php
session_start();
include('connect.php');
include('navBarDiver.php'); // Uncomment if you have a navBar for driver

// Redirect if not logged in or not a driver
if (!isset($_SESSION['userid']) || $_SESSION['status'] != 'driver') {
    header("Location: login.php");
    exit();
}

$conn = $connect; // Assuming $connect from connect.php
$userid = $_SESSION['userid'];
$msg = "";

// Fetch current driver data
$query = "SELECT * FROM driver WHERE driver_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $userid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    // User not found - maybe redirect or show error
    die("Driver not found.");
}

// Fetch all available trucks for dropdown
$truckList = [];
$truckQuery = "SELECT truck_plate FROM trucks";
$truckResult = $conn->query($truckQuery);
if ($truckResult && $truckResult->num_rows > 0) {
    while ($row = $truckResult->fetch_assoc()) {
        $truckList[] = $row['truck_plate'];
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['driver_name']);
    $truckPlate = trim($_POST['truck_plate']);
    $contact = trim($_POST['contact_driver']);
    $password = trim($_POST['driver_pass']);

    // Optional: hash password if you want (recommended)
    // $password = password_hash($password, PASSWORD_DEFAULT);

    // Update driver info
    $update = "UPDATE driver SET driver_name = ?, truck_plate = ?, contact_driver = ?, driver_pass = ? WHERE driver_id = ?";
    $stmt = $conn->prepare($update);
    $stmt->bind_param("sssss", $name, $truckPlate, $contact, $password, $userid);

    if ($stmt->execute()) {
        $msg = "Profile updated successfully.";
        // Update $user to show updated info in the form
        $user['driver_name'] = $name;
        $user['truck_plate'] = $truckPlate;
        $user['contact_driver'] = $contact;
        $user['driver_pass'] = $password;
    } else {
        $msg = "Error updating profile. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Driver Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f8f9fc;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            background: white;
            margin: 50px auto;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        h2 {
            text-align: center;
            color: #DE0003;
            margin-bottom: 30px;
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input[type=text], input[type=password], input[type=email], select {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            font-size: 16px;
        }
        input[type=submit] {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            background-color: #DE0003;
            border: none;
            color: white;
            font-size: 18px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        input[type=submit]:hover {
            background-color: #a90000;
        }
        .msg {
            text-align: center;
            color: green;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Driver Profile</h2>
    <?php if (!empty($msg)) echo "<p class='msg'>$msg</p>"; ?>
    <form method="post" action="">
        <label>Full Name</label>
        <input type="text" name="driver_name" value="<?php echo htmlspecialchars($user['driver_name']); ?>" required>

        <label>Truck Plate</label>
        <select name="truck_plate" required>
            <option value="">-- Select Truck Plate --</option>
            <?php foreach ($truckList as $plate): ?>
                <option value="<?php echo htmlspecialchars($plate); ?>"
                    <?php if ($plate == $user['truck_plate']) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($plate); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Contact Number</label>
        <input type="text" name="contact_driver" value="<?php echo htmlspecialchars($user['contact_driver']); ?>" required>

        <label>Password</label>
        <input type="password" name="driver_pass" value="<?php echo htmlspecialchars($user['driver_pass']); ?>" required>

        <input type="submit" value="Update Profile">
    </form>
</div>

</body>
</html>
