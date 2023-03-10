<?php
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
    <script>
      function updateContentPaneHostInfo(hostname){
        $.get( "manage_get_host_informations.php?hostname=" + hostname + "&action=get_host_info", function( data ) {
        $( "#main_content_pane" ).html( data );
        //alert( data );
       });
      }
     
      function updateContentPaneVmInfo(host,vmid){
        $("#main_content_pane_message").html("");
        $.get( "manage_get_vm_informations.php?hostname=" + host + "&vmid=" + vmid + "&action=get_vm_info", function( data ) {
        $( "#main_content_pane" ).html( data );
        //alert( data );
       });
      }	

      function updateContentPaneDsInfo(host,vmid){
        $("#main_content_pane_message").html("");
        $.get( "manage_get_ds_informations.php?hostname=" + host + "&ds=" + vmid + "&action=get_ds_info", function( data ) {
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

    </script>
  </head>
  <body>
  <?php include "header.php" ?>


  <div class="inner_body">
  <div class="li_navigation">
  <b>Hosts</b>
<?php
  # recupero gli host 
  $hosts_sql="select hostname from hosts;";
  $hosts_result=mysqli_query($con,$hosts_sql);
  while ($row = $hosts_result->fetch_assoc()) {
	  $host=$row["hostname"];
?>
  <div class="li_host">
    <span class="sp_nav_host" onclick="updateContentPaneHostInfo('<?php print $host ?>')"><?php print $host; ?></span><br/>
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
      <span class="sp_nav_vm"  onclick="updateContentPaneVmInfo('<?php print $host ?>','<?php print $vmid ?>')"><?php print $vm; ?></span><br/>
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
      <span class="sp_nav_ds" onclick="updateContentPaneDsInfo('<?php print $host ?>','<?php print $name ?>')" ><?php print $name ?> [<?php print ceil((($total_capacity-$free_space)/$total_capacity)*100)."%"  ?>]</span><br/>
    </div><!--vm-->
<?php
  }
?>



  </div> <!-- host -->
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
