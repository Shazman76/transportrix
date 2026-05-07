<?php
   session_start();
   include('connect.php');
   if(isset($_POST['submit'])){
       $userid = $_POST['userid'];
       $password = $_POST['password'];
       $jumpa = FALSE;
       
       if($jumpa == FALSE){
           $sql = "SELECT * FROM admin";
           $result = mysqli_query($connect, $sql);
if (!$result) {
    die("Query Failed: " . mysqli_error($connect));
}

           while($admin = mysqli_fetch_array($result)) {
               if($admin['admin_id'] == $userid && $admin["admin_pass"] == $password){
                   $jumpa = TRUE;
                   $_SESSION['userid'] = $admin['admin_id'];
                   $_SESSION['name'] = $admin['admin_name'];
                   $_SESSION['status'] = 'admin';
                   break;   
               }
           }
       }
       
       if ($jumpa == FALSE){
           $sql = "SELECT * FROM driver";
           $result = mysqli_query($connect, $sql);
		   if (!$result) {
    	   die("Query Failed: " . mysqli_error($connect));
		   }
           while($driver = mysqli_fetch_array($result)){
               if($driver['driver_id'] == $userid && $driver["driver_pass"] == $password){
                   $jumpa = TRUE;
                   $_SESSION['userid'] = $driver['driver_id'];
                   $_SESSION['name'] = $driver['driver_name'];
                   $_SESSION['status'] = 'driver';
                   break;
               }
            }
        }
       
       
       if($jumpa == TRUE){
           if($_SESSION['status']=='admin')
               header("Location: Menu.php");
           else if ($_SESSION['status']=='driver')
               header("Location: driver_menu.php");
       }
       else
           echo "<script>alert('wrong email/ID or password');
                  window.location='login.php'</script>";
   }
?>
<html>
    <link rel="stylesheet" href="button.css">
    <link rel="stylesheet" href="newLoginStyle.css">
    
    <body>
  <header>
  <img src="image/logo.jpg" width="" height="100"> 
  <h2 class = "brand-name">TRANSPORTRIX</h2>
  <nav class ="navigation">
      <a href ="index.php">HOME</a>
      <a href ="Aboutus.html">ABOUT US</a>
      <a href ="ContactUs.html">CONTACT US</a>
  </nav>
  </header>
        <h3 class="pendek">Log-In</h3>
        <form class="pendek" action="login.php" method="post">
           <table>
           <tr>
               <td><img src="image/user.jpg"></td>
               <td><input type="text" name="userid" placeholder="ID OR EMAIL" required></td>
           </tr>
           <tr>
               <td><img src="image/lock.jpg"></td>
               <td><input type="password" name="password" placeholder="PASSWORD" required></td>
           </tr>   
           </table>
		   <div class = "form-buttons">
           <button class="login" type="submit" name="submit">Login</button>
		   </div>
        </form>
    </body>
</html>