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

  logEventInfo($con,"/$host/vswitch/$vs_name","display esxi vswitch details");

  $sql_vs="select *  from vswitch_informations where vswitch_name='$vs_name' and  hostname='$host' and timestamp=(select max(timestamp) from vswitch_informations where hostname='$host'); ";

?>

    <h2><?php print $host ?> / <?php print $vs_name ?></h2>

    <span class="spn_100">
      <table class="tbl_vswitch_info">
       <tr><td class="tbl_info_header" colspan="5">Virtual Switch informations</td></tr>
       <tr><th>vSwitch Name</th>
       <th>ESXi host</th>
       <th>Portgroup Name</th>
       <th>VLAN ID</th>
       <th>Active clients</th></tr>


<?php
  $result_vs=mysqli_query($con,$sql_vs);
  while ($row = $result_vs->fetch_assoc()) {
    $vswitch_name=$row["vswitch_name"];
    $portgroup=$row["portgroup_name"];
    $vlan_id=$row["vlan_id"];
    $host=$row["hostname"];
    $active_clients=$row["active_clients"];
?>

	<tr><td><?php print $vswitch_name ?></td><td><?php print $host?></td><td><?php print $portgroup?></td><td><?php print $vlan_id?></td><td><?php print $active_clients ?></td></tr>
<?php
  } 
?>
      </table>
      <br/>
    </span>

    <span class="spn_100">
      <table class="tbl_vswitch_visual" cellspacing="0" cellpadding="0">
       <tr>
         <td class="tbl_vswitch_visual_filler">&nbsp;</td>
         <td class="tbl_vswitch_visual_icon_filler">&nbsp;</td>
       </tr>

<?php

  $sql_switch_portgroup="select portgroup_name, vlan_id from vswitch_informations where timestamp = (select max(timestamp) from vswitch_informations  ) and vswitch_name = '".$vs_name."' and  hostname='".$host."'  ;" ;
  $result_vs_pg=mysqli_query($con,$sql_switch_portgroup);
  while ($row = $result_vs_pg->fetch_assoc()) {
    $portgroup=$row["portgroup_name"];
    $vlan_id=$row["vlan_id"];

?>
       <tr>
	 <td class="tbl_vswitch_visual_portgroup">
           <b>Portgroup:</b><br><?php print $portgroup ?><br><br>
           <b>Vlan Id:</b><?php  print $vlan_id ?></td>

<?php
         $sql_switch_pg_vm="select vswitch_name, portgroup_name, vlan_id, virtual_machines.vmid, name from vswitch_informations join vm_network_devices on vswitch_informations.timestamp = (select max(vswitch_informations.timestamp) from vswitch_informations  ) and vswitch_informations.hostname='".$host."' and vswitch_informations.vswitch_name = '".$vs_name."' and vm_network_devices.backing_portgroup='".$portgroup."' and vswitch_informations.timestamp=vm_network_devices.timestamp and vswitch_informations.hostname = vm_network_devices.hostname and vswitch_informations.portgroup_name=vm_network_devices.backing_portgroup join virtual_machines on virtual_machines.timestamp= vswitch_informations.timestamp and vswitch_informations.hostname = virtual_machines.hostname and vm_network_devices.vmid = virtual_machines.vmid;";

         $result_vs_pg_vm=mysqli_query($con,$sql_switch_pg_vm);
   
	 // if there are VMs use connected style
	 $num_vm = mysqli_num_rows($result_vs_pg_vm);
	 if ($num_vm > 0){
?>
           <td class="tbl_vswitch_visual_icon_connected">&nbsp;</td>
           <td class="tbl_vswitch_visual_vm"><b>Virtual machines:</b><br>

<?php	 }else{  ?>
           <td class="tbl_vswitch_visual_icon">&nbsp;</td>
           <td >
<?php
	 }


         while ($row = $result_vs_pg_vm->fetch_assoc()) {
           $portgroup=$row["portgroup_name"];
	   $vlan_id=$row["vlan_id"];
	   $vm_name=$row["name"];
	   $vmid=$row["vmid"];
?>
	   &nbsp;&nbsp;Id: <?php print $vmid ?>
           &nbsp;&nbsp;Name: 
           <span onclick="updateContentPaneVmInfo('<?php print $host ?>','esxi','<?php print $vmid?>')" class="tbl_vswitch_visual_vm_link"><?php print  $vm_name ?></span><br/> 
<?php
         }
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
