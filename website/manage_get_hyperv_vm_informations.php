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
    $num_cpu=$row["num_cpu"];
    $last_seen_ts=$row["timestamp"];
    $timestamp=$row["timestamp"];
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
        <tr><th>Number CPU</th><td><?php print $num_cpu ?></td></tr>
      </table>

    </span>

    <br/>
    <br/>
    <span class="spn_100">
      <table width="100%" class="tbl_vm_snapshots">
	<tr><td class="tbl_info_header" colspan="6">Snapshots <span class="btn_command" style="float:right" onclick="snapHypervVm('<?php print $host ?>','<?php print $vm_name ?>')">[ Take ]</span> </td></tr>

        <tr><th>Name</th><th>Created On</th><th>SnapshotID</th><th>Parent SnapshotID</th></tr>
<?php
      $sql_snap="select * from hyperv_vm_snapshots where timestamp='".mysqli_real_escape_string($con,$timestamp)."' and vmid='".mysqli_real_escape_string($con,$vm_name)."' and hostname='".mysqli_real_escape_string($con,$host)."' order by creation_date, creation_time, parent_snap; ";

      $result_snap=mysqli_query($con,$sql_snap);
      while ($row = $result_snap->fetch_assoc()) {
        $name=$row["name"];
        $snap_id=$row["snap_id"];
        $create_time=$row["creation_time"];
        $create_date=$row["creation_date"];
        $parent=$row["parent_snap"];

?>
       <tr>
	 <td width="80%"><?php print $name; ?></td>
         <td width="80%"><?php print $create_date." ".$create_time; ?></td>

         <td width="10px"><?php print $snap_id; ?></td>
         <td><?php print $parent; ?></td>
       </tr>
<?php } ?>



      </table>
    </span>



    <br/>
    <br/>
    <span class="spn_100" style="padding-top:20px;padding-bottom:10px;">
      <script>
        function showGraphCpuUsage(){
         $( "#graph_cpu_usage" ).show();
         $( "#graph_memory_usage" ).hide();
        }

        function showGraphMemoryUsage(){
         $( "#graph_cpu_usage" ).hide();
         $( "#graph_memory_usage" ).show();
        }

      </script>
      Graph :
      <span class="btn_command" onclick="showGraphCpuUsage()">[CPU Usage]</span>
      <span class="btn_command" onclick="showGraphMemoryUsage()">[Memory Usage]</span>
    </span>

    <span class="spn_100" id="graph_cpu_usage">
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
      $sql_cpu="select timestamp, cpu_load from hyperv_vm_stat where hostname='".mysqli_real_escape_string($con,$host)."' and vmid='".mysqli_real_escape_string($con,$vm_id)."' order by timestamp desc limit 180;";

      $result_cpu=mysqli_query($con,$sql_cpu);
      $a_js_xValues="";
      $a_js_yValues="";
      while ($row = $result_cpu->fetch_assoc()) {
        $timestamp=$row["timestamp"];
        $cpu_load=$row["cpu_load"];

        $a_js_xValues="\"".$timestamp."\",".$a_js_xValues;
        $a_js_yValues=$cpu_load.",".$a_js_yValues;
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
                label : "cpu load",
                backgroundColor: "rgba(0,0,0,1.0)",
                borderColor: "rgb(75, 192, 192)",
                fill:false,
                data: yValues
              }]
            },
            options:{}
          });
        </script>

    <span class="spn_100" id="graph_memory_usage" style="display:none">
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
      $sql_mem="select timestamp, memory_usage, available_memory_buffer, memory_available, memory_virtualquantity,memory_limit,memory_reservation   from hyperv_vm_stat where hostname='".mysqli_real_escape_string($con,$host)."' and vmid='".mysqli_real_escape_string($con,$vm_id)."' order by timestamp desc limit 180;";

      $result_cpu=mysqli_query($con,$sql_mem);
      $a_js_memoryavailable_yValues="";
      $a_js_memoryusage_yValues="";
      $a_js_availablememorybuffer_yValues="";
      $a_js_memoryvirtualquantity_yValues="";
      $a_js_memorylimit_yValues="";
      $a_js_memoryreservation_yValues="";
      $a_js_xValues="";
      while ($row = $result_cpu->fetch_assoc()) {
        $memory_available=$row["memory_available"];
        $available_memory_buffer=$row["available_memory_buffer"];
        $memory_usage=$row["memory_usage"];
        $memory_virtualquantity=$row["memory_virtualquantity"];
        $memory_limit=$row["memory_limit"];
        $memory_reservation=$row["memory_reservation"];

        $a_js_xValues="\"".$timestamp."\",".$a_js_xValues;

        $a_js_memoryavailable_yValues=$memory_available.",".$a_js_memoryavailable_yValues;
        $a_js_memoryusage_yValues=$memory_usage.",".$a_js_memoryusage_yValues;
        $a_js_availablememorybuffer_yValues=$available_memory_buffer.",".$a_js_availablememorybuffer_yValues;
        $a_js_memoryvirtualquantity_yValues=$memory_virtualquantity.",".$a_js_memoryvirtualquantity_yValues;
        $a_js_memorylimit_yValues=$memory_limit.",".$a_js_memorylimit_yValues;
        $a_js_memoryreservation_yValues=$memory_reservation.",".$a_js_memoryreservation_yValues;

      }

?>
        <script>
          var xValues = [ <?php print substr($a_js_xValues,0,strlen($a_js_xValues)-1) ?> ];
          var y_memoryavailable_Values = [ <?php print substr($a_js_memoryavailable_yValues,0,strlen($a_js_memoryavailable_yValues)-1) ?> ];
          var y_memoryusage_Values = [ <?php print substr($a_js_memoryusage_yValues,0,strlen($a_js_memoryusage_yValues)-1) ?> ];
          var y_availablememorybuffer_Values = [ <?php print substr($a_js_availablememorybuffer_yValues,0,strlen($a_js_availablememorybuffer_yValues)-1) ?> ];
          var y_memoryvirtualquantity_Values = [ <?php print substr($a_js_memoryvirtualquantity_yValues,0,strlen($a_js_memoryvirtualquantity_yValues)-1) ?> ];
          var y_memorylimit_Values = [ <?php print substr($a_js_memorylimit_yValues,0,strlen($a_js_memorylimit_yValues)-1) ?> ];
          var y_memoryreservation_Values = [ <?php print substr($a_js_memoryreservation_yValues,0,strlen($a_js_memoryreservation_yValues)-1) ?> ];

          new Chart("vm_mem", {
            type: "line",
            data: {
              labels: xValues,
              datasets: [ /* {
                label : "available memory",
                backgroundColor: "rgba(0,0,0,1.0)",
                borderColor: "rgb(75, 192, 192)",
                fill:false,
                data: y_memoryavailable_Values 
	  }, */
               {
                label : "memory usage",
                backgroundColor: "rgba(0,0,0,1.0)",
                borderColor: "rgb(20, 90, 172)",
                fill:false,
                data: y_memoryusage_Values 
               },
/*               {
                label : "avail memory buffer",
                backgroundColor: "rgba(0,0,0,1.0)",
                borderColor: "rgb(192, 70, 70)",
                fill:false,
                data: y_availablememorybuffer_Values 
	  },*/
               {
                label : "RAM",
                backgroundColor: "rgba(0,0,0,1.0)",
                borderColor: "rgb(150, 60, 60)",
                fill:false,
                data: y_memoryvirtualquantity_Values 
               },
               {
                label : "max dynamic memory",
                backgroundColor: "rgba(0,0,0,1.0)",
                borderColor: "rgb(122, 20, 50)",
                fill:false,
                data: y_memorylimit_Values
	  }, 
               {
                label : "min dynamic memory",
                backgroundColor: "rgba(0,0,0,1.0)",
                borderColor: "rgb(80, 80, 30)",
                fill:false,
                data: y_memoryreservation_Values
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
