<?php
include('connect.php');
include("navBar.php"); // use same navbar for consistency
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f4f6fa;
        }

        .container {
            padding: 30px;
        }

        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding: 20px;
        }

        .card h2 {
            margin: 0 0 15px 0;
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
        }

        .admin-table th, .admin-table td {
            padding: 12px 14px;
            text-align: center;
            border-bottom: 1px solid #e5e5e5;
        }

        .admin-table th {
            background: #f9fafc;
            font-size: 14px;
            font-weight: 600;
            color: #34495e;
            text-transform: uppercase;
        }

        .admin-table tr:hover {
            background: #f4f9ff;
        }

        /* Buttons */
        .btn {
            border: none;
            border-radius: 6px;
            padding: 6px 10px;
            margin: 2px;
            cursor: pointer;
            font-size: 14px;
            color: white;
        }
        .btn-primary { background: #3498db; }
        .btn-danger { background: #e74c3c; }
        .btn-success { background: #27ae60; }
        .btn i { font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <h2>
            Admins
            <a href="admin_form.php">
                <button class="btn btn-success"><i class="fa fa-plus"></i> Add Admin</button>
            </a>
        </h2>

        <table class="admin-table">
            <tr>
                <th>Admin ID</th>
                <th>Admin Name</th>
                <th>Admin Contact</th>
                <th>Password</th>
                <th colspan="2">Actions</th>
            </tr>

            <?php
            $sql = "SELECT * FROM admin";
            $result = mysqli_query($connect, $sql);

            if ($result && mysqli_num_rows($result) > 0) {
                while ($admin = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$admin['admin_id']}</td>
                            <td>{$admin['admin_name']}</td>
                            <td>{$admin['admin_contact']}</td>
                            <td>{$admin['admin_pass']}</td>
                            <td>
                                <a href='admin_update.php?admin_id=" . urlencode($admin['admin_id']) . "'>
                                    <button class='btn btn-primary'><i class='fa fa-edit'></i></button>
                                </a>
                                <a href='admin_delete.php?admin_id=" . urlencode($admin['admin_id']) . "' onclick=\"return confirm('Are you sure you want to delete this admin?')\">
                                    <button class='btn btn-danger'><i class='fa fa-trash'></i></button>
                                </a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No admins found.</td></tr>";
            }
            ?>
        </table>
    </div>
</div>

</body>
</html>
