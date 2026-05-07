<?php
    include('connect.php');
    include('navBar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Truck List</title>
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

    /* Card wrapper */
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

    /* Table */
    .truck-table {
        width: 100%;
        border-collapse: collapse;
    }

    .truck-table th, .truck-table td {
        padding: 12px 14px;
        text-align: center;
        border-bottom: 1px solid #e5e5e5;
    }

    .truck-table th {
        background: #f9fafc;
        font-size: 14px;
        font-weight: 600;
        color: #34495e;
        text-transform: uppercase;
    }

    .truck-table tr:hover {
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
      Trucks
      <a href="truck_form.php">
        <button class="btn btn-success"><i class="fa fa-plus"></i> Add Truck</button>
      </a>
    </h2>

    <table class="truck-table">
      <tr>
        <th>Truck Plate</th>
        <th>Truck Model</th>
        <th>Load Weight</th>
        <th colspan="2">Actions</th>
      </tr>

      <?php
      $sql = "SELECT * FROM truck";
      $result = mysqli_query($connect, $sql);

      while ($truck = mysqli_fetch_array($result)) {
          echo "<tr>
                  <td>{$truck['truck_plate']}</td>
                  <td>{$truck['truck_model']}</td>
                  <td>{$truck['load_weight']}</td>
                  <td>
                      <a href='truck_update.php?truck_plate=" . urlencode($truck['truck_plate']) . "'>
                          <button class='btn btn-primary'><i class='fa fa-edit'></i></button>
                      </a>
                      <a href='delete_truck.php?truck_plate=" . urlencode($truck['truck_plate']) . "' onclick=\"return confirm('Are you sure?')\">
                          <button class='btn btn-danger'><i class='fa fa-trash'></i></button>
                      </a>
                  </td>
                </tr>";
      }
      ?>
    </table>
  </div>
</div>

</body>
</html>
