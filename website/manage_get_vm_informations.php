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
  $host=$_GET["hostname"];
  $vmid=$_GET["vmid"];

  $sql_vm="select * from virtual_machines where vmid='$vmid' and hostname='$host' order by timestamp DESC limit 1 ; ";

  $result_vm=mysqli_query($con,$sql_vm);
  while ($row = $result_vm->fetch_assoc()) {
    $vm_id=$row["vmid"];
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
<!--
    <table class="tbl_vm_command">
      <tr>
	<td>Snapshot: <span onclick="snapVm('<?php print $host ?>','<?php print $vmid ?>')">Take</span> 
        </td>
      </tr>
    </table>
-->
    <span class="spn_50">
      <table class="tbl_vm_info">
       <tr><td class="tbl_info_header" colspan="2">VM info</td></tr>
       <tr><th>VMid</th><td><?php print $vmid ?></td></tr>
       <tr><th>ESXi host</th><td><?php print $host ?></td></tr>
       <tr><th>Datastore</th><td><?php print $datastore ?></td></tr>
       <tr><th>VMX path</th><td><?php print $path ?></td></tr>
       <tr><th>VM HW version</th><td><?php print $version ?></td></tr>
       <tr><th>Last seen</th><td><?php print $last_seen_ts ?></td></tr>
      </table>
      <br/>
    </span>

    <span class="spn_50">
      <table class="tbl_vm_info">
        <tr><td class="tbl_info_header" colspan="2">State</td></tr>
	<tr>
          <th>Power state</th>
	  <td><?php print $runtime_powerstate ?>

	  <?php  if ( $runtime_powerstate == "poweredOff"  ){ ?>
            <span class="btn_command" style="float:right" onclick="poweronVm('<?php print $host ?>','<?php print $vmid ?>')">[Power On]</span>
          <?php }else{ ?>
	    <span class="btn_command" style="float:right" onclick="rebootVm('<?php print $host ?>','<?php print $vmid ?>')">[Reboot]</span> 
	    <span class="btn_command" style="float:right" onclick="poweroffVm('<?php print $host ?>','<?php print $vmid ?>')">[Power Off]</span> 
          <?php  } ?>
  	  </td>
        </tr>
        <tr><th>Overall status</th><td><?php print $overall_status ?></td></tr>
      </table>
      <br/>
      <table class="tbl_vm_info">
        <tr><td class="tbl_info_header" colspan="2">HW summary</td></tr>
        <tr><th>CPU</th><td><?php print $cfg_numCpu ?></td></tr>
        <tr><th>Memory (Mb)</th><td><?php print $cfg_memoryMb ?></td></tr>
        <tr><th>Guest OS full name</th><td><?php print $guest_guestfullname ?></td></tr>
        <tr><th>Last boot time</th><td><?php print $runtime_lastboottime ?></td></tr>
      </table>
    </span>


    <br/>
    <br/> 
    <span class="spn_100">
      <table width="100%" class="tbl_vm_snapshots">
	<tr><td class="tbl_info_header" colspan="6">Snapshots <span class="btn_command" style="float:right" onclick="snapVm('<?php print $host ?>','<?php print $vmid ?>')">[ Take ]</span> </td></tr>
        <tr><th>SnapshotID</th><th>Parent SnapshotID</th><th>Name</th><th>Description</th><th>Created On</th><th>Filesystem Quiesced</th></tr>
<?php
      $sql_snap="select * from vm_snapshots where timestamp='$timestamp' and vmid='$vmid' and hostname='$host' order by parent_snap, snapshot ; ";

      $result_snap=mysqli_query($con,$sql_snap);
      while ($row = $result_snap->fetch_assoc()) {
        $name=$row["name"];
        $description=$row["description"];
        $create_time=$row["create_time"];
        $quiesced=$row["quiesced"];
        $snapshot=$row["snapshot"];
        $parent=$row["parent_snap"];

?>
       <tr>
         <td width="10px"><?php print $snapshot; ?></td>
         <td><?php print $parent; ?></td>
         <td width="80%"><?php print $name; ?></td>
         <td width="80%"><?php print $description; ?></td>
         <td width="80%"><?php print $create_time; ?></td>
         <td width="10px"><?php print $quiesced; ?></td>
       </tr>
<?php } ?>
      </table>

    </span>


    <br/>
    <br/>
    <span class="spn_100">
      <table width="100%" class="tbl_vm_graphs">
	<tr><td class="tbl_info_header" >Overall CPU Usage</td></tr>
	<tr>
	  <td align="center">
            <canvas id="vm_cpu" style="max-width:90%;max-height:350px"></canvas>
          </td>
	</tr>
      </table>
    </span>
<?php
      $sql_cpu="select timestamp,overallCpuUsage from vm_quickstat where hostname='$host' and vmid='$vmid' order by timestamp desc limit 180;";

      $result_cpu=mysqli_query($con,$sql_cpu);
      $a_js_xValues="";
      $a_js_yValues="";
      while ($row = $result_cpu->fetch_assoc()) {
        $timestamp=$row["timestamp"];
	$overallCpuUsage=$row["overallCpuUsage"];

	$a_js_xValues="\"".$timestamp."\",".$a_js_xValues;
	$a_js_yValues=$overallCpuUsage.",".$a_js_yValues;
      }
      
?>
	<script>
          var xValues = [ <?php print substr($a_js_xValues,0,strlen($a_js_xValues)-1) ?> ];
	  var yValues = [ <?php print substr($a_js_yValues,0,strlen($a_js_yValues)-1) ?> ];

	  new Chart("vm_cpu", {
	    type: "line",
	    data: {
	      labels: xValues,
	      datasets: [{
                label : "overallCpuUsage",
	        backgroundColor: "rgba(0,0,0,1.0)",
		borderColor: "rgb(75, 192, 192)",
		fill:false,
	        data: yValues
	      }]
	    },
	    options:{}
	  });
        </script>


    <br/>
    <br/>
    <br/>
    <span class="spn_100">
      <table width="100%" class="tbl_vm_graphs">
        <tr><td class="tbl_info_header" >Memory Usage</td></tr>
        <tr>
          <td align="center">
            <canvas id="vm_mem" style="max-width:90%;max-height:350px"></canvas>
          </td>
        </tr>
      </table>
    </span>
<?php
      $sql_cpu="select timestamp, guestMemoryUsage, hostMemoryUsage, balloonedMemory  from vm_quickstat where hostname='$host' and vmid='$vmid' order by timestamp desc limit 180;";

      $result_cpu=mysqli_query($con,$sql_cpu);
      $a_js_guest_yValues="";
      $a_js_host_yValues="";
      $a_js_baloon_yValues="";
      $a_js_xValues="";
      while ($row = $result_cpu->fetch_assoc()) {
        $timestamp=$row["timestamp"];
        $balloonedMemory=$row["balloonedMemory"];
        $hostMemoryUsage=$row["hostMemoryUsage"];
        $guestMemoryUsage=$row["guestMemoryUsage"];


        $a_js_xValues="\"".$timestamp."\",".$a_js_xValues;
        $a_js_guest_yValues=$guestMemoryUsage.",".$a_js_guest_yValues;
        $a_js_host_yValues=$hostMemoryUsage.",".$a_js_host_yValues;
        $a_js_baloon_yValues=$balloonedMemory.",".$a_js_baloon_yValues;
      }

?>
        <script>
          var xValues = [ <?php print substr($a_js_xValues,0,strlen($a_js_xValues)-1) ?> ];
          var y_guest_Values = [ <?php print substr($a_js_guest_yValues,0,strlen($a_js_guest_yValues)-1) ?> ];
          var y_host_Values = [ <?php print substr($a_js_host_yValues,0,strlen($a_js_host_yValues)-1) ?> ];
          var y_baloon_Values = [ <?php print substr($a_js_baloon_yValues,0,strlen($a_js_baloon_yValues)-1) ?> ];

          new Chart("vm_mem", {
            type: "line",
            data: {
              labels: xValues,
              datasets: [{
                label : "guestMemoryUsage",
                backgroundColor: "rgba(0,0,0,1.0)",
                borderColor: "rgb(75, 192, 192)",
                fill:false,
                data: y_guest_Values
	       },
               {
                label : "hostMemoryUsage",
                backgroundColor: "rgba(0,0,0,1.0)",
                borderColor: "rgb(70, 172, 172)",
                fill:false,
                data: y_host_Values
               },
               {
                label : "balloonedMemory",
                backgroundColor: "rgba(0,0,0,1.0)",
                borderColor: "rgb(65, 122, 122)",
                fill:false,
                data: y_baloon_Values
               }
	       ]
            },
            options:{}
          });
        </script>







<?php
  }
}
?>
