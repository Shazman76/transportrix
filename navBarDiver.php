<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('connect.php');

// Ensure user is logged in as driver
if (!isset($_SESSION['userid']) || $_SESSION['status'] !== 'driver') {
    header("Location: login.php");
    exit();
}

$driver_id = $_SESSION['userid'];
$driver_name = 'Driver';

// Fetch driver's name from database
$query = "SELECT driver_name FROM driver WHERE driver_id = ?";
$stmt = $connect->prepare($query);
$stmt->bind_param("s", $driver_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    $driver_name = $row['driver_name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Driver Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: url('image/truck_bg.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
        }

        /* ===== Top Navbar (Updated Colors) ===== */
        .top-navbar {
            width: 100%;
            height: 70px;
            background-color: white; /* White background */
            color: black;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1100;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

        .top-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-img {
            height: 50px;
            width: 50px;
            object-fit: contain;
            border-radius: 6px;
            margin-right: 10px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            color: black; /* Black title text */
        }

        .logout-btn {
            background-color: transparent;
            color: black; /* Black logout text */
            font-size: 15px;
            text-decoration: none;
            font-weight: bold;
            margin-right: 12px;
        }

        .logout-btn:hover {
            color: #e74c3c;
        }

        .toggle-btn {
            font-size: 24px;
            cursor: pointer;
            color: black; /* Sidebar icon (hamburger) in black */
        }

        /* ===== Sidebar ===== */
        .sidebar {
            height: 100%;
            width: 220px;
            position: fixed;
            top: 0;
            left: -220px;
            background-color: white;
            padding-top: 70px;
            transition: 0.3s;
            z-index: 1000;
            color: #ccc;
        }

        .sidebar.active {
            left: 0;
        }

        .sidebar a {
            padding: 15px 25px;
            text-decoration: none;
            font-size: 18px;
            color: black;
            display: block;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background-color: #34495e;
            padding-left: 35px;
            color: white;
        }

        .sidebar .close-btn {
            font-size: 24px;
            color: #ccc;
            text-align: right;
            padding: 10px 25px;
            cursor: pointer;
        }

        .sidebar .close-btn:hover {
            color: white;
        }

        /* ===== Page Content ===== */
        .content {
            margin-top: 70px;
            padding: 20px;
            color: white;
        }

        .welcome-box {
            background-color: rgba(0, 0, 0, 0.6);
            padding: 40px;
            border-radius: 15px;
            max-width: 600px;
            margin: 0px auto;
            text-align: center;
            border: 2px solid #DE0003;
        }

        .welcome-box h1 {
            margin-bottom: 20px;
            color: #DE0003;
        }

        /* ===== Responsive Styling ===== */
        @media screen and (max-width: 600px) {
            .top-left .title {
                font-size: 16px;
            }

            .logo-img {
                height: 40px;
                width: 40px;
            }

            .logout-btn {
                margin-right: 8px;
                font-size: 14px;
            }

            .sidebar {
                width: 180px;
                padding-top: 70px;
            }

            .sidebar a {
                font-size: 16px;
                padding: 12px 20px;
            }

            .sidebar a:hover {
                padding-left: 25px;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navbar -->
    <div class="top-navbar">
        <div class="top-left">
            <div class="toggle-btn" onclick="toggleSidebar()">
                <i class="fa fa-bars"></i>
            </div>
            <img src="image/logo.jpg" alt="logo" class="logo-img" />
            <div class="title">TRANSPORTRIX</div>
        </div>
        <a href="logout.php" class="logout-btn">LOG OUT</a>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="close-btn" onclick="toggleSidebar()"><i class="fa fa-times"></i></div>
        <a href="driver_menu.php"><i class="fa fa-home"></i> Home</a>
        <a href="driver_shipment.php"><i class="fa fa-eye"></i> View Order</a>
        <a href="driver_editAvailable.php"><i class="fa fa-edit"></i> Edit Availability</a>
    </div>

    <!-- Page Content -->
    <div class="content">
        <div class="welcome-box">
            <h1>Welcome, <?php echo htmlspecialchars($driver_name); ?>!</h1>
        </div>
    </div>

    <!-- Sidebar Toggle Script -->
    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>

</body>
</html>
