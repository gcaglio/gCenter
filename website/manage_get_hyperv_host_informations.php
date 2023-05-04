<?php
require_once "../common/db.php";
require_once "../conf/db.php";
# return host informations

session_start();
if ( ! (isset($_SESSION["_CURRENT_USER"]) ) ){
  $_GET["message"]="Session not valid. Please login.";
  header('Location: ./index.php');
  exit;
}




$con=getConnection($servername,$username,$password,$dbname);


if (  isset($_GET["hostname"]) && "get_host_info"==$_GET["action"]  ) {
  $host=mysqli_real_escape_string($con,$_GET["hostname"]);
  $sql="select * from hyperv_hosts_informations where timestamp=(select max(timestamp) from hyperv_hosts_informations where hostname='$host') and hostname='$host';";
  $result=mysqli_query($con,$sql);
  while ($row = $result->fetch_assoc()) {
    $last_seen=$row["timestamp"];
    $health_state=$row["health_state"];
    $status=$row["status"];
    $status_descriptions=$row["status_descriptions"];
?>
    <span class="spn_50">
    <table class="tbl_host_info">
      <tr><td class="tbl_info_header" colspan="2">Host informations</td></tr>
      <tr><th>Last seen timestamp</th><td><?php print $last_seen ?></td></tr>
      <tr><th>Hostname</th><td><?php print $host_name ?></td></tr>
      <tr><th>Health state</th><td><?php print $health_state?></td></tr>
      <tr><th>Status</th><td><?php print $status ?></td></tr>
      <tr><th>Status descriptions</th><td><?php print $status_descriptions ?></td></tr>
    </table>
    </span>
  

    <br />

<?php
  } // while host


}

?>
