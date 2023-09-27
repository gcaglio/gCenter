<?php
require_once "../common/db.php";
require_once "../conf/db.php";
require_once( "../common/check_roles.php");
# return roles informations

if(!isset($_SESSION)) session_start();

if ( ! (isset($_SESSION["_CURRENT_USER"]) ) ){
  $_GET["message"]="Session not valid. Please login.";
  header('Location: ./index.php');
  exit;
}




$con=getConnection($servername,$username,$password,$dbname);


if ( isAdmin($con) && "get_users"==$_GET["action"]  ) {
?>
    <h2>Users  </h2>


<?php 
   if ( isset($_SESSION["successful_message"]) ){
?>
    <span class="success_message">
      <?php print $_SESSION["successful_message"]; unset($_SESSION["successful_message"]); ?>
    </span>
<?php
   }
?>




<?php
   if ( isset($_SESSION["error_message"]) ){
?>
    <span class="error_message">
      <?php print $_SESSION["error_message"]; unset($_SESSION["error_message"]); ?>
    </span>
<?php
   }
?>



    <span class="spn_100">
      <form action="./manage_users.php?action=insert_user" method="POST">

        <table class="tbl_insert_user">
          <tr>
	    <th>Username</th>
	    <th>Password</th>
	    <th>Email address</th>
	  </tr>

          <tr>
	    <td>
              <input type="text" name="username" title="username" size="20" />
            </td>
            <td>
              <input type="password" name="password" title="password" size="20" />
            </td>
            <td>
              <input type="text" name="email" title="email" size="20" />
            </td>

            <td>
              <button type="submit" value="Insert user">Insert user</button>
            </td>


          </tr>

        </table>
        <input type="hidden" name="action" value="insert_user"/>
      </form>
    </span>
 
    <br/>
    <br/>
    <span class="spn_100">
    <table class="tbl_user_info">
      <tr>
        <th>Username</th>
        <th>Email</th>
	<th>Last login</th>
        <th></th>
      </tr>
<?php

  $sql="select users.username as username ,email,max(events.timestamp) as last_login from users left  join events on users.username=events.username and event_code='LOGIN_OK' group by users.username, event_code;";
  $result=mysqli_query($con,$sql);
  while ($row = $result->fetch_assoc()) {
    $username=$row["username"];
    $email=$row["email"];
    $last_login=$row["last_login"];

?>
      <tr>
        <td><?php print $username ?></td>
        <td><?php print $email ?></td>
	<td><?php print $last_login ?></td> 
	<td><img onclick="deleteUser('<?php print md5( $username.";".$email ) ?>')" src="./images/delete_user.png" title="delete user"/></td>
      </tr>
  
<?php
  } // while
?>

   </table>
   </span>




<?php
}else if ( isAdmin($con) && "delete_user"==$_GET["action"] && isset($_GET["hash"])  ) {
  $hash=mysqli_real_escape_string($con,$_GET["hash"]);
  $sql_delete_user="delete from users where '".$hash."' =  md5( concat(username,concat(';',concat(email))) );";
#  echo $sql_delete_role;
  if (mysqli_query($con,$sql_delete_user)) {
    $_SESSION["successful_message"]="User deleted";
    
  }else{
    $_SESSION["error_message"]="Error deleting user";
  }

  header('Location: ./show_message.php');
  exit;






}else if ( isAdmin($con) && isset($_POST["username"]) && isset($_POST["action"]) && "insert_user" == $_POST["action"]  && isset($_POST["password"]) && isset($_POST["email"]) && strlen($_POST["username"])>0 && strlen($_POST["email"])>0  && strlen($_POST["password"])>0  ) {
  # insert new user
  $username=mysqli_real_escape_string($con,$_POST["username"]);
  $email=mysqli_real_escape_string($con,$_POST["email"]);
  $password=mysqli_real_escape_string($con, md5($_POST["password"]));

  $sql_insert="insert into users (username,email,password) values ('".$username."','".$email."','".$password."'); ";
  if (mysqli_query($con,$sql_insert)) {
    $_SESSION["successful_message"]="User '$username' inserted";
  }else{
    $_SESSION["error_message"]="Error inserting user '$username'.";
  }

  header('Location: ./home.php?action=show_manage_users');
  exit;

}else{
?>
  <span class="error_message">
    User is not admin on '*'
  </span>



<?php
}

?>
