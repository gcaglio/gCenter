<?php
require_once "../common/db.php";
require_once "../conf/db.php";
# return vm informations

session_start();
if ( ! (isset($_SESSION["_CURRENT_USER"]) ) ){
  $_GET["message"]="Session not valid. Please login.";
  header('Location: ./index.php');
  exit;
}




$con=getConnection($servername,$username,$password,$dbname);


if (  isset($_GET["hostname"]) && isset($_GET["vmid"]) && "get_vm_info"==$_GET["action"]  ) {
  $host=mysqli_real_escape_string($con,$_GET["hostname"]);
  $vmid=mysqli_real_escape_string($con,$_GET["vmid"]);

  $sql_vm="select * from hyperv_virtual_machines where vm_id='$vmid' and hostname='$host' order by timestamp DESC limit 1 ; ";

  $result_vm=mysqli_query($con,$sql_vm);
  while ($row = $result_vm->fetch_assoc()) {
    $vm_id=$row["vm_id"];
    $vm_name=$row["vm_name"];
    $host=$row["hostname"];
    $health_state=$row["health_state"];
    $name=$row["vm_name"];
    $last_seen_ts=$row["timestamp"];
    $uptime_millisec=$row["uptime_millisec"];
    $status_descriptions=$row["status_descriptions"];
    $status=$row["status"];
    $enabled_state=$row["enabled_state"];
    $memory_virtualquantity=$row["memory_virtualquantity"];
    $memory_limit=$row["memory_limit"];
    $memory_reservation=$row["memory_reservation"];


    $_wmi_enabled_state_poweroff = 3 ;
    $_wmi_enabled_state_poweron = 2 ;

?>
    <h2><?php print $host ?> / <?php print $vm_name ?> </h2>
    <span class="spn_50">
      <table class="tbl_vm_info">
       <tr><td class="tbl_info_header" colspan="2">VM info</td></tr>
       <tr><th>VMid</th><td><?php print $vm_id ?></td></tr>
       <tr><th>VM name</th><td><?php print $vm_name ?></td></tr>
       <tr><th>Hyper-V host</th><td><?php print $host ?></td></tr>
       <tr><th>Health state</th><td><?php print $health_state ?></td></tr>
       <tr><th>Last seen</th><td><?php print $last_seen_ts ?></td></tr>
      </table>
      <br/>
    </span>

    <span class="spn_50">
      <table class="tbl_vm_info">
        <tr><td class="tbl_info_header" colspan="2">State</td></tr>
        <tr>
          <th>Power state</th>
          <td><?php print $enabled_state ?>

          <?php  if ( $enabled_state == $_wmi_enabled_state_poweroff  ){ ?>
            <span class="btn_command" style="float:right" onclick="poweronHypervVm('<?php print $host ?>','<?php print $vm_name ?>')">[Power On]</span>
          <?php }else{ ?>
<!--            <span class="btn_command" style="float:right" onclick="rebootHypervVm('<?php print $host ?>','<?php print $vm_name ?>')">[Reboot]</span>
            <span class="btn_command" style="float:right" onclick="resetHypervVm('<?php print $host ?>','<?php print $vm_name ?>')">[Reset]</span> -->
            <span class="btn_command" style="float:right" onclick="poweroffHypervVm('<?php print $host ?>','<?php print $vm_name ?>')">[Power Off]</span>
          <?php  } ?>
          </td>
	</tr>
        <tr><th>Status</th><td><?php print $status ?></td></tr>
        <tr><th>Status descriptions</th><td><?php print $status_descriptions ?></td></tr>

      </table>
      <br/>
      <table class="tbl_vm_info">
        <tr><td class="tbl_info_header" colspan="2">HW summary</td></tr>
        <!--<tr><th>CPU</th><td><?php print $cfg_numCpu ?></td></tr> -->
        <tr><th>Memory configured (Mb)</th><td><?php print $memory_virtualquantity ?></td></tr>
        <tr><th>Memory limit (Mb)</th><td><?php print $memory_limit ?></td></tr>
        <tr><th>Memory reservation (Mb)</th><td><?php print $memory_reservation ?></td></tr>
      </table>

    </span>

<?php
  }
}
?>
