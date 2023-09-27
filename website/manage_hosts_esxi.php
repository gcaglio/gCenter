<?php
require_once "../common/db.php";
require_once "../conf/db.php";
require_once "../conf/eventlog.php";
require_once( "../common/check_roles.php");
require_once( "../common/eventlog.php");
# return hosts informations

if(!isset($_SESSION)) session_start();

if ( ! (isset($_SESSION["_CURRENT_USER"]) ) ){
  $_GET["message"]="Session not valid. Please login.";
  header('Location: ./index.php');
  exit;
}




$con=getConnection($servername,$username,$password,$dbname);


if ( isAdmin($con) && "get_hosts_esxi"==$_GET["action"]  ) {
?>
    <h2>Hosts </h2>


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
      <form action="./manage_hosts_esxi.php?action=insert_hosts_esxi" method="POST">

        <table class="tbl_insert_hosts_esxi">
          <tr>
	    <th>Hostname/IP</th>
	    <th>Username</th>
	    <th>Password</th>
	  </tr>

	  <tr>
            <td>
              <input type="text" name="hostname" title="hostname" size="20" />
            </td>
	    <td>
              <input type="text" name="username" title="username" size="20" />
            </td>
            <td>
              <input type="password" name="password" title="password" size="20" />
            </td>

            <td>
              <button type="submit" value="Insert host">Insert host</button>
            </td>


          </tr>

        </table>
        <input type="hidden" name="action" value="insert_host_esxi"/>
      </form>
    </span>
 
    <br/>
    <br/>
    <span class="spn_100">
    <table class="tbl_hosts_info">
      <tr>
        <th>Hostname</th>
        <th>Username</th>
        <th></th>
      </tr>
<?php

  $sql="select hostname, username, password from hosts";
  $result=mysqli_query($con,$sql);
  while ($row = $result->fetch_assoc()) {
    $username=$row["username"];
    $hostname=$row["hostname"];
    $password=$row["password"];

?>
      <tr>
        <td><?php print $hostname ?></td>
        <td><?php print $username ?></td>
	<td><img onclick="deleteHostEsxi('<?php print md5( $username.";".$hostname.";".$password ) ?>')" src="./images/delete_host.png" title="delete host"/></td>
      </tr>
  
<?php
  } // while
?>

   </table>
   </span>




<?php
}else if ( isAdmin($con) && "delete_host_esxi"==$_GET["action"] && isset($_GET["hash"])  ) {
  $hash=mysqli_real_escape_string($con,$_GET["hash"]);
  $sql_delete_user="delete from hosts where '".$hash."' =  md5( concat(username,concat(';',concat(hostname,concat(';',password)))) );";
#  echo $sql_delete_role;
  if (mysqli_query($con,$sql_delete_user)) {
    $_SESSION["successful_message"]="Host deleted";
    logEventAddHostEsxiSuccessful($con,$hostname,"Host deleted succesfully") ;
  }else{
    $_SESSION["error_message"]="Error deleting host";
    logEventAddHostEsxiError($con,$hostname,"Host deleted succesfully");
  }

  header('Location: ./show_message.php');
  exit;






}else if ( isAdmin($con) && isset($_POST["username"]) && isset($_POST["action"]) && "insert_host_esxi" == $_POST["action"]  && isset($_POST["password"]) && isset($_POST["hostname"]) && strlen($_POST["username"])>0 && strlen($_POST["hostname"])>0  && strlen($_POST["password"])>0  ) {
  # insert new hosts
  $username=mysqli_real_escape_string($con,$_POST["username"]);
  $hostname=mysqli_real_escape_string($con,$_POST["hostname"]);
  $password=mysqli_real_escape_string($con, md5($_POST["password"]));

  $sql_insert="insert into hosts (username,hostname,password) values ('".$username."','".$hostname."','".$password."'); ";
  if (mysqli_query($con,$sql_insert)) {
    $_SESSION["successful_message"]="Host '$hostname' inserted";
    logEventAddHostEsxiSuccessful($con,$hostname,"Host added succesfully");
  }else{
    $_SESSION["error_message"]="Error inserting host '$hostname'.";
    logEventAddHostEsxiError($con,$hostname,"Error adding host");
  }

  header('Location: ./home.php?action=show_manage_hosts_esxi');
  exit;

}else{
?>
  <span class="error_message">
    User is not admin on '*'
  </span>



<?php
}

?>
