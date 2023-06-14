<?php
include "check_session.php";
require_once "../common/db.php";
require_once "../conf/db.php";
# gather info from esxi hosts
$con=getConnection($servername,$username,$password,$dbname);


?>
<html>
  <head>
    <title>gCenter</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
    <script>
      function updateContentPaneHostInfo(hostname, host_type){
        $.get( "manage_get_"+host_type+"_host_informations.php?hostname=" + hostname + "&action=get_host_info", function( data ) {
        $( "#main_content_pane" ).html( data );
        //alert( data );
       });
      }

      function updateContentPaneAllHosts(){
        $("#main_content_pane_message").html("");
        $.get( "manage_get_host_informations.php?action=get_all_hosts_info", function( data ) {
		$( "#main_content_pane" ).html( data );
	});
      }

      function updateContentPaneAllVirtualMachines(){
        $("#main_content_pane_message").html("");
        $.get( "manage_get_vm_informations.php?action=get_all_vms_info", function( data ) {
          $( "#main_content_pane" ).html( data );
        });
      }

      function updateContentPaneAllDatastores(){
        $("#main_content_pane_message").html("");
        $.get( "manage_get_ds_informations.php?action=get_all_ds_info", function( data ) {
        $( "#main_content_pane" ).html( data );
        });
      }

      function updateContentPaneVmInfo(host,type,vmid){
        $("#main_content_pane_message").html("");
        $.get( "manage_get_"+type+"_vm_informations.php?hostname=" + host + "&vmid=" + vmid + "&action=get_vm_info", function( data ) {
        $( "#main_content_pane" ).html( data );
        //alert( data );
       });
      }	

      function updateContentPaneDsInfo(host,type,vmid){
        $("#main_content_pane_message").html("");
        $.get( "manage_get_"+type+"_ds_informations.php?hostname=" + host +  "&ds=" + vmid + "&action=get_ds_info", function( data ) {
        $( "#main_content_pane" ).html( data );
        //alert( data );
       });
      }


      function updateContentPaneVswitchInfo(host,type,vmid){
        $("#main_content_pane_message").html("");
        $.get( "manage_get_"+type+"_vswitch_informations.php?hostname=" + host +  "&vswitch=" + vmid + "&action=get_vswitch_info", function( data ) {
        $( "#main_content_pane" ).html( data );
        //alert( data );
       });
      }
      


      function poweronVm(host,vmid){
	$.get( "manage_vm.php?hostname=" + host + "&vmid=" + vmid + "&action=power_on", function( data ) {
          $( "#main_content_pane_message" ).html( data );
        //alert( data );
       });
      }      


      function rebootVm(host,vmid){
        $.get( "manage_vm.php?hostname=" + host + "&vmid=" + vmid + "&action=reboot", function( data ) {
          $( "#main_content_pane_message" ).html( data );
        //alert( data );
       });
      }


      function poweroffVm(host,vmid){
        $.get( "manage_vm.php?hostname=" + host + "&vmid=" + vmid + "&action=power_off", function( data ) {
          $( "#main_content_pane_message" ).html( data );
        //alert( data );
       });
      }

      function snapVm(host,vmid){
        $.get( "manage_vm.php?hostname=" + host + "&vmid=" + vmid + "&action=take_snap", function( data ) {
          $( "#main_content_pane_message" ).html( data );
        //alert( data );
       });
      }

      //Hyper-V
      function poweronHypervVm(host,vmid){
        $.get( "manage_hyperv_vm.php?hostname=" + host + "&vmid=" + vmid + "&action=power_on", function( data ) {
          $( "#main_content_pane_message" ).html( data );
        //alert( data );
       });
      }


      function rebootHypervVm(host,vmid){
        $.get( "manage_hyperv_vm.php?hostname=" + host + "&vmid=" + vmid + "&action=reboot", function( data ) {
          $( "#main_content_pane_message" ).html( data );
        //alert( data );
       });
      }

      function resetHypervVm(host,vmid){
        $.get( "manage_hyperv_vm.php?hostname=" + host + "&vmid=" + vmid + "&action=reset", function( data ) {
          $( "#main_content_pane_message" ).html( data );
        //alert( data );
       });
      }

      function poweroffHypervVm(host,vmid){
        $.get( "manage_hyperv_vm.php?hostname=" + host + "&vmid=" + vmid + "&action=power_off", function( data ) {
          $( "#main_content_pane_message" ).html( data );
        //alert( data );
       });
      }


      function snapHypervVm(host,vmid){
        $.get( "manage_hyperv_vm.php?hostname=" + host + "&vmid=" + vmid + "&action=take_snap", function( data ) {
          $( "#main_content_pane_message" ).html( data );
        //alert( data );
       });
      }

     


    </script>
  </head>
  <body>
  <?php include "header.php" ?>

  <div class="inner_body">


  <div class="li_navigation">
    <b>Summary</b>
    <div class="li_summary">
      <span class="sp_nav_summary" onclick="updateContentPaneAllHosts()">Hosts</span><br/>
      <span class="sp_nav_summary" onclick="updateContentPaneAllVirtualMachines()">Virtual Machines</span><br/>
    <!--  <span class="sp_nav_summary" onclick="updateContentPaneAllDatastores()">Datastores</span><br/> -->
   </div>
<!--  </div>

  <div class="li_navigation"> -->
  <b>ESXi hosts</b>
<?php
  # recupero gli host 
  $hosts_sql="select hostname from hosts;";
  $hosts_result=mysqli_query($con,$hosts_sql);
  while ($row = $hosts_result->fetch_assoc()) {
	  $host=$row["hostname"];
?>
  <div class="li_host">
    <span class="sp_nav_host" onclick="updateContentPaneHostInfo('<?php print $host ?>','esxi')"><?php print $host; ?></span><br/>
    <b>Virtual machines</b>

<?php
  # recupero le vm
  $vm_sql="select name, vmid, hostname,guest_os from virtual_machines where timestamp=(select max(timestamp) from virtual_machines where hostname='$host') and hostname='$host';";
  $vm_result=mysqli_query($con,$vm_sql);
  while ($row = $vm_result->fetch_assoc()) {
	  $vm=$row["name"];
	  $host=$row["hostname"];
	  $guestos=$row["guest_os"];
	  $vmid=trim($row["vmid"]);
?>
    <div class="li_vm">
      <span class="sp_nav_vm"  onclick="updateContentPaneVmInfo('<?php print $host ?>','esxi','<?php print $vmid ?>')"><?php print $vm; ?></span><br/>
    </div><!--vm-->
<?php
  }
?>
    <b>Datastores</b>

<?php
  # recupero i datastore
  $ds_sql="select name, capacity, freespace from datastores where timestamp=(select max(timestamp) from datastores where hostname='$host') and hostname='$host';";
  $ds_result=mysqli_query($con,$ds_sql);
  while ($row = $ds_result->fetch_assoc()) {
          $name=$row["name"];
          $total_capacity=$row["capacity"];
          $free_space=$row["freespace"];
?>
    <div class="li_ds">
      <span class="sp_nav_ds" onclick="updateContentPaneDsInfo('<?php print $host ?>','esxi','<?php print $name ?>')" ><?php print $name ?> [<?php print ceil((($total_capacity-$free_space)/$total_capacity)*100)."%"  ?>]</span><br/>
    </div><!--ds-->
<?php
  }
?>


    <b>Network</b>

<?php
  # recupero i vswitch
  $vs_sql="select vswitch_name from vswitch_informations where timestamp=(select max(timestamp) from vswitch_informations where hostname='$host') and hostname='$host' group by vswitch_name;";
  $vs_result=mysqli_query($con,$vs_sql);
  while ($row = $vs_result->fetch_assoc()) {
          $name=$row["vswitch_name"];
?>
    <div class="li_vswitch">
      <span class="sp_nav_vswitch" onclick="updateContentPaneVswitchInfo('<?php print $host ?>','esxi','<?php print $name ?>')" ><?php print $name ?></span><br/>
    </div><!--vs-->
<?php
  }
?>





  </div> <!-- li host -->
<?php
  }
?>

<br/>
<br/>

  <b>Hyper-V hosts</b>
<?php
  # recupero gli host
  $hosts_sql="select hostname from hyperv_hosts;";
  $hosts_result=mysqli_query($con,$hosts_sql);
  while ($row = $hosts_result->fetch_assoc()) {
          $host=$row["hostname"];
?>
  <div class="li_host">
    <span class="sp_nav_host" onclick="updateContentPaneHostInfo('<?php print $host ?>','hyperv')"><?php print $host; ?></span><br/>
    <b>Virtual machines</b>

<?php
  # recupero le vm
  $vm_sql="select vm_name, vm_id, hostname from hyperv_virtual_machines where timestamp=(select max(timestamp) from hyperv_virtual_machines where hostname='$host') and hostname='$host';";
  $vm_result=mysqli_query($con,$vm_sql);
  while ($row = $vm_result->fetch_assoc()) {
          $vm=$row["vm_name"];
          $host=$row["hostname"];
          $vmid=trim($row["vm_id"]);
?>
    <div class="li_vm">
      <span class="sp_nav_vm"  onclick="updateContentPaneVmInfo('<?php print $host ?>','hyperv','<?php print $vmid ?>')"><?php print $vm; ?></span><br/>
    </div><!--vm-->
<?php
  }
?>
  </div> <!-- li host -->
<?php
  }
?>






  </div><!--navigation-->

  <div id="main_container">
    <div id="main_content_pane_message">
    </div>
    <div id="main_content_pane">
    </div>

  </div>

  </div> <!-- inner_body -->
<!--
  <?php include "footer.php" ?>
-->
  </body>
</html>
