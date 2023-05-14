<?php
require_once "../common/db.php";
require_once "../conf/db.php";
# manage vm status start/stop

session_start();
if ( ! (isset($_SESSION["_CURRENT_USER"]) ) ){
  $_GET["message"]="Session not valid. Please login.";
  header('Location: ./index.php');
  exit;
}



$con=getConnection($servername,$username,$password,$dbname);

ini_set("allow_url_fopen", 1);
$protocol="http";
$debug=false;

if (  isset($_GET["hostname"]) && isset($_GET["vmid"])  ) {
  $host=$_GET["hostname"];
  $vm_id=$_GET["vmid"];
  $action=$_GET["action"];


  $sql="select hostname, ip, port from hyperv_hosts where hostname='$host';";
  $result=mysqli_query($con,$sql);
  $ip=null;
  $port=null;
  while ($row = $result->fetch_assoc()) {
    $host=$row["hostname"];
    $ip=$row["ip"];
    $port=$row["port"];
  }



  $json = file_get_contents($protocol.'://'.$ip.":".$port."/hyperv/api_v1/getvms");
  $obj = json_decode($json);


  if ("power_off"==$_GET["action"] )
  {
    $command="/hyperv/api_v1/stopvm/$vm_id";


    $json = file_get_contents($protocol.'://'.$ip.":".$port.$command);
    $obj = json_decode($json);
    $status = $obj->status;


    echo "INFO : stopvm $vm_id retval $status<br/>";
    if ($debug){
      echo "DEBUG : output: $json<br/>";
    }

    if ( $status == "OK" ){
?>
    Powered off
<?php
    }else{
      $description=$obj->description;
?>
      Error during Poweroff : <?php print $description ?>
<?php	    
    }

  }else if ( "power_on"==$_GET["action"] ){

    $command="/hyperv/api_v1/startvm/$vm_id";


    $json = file_get_contents($protocol.'://'.$ip.":".$port.$command);
    $obj = json_decode($json);
    $status = $obj->status;


    echo "INFO : startvm $vm_id retval $status<br/>";
    if ($debug){
      echo "DEBUG : output: $json<br/>";
    }

    if ( $status == "OK" ){
?>
    Powered on
<?php
    }else{
      $description=$obj->description;
?>
      Error during Poweron : <?php print $description ?>
<?php
    }
  }else if ( "reboot"==$_GET["action"] ){

    $command="/hyperv/api_v1/rebootvm/$vm_id";


    $json = file_get_contents($protocol.'://'.$ip.":".$port.$command);
    $obj = json_decode($json);
    $status = $obj->status;


    echo "INFO : rebootvm $vm_id retval $status<br>";
    if ($debug){
      echo "DEBUG : output: $json<br>";
    }

    if ( $status == "OK" ){
?>
    Rebooting...
<?php
    }else{
      $description=$obj->description;
?>
      Error during Reboot : <?php print $description ?>
<?php
    }

  }else if ( "reset"==$_GET["action"] ){

    $command="/hyperv/api_v1/resetvm/$vm_id";


    $json = file_get_contents($protocol.'://'.$ip.":".$port.$command);
    $obj = json_decode($json);
    $status = $obj->status;


    echo "INFO : resetvm $vm_id retval $status<br>";
    if ($debug){
      echo "DEBUG : output: $json<br>";
    }

    if ( $status == "OK" ){
?>
    Reseting...
<?php
    }else{
      $description=$obj->description;
?>
      Error during Resetting : <?php print $description ?>
<?php
    }


  }else if ( "take_snap"==$_GET["action"] ){
    $full_date=date("D M j G:i:s T Y");
    $desc_snap="gCenter snap taken on : $full_date";

    $command="/hyperv/api_v1/takesnapshot/$vm_id";


    $json = file_get_contents($protocol.'://'.$ip.":".$port.$command);
    $obj = json_decode($json);
    $status = $obj->status;


    echo "INFO : Take snapshot $vm_id retval $status<br>";
    if ($debug){
      echo "DEBUG : output: $json<br>";
    }

    if ( $status == "OK" ){
?>
      Snaphost taken succesfully
<?php
    }else{
      $exception=$obj->exception;
?>
      Error during creating snapshot : <?php print $status ?><br/>
      <?php print $exception ?>
<?php
    }

	
  }

}else{
?>
Wrong invocation
<?php
}
?>

