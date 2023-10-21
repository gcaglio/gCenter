<?php
require_once "../common/db.php";
require_once "../common/eventlog.php";
require_once "../conf/db.php";
require_once "../conf/eventlog.php";
# return vswitch informations

if(!isset($_SESSION)) session_start();
if ( ! (isset($_SESSION["_CURRENT_USER"]) ) ){
  $_GET["message"]="Session not valid. Please login.";
  header('Location: ./index.php');
  exit;
}



$con=getConnection($servername,$username,$password,$dbname);


if (  isset($_GET["hostname"]) && isset($_GET["vswitch"]) && "get_vswitch_info"==$_GET["action"]  ) {
  $host=mysqli_real_escape_string($con,$_GET["hostname"]);
  $vs_name=mysqli_real_escape_string($con,$_GET["vswitch"]);
  $current_user=$_SESSION["_CURRENT_USER"];

  logEventInfo($con,"/$host/vswitch/$vs_name","display hyperv vswitch details");

  $sql_vs="select *  from hyperv_vswitch_informations where vswitch_name='$vs_name' and  hostname='$host' and timestamp=(select max(timestamp) from hyperv_vswitch_informations where hostname='$host'); ";

?>

    <h2><?php print $host ?> / <?php print $vs_name ?></h2>

    <span class="spn_100">
      <table class="tbl_vswitch_info">
       <tr><td class="tbl_info_header" colspan="5">Virtual Switch informations</td></tr>
       <tr><th>vSwitch Name</th>
       <th>Hyper-V host</th>
       <th>Health status</th>
       <th>Status</th>
       <th>Enabled state</th></tr>


<?php
  $result_vs=mysqli_query($con,$sql_vs);
  while ($row = $result_vs->fetch_assoc()) {
    $vswitch_name=$row["vswitch_name"];
    $health_status=$row["health_state"];
    $host=$row["hostname"];
    $vswitch_id=$row["vswitch_id"];
    $enabled_state=$row["enabled_state"];
    $status_descriptions=$row["status_descriptions"];
?>

	<tr><td><?php print $vswitch_name ?></td><td><?php print $host?></td><td><?php print $health_status?></td><td><?php print $status_descriptions?></td><td><?php print $enabled_state ?></td></tr>
<?php
  } 
?>
      </table>
      <br/>
    </span>
    <br/>
    <br/>

    <span class="spn_100">
      <table class="tbl_vswitch_visual" cellspacing="0" cellpadding="0">
       <tr>
         <td class="tbl_vswitch_visual_filler">&nbsp;</td>
         <td class="tbl_vswitch_visual_icon_filler">&nbsp;</td>
       </tr>

<?php


  $sql_switch_ports="select hyperv_vm_network_devices.timestamp, hyperv_vm_network_devices.port_id, hyperv_vm_network_devices.macaddress, hyperv_vm_network_devices.vswitch_id,hyperv_vswitch_informations.vswitch_name, hyperv_virtual_machines.vm_name, hyperv_virtual_machines.vm_id  from hyperv_vm_network_devices join hyperv_vswitch_informations on hyperv_vm_network_devices.timestamp=hyperv_vswitch_informations.timestamp and  hyperv_vm_network_devices.hostname = hyperv_vswitch_informations.hostname and hyperv_vm_network_devices.vswitch_id = hyperv_vswitch_informations.vswitch_id join hyperv_virtual_machines on hyperv_virtual_machines.timestamp=hyperv_vm_network_devices.timestamp and hyperv_vm_network_devices.hostname=hyperv_virtual_machines.hostname and hyperv_virtual_machines.vm_id=hyperv_vm_network_devices.vmid  where hyperv_vswitch_informations.timestamp = (select max(timestamp) from hyperv_vswitch_informations) and  hyperv_vswitch_informations.vswitch_name='$vs_name' and hyperv_vswitch_informations.hostname='$host';";

  $result_vs_p=mysqli_query($con,$sql_switch_ports);
  while ($row = $result_vs_p->fetch_assoc()) {
    $vmid=$row["vm_id"];
    $vm_name=$row["vm_name"];
    $port_id=$row["port_id"];
    $macaddress=$row["macaddress"];
?>
       <tr>
         <td class="tbl_vswitch_visual_portgroup">

           <td class="tbl_vswitch_visual_icon_connected">&nbsp;</td>
           <td class="tbl_vswitch_visual_vm"><b>Virtual machine:</b><br>

           &nbsp;&nbsp;Id: <?php print $vmid ?> <br/>
           &nbsp;&nbsp;Name:
	   <span onclick="updateContentPaneVmInfo('<?php print $host ?>','hyperv','<?php print $vmid?>')" class="tbl_vswitch_visual_vm_link"><?php print  $vm_name ?></span><br/>

           &nbsp;&nbsp;MAC: <?php print $macaddress ?> <br/>

<?php
?>

         </td>
       </tr>
       <tr>
         <td class="tbl_vswitch_visual_filler">&nbsp;</td>
         <td class="tbl_vswitch_visual_icon_filler">&nbsp;</td>
       </tr>
<?php

  }

?>
       <tr>
         <td class="tbl_vswitch_visual_filler">&nbsp;</td>
         <td class="tbl_vswitch_visual_icon_filler">&nbsp;</td>
       </tr>

      </table>
   </span>


<?php
}
?>
