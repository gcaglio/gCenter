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


if (  isset($_GET["hostname"]) && isset($_GET["ds"]) && "get_ds_info"==$_GET["action"]  ) {
  $host=mysqli_real_escape_string($con,$_GET["hostname"]);
  $ds_name=mysqli_real_escape_string($con,$_GET["ds"]);

  $sql_ds="select *  from datastores where name='$ds_name' and  hostname='$host' and timestamp=(select max(timestamp) from datastores where hostname='$host'); ";

?>
  <h2><?php print $host ?> / <?php print $ds_name ?></h2>
<?php

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

    <span class="spn_100">
      <table class="tbl_ds_info">
       <tr><td class="tbl_info_header" colspan="2">Datastore info</td></tr>
       <tr><th>Name</th><td><?php print $name ?></td></tr>
       <tr><th>ESXi host</th><td><?php print $host ?></td></tr>
       <tr><th>Datastore</th><td><?php print $datastore ?></td></tr>
       <tr><th>Datastore path</th><td><?php print $url ?></td></tr>
       <tr><th>Capacity</th><td><?php print $capacity ?>Gb</td></tr>
       <tr><th>Freespace</th><td><?php print $freespace ?>Gb / <?php print floor(($freespace/$capacity)*100) ?>%</td></tr>
       <tr><th>Last seen</th><td><?php print $last_seen_ts ?></td></tr>
      </table>
      <br/>
    </span>

    <br/>
    <br/> 
    <span class="spn_100">
      <table width="100%" class="tbl_ds_content">
        <tr><td class="tbl_info_header" colspan="6">Content (filesystem)</td></tr>
      </table>
    </span>
    <span class="spn_100" style="overflow: auto;height:60%">
      <table width="100%" class="tbl_ds_content">
        <tr><th class="sticky_th" style="width:10%">Size</th><th class="sticky_th" style="width:10%">Last mod.time</th><th class="sticky_th" style="width:80%">Filename</th></tr>
<?php
      $sql_content="select * from ds_content where timestamp='".mysqli_real_escape_string($con,$last_seen_ts)."' and datastore='".mysqli_real_escape_string($con,$name)."' and hostname='".mysqli_real_escape_string($con,$host)."'; ";

      $result_content=mysqli_query($con,$sql_content);
      while ($row = $result_content->fetch_assoc()) {
	$content=$row["content_ls"];
        $a_content=explode("\n",$content);
	for ($i=0; $i<count($a_content); $i++){
          $size=explode(";",$a_content[$i])[0];
          $last_mod_time=explode(";",$a_content[$i])[1];
          $last_mod_time2=explode(";",$a_content[$i])[2];
          $filepath=explode(";",$a_content[$i])[3];
?>
       <tr>
         <td><?php print $size; ?></td>
         <td><?php print $last_mod_time." ".$last_mod_time2; ?></td>
         <td><?php print $filepath; ?></td>
       </tr>
<?php   }
      } 
?>
      </table>

    </span>
<?php
  }
}
?>
