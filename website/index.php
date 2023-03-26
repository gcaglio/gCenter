<?php
session_start();

?>
<html>
  <head>
    <title>gCenter - Login</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
  </head>
  <body>

  <div class="login_outer_container">
  <div class="login_middle_container">
  <div class="login_inner_container">

  <span class="login_form">
   <form method="POST" action="./manage_login.php">
     <table class="tbl_login" align="center">
     <tr><td colspan="2"><?php print $_SESSION["message"] ?></td></tr>
       <tr><th>Username</th><td><input type="text" name="uname" size="20" /></td></tr>
       <tr><th>Password</th><td><input type="password" name="passwd" size="20" /></td></tr>
       <tr><td colspan="2" align="right"><button type="submit" name="action" value="login">Log In</button></td></tr>
     </table>
   </form> 
  </span>
  <span class="login_product_info">
   gCenter
    <span class="login_version">
     <?php include "version.txt"; ?>
    </span>
  </span>


  </div><!-- inner -->




  </div><!-- middle -->
  </div><!-- outer -->
  </body>
</html>
