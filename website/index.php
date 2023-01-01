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
    </script>
  </head>
  <body>
  <?php include "header.php" ?>


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
    <span onclick="updateContentPaneHostInfo('<?php print $host ?>')"><?php print $host; ?></span><br/>
    <b>Virtual machines</b>

<?php
  # recupero le vm
  $vm_sql="select name, guest_os from virtual_machines where timestamp=(select max(timestamp) from virtual_machines where hostname='$host') and hostname='$host';";
  $vm_result=mysqli_query($con,$vm_sql);
  while ($row = $vm_result->fetch_assoc()) {
	  $vm=$row["name"];
	  $guestos=$row["guest_os"];
?>
    <div class="li_vm">
      <?php print $vm ?>
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
      <?php print $name ?> [<?php print ceil((($total_capacity-$free_space)/$total_capacity)*100)."%"  ?>]
    </div><!--vm-->
<?php
  }
?>



  </div> <!-- host -->
<?php
  }
?>

  </div><!--navigation-->

  <div id="main_content_pane">
  </div>

  <?php include "footer.php" ?>
  </body>
</html>
