<?php
require_once "../common/db.php";
require_once "../conf/db.php";
# return host informations

session_start();
if ( ! (isset($_SESSION["_CURRENT_USER"]) ) ){
  $_GET["message"]="Session not valid. Please login.";
  header('Location: ./index.php');
  exit;
}




$con=getConnection($servername,$username,$password,$dbname);


if (  isset($_GET["hostname"]) && "get_host_info"==$_GET["action"]  ) {
  $host=mysqli_real_escape_string($con,$_GET["hostname"]);
  $sql="select * from hosts_informations where timestamp=(select max(timestamp) from hosts_informations where hostname='$host') and hostname='$host';";
  $result=mysqli_query($con,$sql);
  while ($row = $result->fetch_assoc()) {
    $last_seen=$row["timestamp"];
    $hw_vendor=$row["hw_vendor"];
    $hw_model=$row["hw_model"];
    $memory_size=$row["memory_size"];
    $cpu_model=$row["cpu_model"];
    $cpu_mhz=$row["cpu_mhz"];
    $cpu_cores=$row["cpu_cores"];
    $cpu_threads=$row["cpu_threads"];
    $nic=$row["nics"];
    $hbas=$row["hbas"];
    $is_in_maintenance=$row["is_in_maintenance"];
    $boot_time=$row["boot_time"];
    $host_name=$row["host_name"];
    $product_name=$row["product_name"];
    $product_fullname=$row["product_fullname"];
    $product_version=$row["product_version"];
    $os_type=$row["os_type"];
?>
    <h2><?php print $host_name ?>  </h2>
    <span class="spn_50">
    <table class="tbl_host_info">
      <tr><td class="tbl_info_header" colspan="2">Host informations</td></tr>
      <tr><th>Last seen timestamp</th><td><?php print $last_seen ?></td></tr>
      <tr><th>Hostname</th><td><?php print $host_name ?></td></tr>
      <tr><th>Boot time</th><td><?php print $boot_time?></td></tr>
      <tr><th>Product name</th><td><?php print $product_name ?></td></tr>
      <tr><th>Product fullname</th><td><?php print $product_fullname ?></td></tr>
      <tr><th>Product version</th><td><?php print $product_version ?></td></tr>
      <tr><th>OS type</th><td><?php print $os_type ?></td></tr>
    </table>
    </span>
  
    <span class="spn_50">
    <table class="tbl_hw_info">
     <tr><td class="tbl_info_header" colspan="2">Host hardware informations</td></tr>
     <tr><th>CPU model</th><td><?php print $cpu_model ?></td></tr>
     <tr><th>CPU cores</th><td><?php print $cpu_cores ?></td></tr>
     <tr><th>CPU mhz</th><td><?php print $cpu_mhz ?></td></tr>
     <tr><th>CPU threads</th><td><?php print $cpu_threads ?></td></tr>
     <tr><th>Memory</th><td><?php print $memory_size ?></td></tr>
    </table>
    </span>

    <br />

<?php
  } // while
  $sql_ds="select * from datastores where hostname='".mysqli_real_escape_string($con,$host)."' and timestamp=(select max(timestamp) from datastores where hostname='".mysqli_real_escape_string($con,$host)."'); ";

  $result_ds=mysqli_query($con,$sql_ds);
  while ($row = $result_ds->fetch_assoc()) {
    $datastore=$row["datastore"];
    $name=$row["name"];
    $last_seen_ts=$row["timestamp"];
    $url=$row["url"];
    $capacity=floor($row["capacity"]/1024/1024/1024);
    $freespace=floor($row["freespace"]/1024/1024/1024);
    $datastore=$row["datastore"];
?>

    <span class="spn_50">
      <table class="tbl_ds_info">
      <tr><td class="tbl_info_header" colspan="2">Datastore info : <?php print $name ?></td></tr>
       <tr><th>Datastore</th><td><?php print $datastore ?></td></tr>
       <tr><th>Datastore path</th><td><?php print $url ?></td></tr>
       <tr><th>Capacity</th><td><?php print $capacity ?>Gb</td></tr>
       <tr><th>Freespace</th><td><?php print $freespace ?>Gb / <?php print floor(($freespace/$capacity)*100) ?>%</td></tr>
       <tr><th>Last seen</th><td><?php print $last_seen_ts ?></td></tr>
      </table>
    </span>
    <br/>

<?php } //while ?>


<?php
}
?>
