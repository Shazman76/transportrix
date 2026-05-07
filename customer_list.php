<?php
    include('connect.php');
    include('navBar.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customer List</title>
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

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .card-header h2 {
        font-size: 20px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    /* Table */
    .customer-table {
        width: 100%;
        border-collapse: collapse;
    }

    .customer-table th, .customer-table td {
        padding: 12px 14px;
        text-align: center;
        border-bottom: 1px solid #e5e5e5;
    }

    .customer-table th {
        background: #f9fafc;
        font-size: 14px;
        font-weight: 600;
        color: #34495e;
        text-transform: uppercase;
    }

    .customer-table tr:hover {
        background: #f4f9ff;
    }

    /* Buttons */
    .btn {
        border: none;
        border-radius: 6px;
        padding: 8px 12px;
        margin: 2px;
        cursor: pointer;
        font-size: 14px;
        color: white;
        display: inline-flex;
        align-items: center;
        gap: 6px;
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
    <div class="card-header">
      <h2>Customers</h2>
      <a href="customer_form.php">
        <button class="btn btn-success"><i class="fa fa-plus"></i> Add Customer</button>
      </a>
    </div>

    <table class="customer-table">
      <tr>
        <th>Email</th>
        <th>Customer Name</th>
        <th>Contact</th>
        <th>Address</th>
        <th colspan="2">Actions</th>
      </tr>

      <?php
      $sql = "SELECT * FROM customer";
      $result = mysqli_query($connect, $sql);

      while ($customer = mysqli_fetch_array($result)) {
          echo "<tr>
                  <td>{$customer['cust_email']}</td>
                  <td>{$customer['cust_name']}</td>
                  <td>{$customer['cust_contact']}</td>
                  <td>{$customer['address']}</td>
                  <td>
                      <a href='customer_update.php?cust_email=" . urlencode($customer['cust_email']) . "'>
                          <button class='btn btn-primary'><i class='fa fa-edit'></i></button>
                      </a>
                      <a href='delete_customer.php?cust_email=" . urlencode($customer['cust_email']) . "' onclick=\"return confirm('Are you sure?')\">
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
