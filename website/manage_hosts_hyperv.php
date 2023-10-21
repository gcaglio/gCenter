<?php
require_once "../common/db.php";
require_once "../conf/db.php";
require_once "../conf/eventlog.php";
require_once( "../common/check_roles.php");
require_once( "../common/eventlog.php");

if(!isset($_SESSION)) session_start();

if ( ! (isset($_SESSION["_CURRENT_USER"]) ) ){
  $_GET["message"]="Session not valid. Please login.";
  header('Location: ./index.php');
  exit;
}




$con=getConnection($servername,$username,$password,$dbname);


if ( isAdmin($con) && "get_hosts_hyperv"==$_GET["action"]  ) {
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
      <form action="./manage_hosts_hyperv.php?action=insert_hosts_hyperv" method="POST">

        <table class="tbl_insert_hosts_hyperv">
          <tr>
	    <th>Hostname</th>
	    <th>IP</th>
	    <th>WMI agent tcp port</th>
	  </tr>

	  <tr>
            <td>
              <input type="text" name="hostname" title="hostname" size="20" />
            </td>
	    <td>
              <input type="text" name="ip" title="ip" size="20" />
            </td>
            <td>
              <input type="text" name="wmi_port" title="wmi_port" size="20" />
            </td>

            <td>
              <button type="submit" value="Insert host">Insert host</button>
            </td>


          </tr>

        </table>
        <input type="hidden" name="action" value="insert_host_hyperv"/>
      </form>
    </span>
 
    <br/>
    <br/>
    <span class="spn_100">
    <table class="tbl_hosts_info">
      <tr>
        <th>Hostname</th>
	<th>IP</th>
	<th>WMI agent tcp port</th>
        <th></th>
      </tr>
<?php

  $sql="select hostname, ip, port from hyperv_hosts;";
  $result=mysqli_query($con,$sql);
  while ($row = $result->fetch_assoc()) {
    $ip=$row["ip"];
    $hostname=$row["hostname"];
    $port=$row["port"];

?>
      <tr>
        <td><?php print $hostname ?></td>
        <td><?php print $ip ?></td>
        <td><?php print $port ?></td>
	<td><img onclick="deleteHostHyperv('<?php print md5( $ip.";".$hostname.";".$port ) ?>')" src="./images/delete_host.png" title="delete host"/></td>
      </tr>
  
<?php
  } // while
?>

   </table>
   </span>




<?php
}else if ( isAdmin($con) && "delete_host_hyperv"==$_GET["action"] && isset($_GET["hash"])  ) {
  $hash=mysqli_real_escape_string($con,$_GET["hash"]);
  $sql_delete_host="delete from hyperv_hosts where '".$hash."' =  md5( concat(ip,concat(';',concat(hostname,concat(';',port)))) );";
#  echo $sql_delete_role;
  if (mysqli_query($con,$sql_delete_host)) {
    $_SESSION["successful_message"]="Host deleted";
    logEventAddHostHypervSuccessful($con,$hostname,"Host deleted succesfully") ;
  }else{
    $_SESSION["error_message"]="Error deleting host";
    logEventAddHostHypervError($con,$hostname,"Host deleted succesfully");
  }

  header('Location: ./show_message.php');
  exit;






}else if ( isAdmin($con) && isset($_POST["action"]) && "insert_host_hyperv" == $_POST["action"]  && isset($_POST["ip"]) && isset($_POST["hostname"]) && strlen($_POST["ip"])>0 && strlen($_POST["hostname"])>0  && strlen($_POST["wmi_port"])>0  ) {
  # insert new hosts
  $ip=mysqli_real_escape_string($con,$_POST["ip"]);
  $hostname=mysqli_real_escape_string($con,$_POST["hostname"]);
  $wmi=mysqli_real_escape_string($con, $_POST["wmi_port"]);

  $sql_insert="insert into hyperv_hosts (hostname,ip, port) values ('".$hostname."','".$ip."','".$wmi."'); ";
  if (mysqli_query($con,$sql_insert)) {
    $_SESSION["successful_message"]="Host '$hostname' inserted";
    logEventAddHostHypervSuccessful($con,$hostname,"Host added succesfully");
  }else{
    $_SESSION["error_message"]="Error inserting host '$hostname'.";
    logEventAddHostHypervError($con,$hostname,"Error adding host");
  }

  header('Location: ./home.php?action=show_manage_hosts_hyperv');
  exit;

}else{
?>
  <span class="error_message">
    User is not admin on '*'
  </span>



<?php
}

?>
