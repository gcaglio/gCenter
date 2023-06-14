<?php
require_once "../common/db.php";
require_once "../conf/db.php";
# return vswitch informations

session_start();
if ( ! (isset($_SESSION["_CURRENT_USER"]) ) ){
  $_GET["message"]="Session not valid. Please login.";
  header('Location: ./index.php');
  exit;
}



$con=getConnection($servername,$username,$password,$dbname);


if (  isset($_GET["hostname"]) && isset($_GET["vswitch"]) && "get_vswitch_info"==$_GET["action"]  ) {
  $host=mysqli_real_escape_string($con,$_GET["hostname"]);
  $vs_name=mysqli_real_escape_string($con,$_GET["vswitch"]);

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


<?php
}
?>
