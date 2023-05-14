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
//  $sql_esxi_hosts="select hosts.hostname, product_name, product_fullname from hosts join hosts_informations on hosts.hostname=hosts_informations.hostname where timestamp = (select max(timestamp) from hosts_informations);";
    $sql_esxi_hosts="select hosts.hostname, max(timestamp) as last_seen, product_name, is_in_maintenance, truncate(memory_size/1024/1024/1024,1) as memory_gb, cpu_cores   from hosts left join hosts_informations on hosts.hostname = hosts_informations.hostname group by hostname, is_in_maintenance, memory_size, cpu_cores ;";
  //hyperv
//  $sql_hyperv_hosts="select hyperv_hosts.hostname from hyperv_hosts left join hyperv_hosts_informations on hyperv_hosts.hostname = hyperv_hosts_informations.hostname where timestamp = (select max(timestamp) from hyperv_hosts_informations);";
  $sql_hyperv_hosts="select hyperv_hosts.hostname, max(timestamp) as last_seen, status, health_state  from hyperv_hosts left join hyperv_hosts_informations on hyperv_hosts.hostname = hyperv_hosts_informations.hostname group by hostname, status, health_state;";

  $result_esxi_hosts=mysqli_query($con,$sql_esxi_hosts);
  $result_hyperv_hosts=mysqli_query($con,$sql_hyperv_hosts);
?>
    <span class="spn_100">
      <table class="tbl_host_info">
      <tr><td class="tbl_info_header">Hostname</td><td class="tbl_info_header">OS</td><td class="tbl_info_header">Last seen</td><td class="tbl_info_header">Is in maintenance</td><td class="tbl_info_header">Memory (Gb)</td><td class="tbl_info_header">Cpu cores</td></tr>

<?php
  while ($row = $result_esxi_hosts->fetch_assoc()) {
    $hostname=$row["hostname"];
    $is_in_maintenance=$row["is_in_maintenance"];
    $product_name=$row["product_name"];
    $last_seen=$row["last_seen"];
    $memory=$row["memory_gb"];
    $core=$row["cpu_cores"];
?>
	<tr><td><?php print $hostname; ?></td><td><?php print $product_name; ?></td><td><?php print $last_seen; ?></td><td><?php print $is_in_maintenance;?></td><td><?php print $memory; ?></td><td><?php print $core; ?></td></tr>
<?php } //while ESXI ?>
      </table>
      <table class="tbl_host_info">
      <tr><td class="tbl_info_header">Hostname</td><td class="tbl_info_header">OS</td><td class="tbl_info_header">Last seen</td><td class="tbl_info_header">Status</td><td class="tbl_info_header">Health state</td></tr>
<?php
  while ($row = $result_hyperv_hosts->fetch_assoc()) {
    $hostname=$row["hostname"];
    $last_seen=$row["last_seen"];
    $status=$row["status"];
    $health_state=$row["health_state"];
?>
	<tr><td><?php print $hostname; ?></td><td>HyperV</td><td><?php print $last_seen; ?></td><td><?php print $status; ?></td><td><?php print $health_state; ?></td></tr>
<?php } //while HYPERV ?>






       </table>
    </span>
    <br/>

<?php
}
?>
