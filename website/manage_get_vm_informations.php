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


if ( "get_all_vms_info"==$_GET["action"]  ) {

  $sql_esxi_vms="select * from virtual_machines where timestamp=(select max(timestamp) from virtual_machines) order by hostname, vmid; ";
  $sql_hyperv_vms="select * from hyperv_virtual_machines where timestamp=(select max(timestamp) from  hyperv_virtual_machines)  order by hostname, vm_name; ";

  $result_esxi_vms=mysqli_query($con,$sql_esxi_vms);
  $result_hyperv_vms=mysqli_query($con,$sql_hyperv_vms);
?>
    <span class="spn_100">
      <table class="tbl_vms_info">
       <tr>
          <th class="tbl_info_header">VMid</th>
          <th class="tbl_info_header">Name</th>
          <th class="tbl_info_header">ESXi host</th>
          <th class="tbl_info_header">Datastore</th>
          <th class="tbl_info_header">VMX path</th>
          <th class="tbl_info_header">VM HW version</th>
          <th class="tbl_info_header">Power state</th>
          <th class="tbl_info_header">Status</th>
          <th class="tbl_info_header">num.Cpu</th>
          <th class="tbl_info_header">RAM (Mb)</th>
          <th class="tbl_info_header">Guest fullname</th>
          <th class="tbl_info_header">Last boot time</th>
       </tr>

<?php
  while ($row = $result_esxi_vms->fetch_assoc()) {
    $vm_id=$row["vmid"];
    $host=$row["hostname"];
    $name=$row["name"];
    $last_seen_ts=$row["timestamp"];
    $cfg_numCpu=$row["config_numCpu"];
    $cfg_memoryMb=$row["config_memorySizeMB"];
    $version=$row["version"];
    $datastore=$row["datastore"];
    $overall_status=$row["overall_status"];
    $guest_guestfullname=$row["guest_guestfullname"];
    $runtime_lastboottime=$row["runtime_lastboottime"];
    $runtime_powerstate=$row["runtime_powerstate"];
    $timestamp=$row["timestamp"];
    $path=$row["path"];
?>
       <tr>
	 <td><?php print $vm_id; ?></td>
	 <td><?php print $name; ?></td>
	 <td><?php print $host; ?></td>
	 <td><?php print $datastore; ?></td>
	 <td><?php print $path; ?></td>
	 <td><?php print $version; ?></td>
	 <td><?php print $runtime_powerstate; ?></td>
	 <td><?php print $overall_status; ?></td>
	 <td><?php print $cfg_numCpu; ?></td>
	 <td><?php print $cfg_memoryMb; ?></td>
	 <td><?php print $guest_guestfullname; ?></td>
	 <td><?php print $runtime_lastboottime; ?></td>
       </tr>
<?php
  }
?>
    </table>
<br/>
<br/>

    <span class="spn_100">
      <table class="tbl_vms_info">
       <tr>
          <th class="tbl_info_header">VMid</th>
          <th class="tbl_info_header">Name</th>
          <th class="tbl_info_header">Hyper-V host</th>
          <th class="tbl_info_header">Status</th>
          <th class="tbl_info_header">Status descriptions</th>
          <th class="tbl_info_header">Uptime</th>
          <th class="tbl_info_header">Health</th>
       </tr>

<?php
  while ($row = $result_hyperv_vms->fetch_assoc()) {
    $vm_id=$row["vm_id"];
    $host=$row["hostname"];
    $health_state=$row["health_state"];
    $name=$row["vm_name"];
    $last_seen_ts=$row["timestamp"];
    $uptime_millisec=$row["uptime_millisec"];
    $status_descriptions=$row["status_descriptions"];
    $status=$row["status"];
    $enabled_state=$row["enabled_state"];
?>
       <tr>
         <td><?php print $vm_id; ?></td>
         <td><?php print $name; ?></td>
	 <td><?php print $host; ?></td>
	 <td>
          <?php
            if ($enabled_state == 3){
              print "3 = Powered off";
            }else if ($enabled_state ==2) {
              print "2 = Powered on";
            }else{
              print $enabled_state;
            }

          ?>

         </td>
         <td><?php print $status; ?></td>
         <td><?php print $status_descriptions; ?></td>
         <td><?php print $health_state; ?></td>
       </tr>
<?php
  }
?>

    </table>

  </span>
<?php
}
?>
