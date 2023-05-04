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

if ( "get_all_hosts_info"==$_GET["action"]  ) {

  // esxi
  $sql_esxi_hosts="select hosts.hostname, product_name, product_fullname from hosts join hosts_informations on hosts.hostname=hosts_informations.hostname where timestamp = (select max(timestamp) from hosts_informations);";

  //hyperv
  $sql_hyperv_hosts="select hyperv_hosts.hostname from hyperv_hosts join hyperv_hosts_informations on hyperv_hosts.hostname = hyperv_hosts_informations.hostname where timestamp = (select max(timestamp) from hyperv_hosts_informations);";

  $result_esxi_hosts=mysqli_query($con,$sql_esxi_hosts);
  $result_hyperv_hosts=mysqli_query($con,$sql_hyperv_hosts);
?>
    <span class="spn_100">
      <table class="tbl_host_info">
      <tr><td class="tbl_info_header">Hostname</td><td class="tbl_info_header">Type</td><td class="tbl_info_header">Product fullname</td></tr>

<?php
  while ($row = $result_esxi_hosts->fetch_assoc()) {
    $hostname=$row["hostname"];
    $product_name=$row["product_name"];
    $product_fullname=$row["product_fullname"];
?>
	<tr><td><?php print $hostname; ?></td><td><?php print $product_name; ?></td><td><?php print $product_fullname; ?></td></tr>
<?php } //while ESXI ?>

<?php
  while ($row = $result_hyperv_hosts->fetch_assoc()) {
    $hostname=$row["hostname"];
?>
        <tr><td><?php print $hostname; ?></td><td>HyperV</td><td></td></tr>
<?php } //while HYPERV ?>






       </table>
    </span>
    <br/>

<?php
}
?>
