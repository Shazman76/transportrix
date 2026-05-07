<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
    include('connect.php');
	include('navBarDiver.php');
}

// Dummy values – replace with actual database queries
$pendingOrders = 5;
$completedDeliveries = 23;
$availability = "Available";
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

        .content {
            padding: 80px 30px 30px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Dashboard Cards */
        .dashboard-cards {
            display: flex;
            justify-content: center;
            gap: 25px;
            flex-wrap: wrap;
            max-width: 900px;
            margin-top: -700px;
        }

        .card {
            background-color: rgba(0,0,0,0.6);
            flex: 1 1 250px;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 0 8px #DE0003;
            transition: transform 0.3s;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card h2 {
            font-size: 40px;
            margin: 10px 0;
            color: #DE0003;
        }

        .card p {
            font-size: 20px;
            margin: 0;
            color: #ddd;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-cards {
                flex-direction: column;
                gap: 20px;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- Page Content -->
    <div class="content">

        <div class="dashboard-cards">
            <div class="card">
                <h2><?php echo $pendingOrders; ?></h2>
                <p>Pending Orders</p>
            </div>
            <div class="card">
                <h2><?php echo $completedDeliveries; ?></h2>
                <p>Completed Deliveries</p>
            </div>
            <div class="card">
                <h2><?php echo htmlspecialchars($availability); ?></h2>
                <p>Availability Status</p>
            </div>
        </div>

    </div>

</body>
</html>
