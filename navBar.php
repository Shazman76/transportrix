<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        /* ===== Top Navbar ===== */
        .top-navbar {
            width: 100%;
            height: 70px; /* increased height to fit larger logo */
            background-color: white;
            color: black;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1100;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }

        /* Left section (toggle + logo + title) */
        .top-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .top-navbar .logo-img {
            height: 55px; /* bigger logo */
            width: auto;
        }

        .top-navbar .title {
            font-size: 22px;
            font-weight: bold;
        }

        /* Logout button */
        .logout-btn {
            background-color: transparent;
            color: black;
            padding: 10px 18px;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
        }
        .logout-btn:hover {
            background-color: transparent;
        }

        /* ===== Sidebar ===== */
        .sidebar {
            height: 100%;
            width: 220px;
            position: fixed;
            top: 0;
            left: -220px; /* hidden by default */
            background-color: white;
            padding-top: 70px; /* push down under navbar */
            transition: 0.3s;
            z-index: 1000;
        }
        .sidebar.active {
            left: 0;
        }
        .sidebar a {
            padding: 12px 20px;
            text-decoration: none;
            font-size: 16px;
            color: black;
            display: block;
            transition: 0.2s;
        }
        .sidebar a:hover {
            background-color: #34495e;
            padding-left: 30px;
        }

        /* ===== Toggle Button ===== */
        .toggle-btn {
            font-size: 24px;
            cursor: pointer;
            color: black;
        }

        /* ===== Page Content ===== */
        .content {
            margin-top: 70px; /* space under navbar */
            padding: 20px;
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
            <img src="image/logo.jpg" alt="logo" class="logo-img">
            <div class="title">Transportrix</div>
        </div>
        <a href="logout.php" class="logout-btn"><i class="fa fa-sign-out-alt"></i> Logout</a>
    </div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <a href="Menu.php"><i class="fa fa-home"></i> Dashboard</a>
    <a href="admin_list.php"><i class="fa fa-user-shield"></i> Admin</a>
    <a href="customer_list.php"><i class="fa fa-users"></i> Customers</a>
    <a href="driver_list.php"><i class="fa fa-id-card"></i> Drivers</a>
    <a href="truck_list.php"><i class="fa fa-warehouse"></i> Trucks</a>
    <a href="invoice_list.php"><i class="fa fa-file-invoice"></i> Invoices</a>
    <a href="manage_prices.php"><i class="fa fa-tags"></i> Price Lookup</a>
    </div>

    <!-- Page Content -->
    <div class="content">
       
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("active");
        }
    </script>

</body>
</html>
