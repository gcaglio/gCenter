<?php
# gather information from HyperV servers
# and insert into db

require_once "../common/db.php";
require_once "../common/jsonify.php";
require_once "../conf/db.php";


function getHostInfo( $db_con, $date, $time, $hostname, $ip, $port, $apikey ){

  $debug=true;
  $output=null;
  $retval=null;
  $protocol="http";
  
  $json = file_get_contents($protocol.'://'.$ip.":".$port."/hyperv/api_v1/gethostinfo");
  $obj = json_decode($json);


  if ($debug){
    echo "DEBUG : output:\n";
    print_r($json."\n");

  }

  // replace a lot of string to have a valid json
  for ($i=0;$i<count($obj);$i++){
    $element=$obj[$i];
    $name=$element->name;
    $health_state=$element->health_state;
    $status=$element->status;
    $status_descriptions=implode(",", $element->status_description);
    $enabled_state=$element->enabled_state;

    $sql="insert into hyperv_hosts_informations (timestamp,date,time, hostname, name, health_state, status, status_descriptions, enabled_state) values ('$date $time', '$date','$time','$hostname','$name','$health_state','$status','$status_descriptions','$enabled_state');";

    if ($db_con->query($sql) === TRUE) {
      echo "INFO : host '$hostname' informations inserted.\n";
    } else {
      echo "ERROR :  " . $sql . "\n" . $db_con->error."\n";
    }

  }

}


function getVirtualMachines( $db_con, $date, $time, $hostname, $ip, $port, $apikey ){

  $debug=true;
  $output=null;
  $retval=null;
  $protocol="http";

  $json = file_get_contents($protocol.'://'.$ip.":".$port."/hyperv/api_v1/getvms");
  $obj = json_decode($json);


  if ($debug){
    echo "DEBUG : output:\n";
    print_r($json."\n");

  }

  // replace a lot of string to have a valid json
  for ($i=0;$i<count($obj);$i++){
    $element=$obj[$i];


    $vm_name=$element->vm_name;
    $health_state=$element->health_state;
    $vm_id=$element->vm_id;
    $uptime_millisec=$element->uptime_millis;
    $status_descriptions=implode(",", $element->status_description);
    $enabled_state=$element->enabled_state;
    $status=$element->status;

    $sql="insert into hyperv_vm_informations (timestamp,date,time, hostname, vm_name, vm_id, health_state, status, status_descriptions, enabled_state, uptime_millisec) values ('$date $time', '$date','$time','$hostname','$vm_name', '$vm_id', '$health_state','$status','$status_descriptions','$enabled_state', '$uptime_millisec');";

    if ($db_con->query($sql) === TRUE) {
      echo "INFO : vm '$vm_name' on host '$hostname' informations inserted.\n";
    } else {
      echo "ERROR :  " . $sql . "\n" . $db_con->error."\n";
    }

  }

}



#date and time of this scan
$date=date("Y-m-d");
$time=date("H:i:s");

# gather info from esxi hosts
$con=getConnection($servername,$username,$password,$dbname);

$sql="select hostname, ip, port, api_key from hyperv_hosts;";
$result=mysqli_query($con,$sql);
while ($row = $result->fetch_assoc()) {
  $host=$row["hostname"];
  $api_key=$row["api_key"];
  $ip=$row["ip"];
  $port=$row["port"];

  echo "INFO : gathering host informations from '".$host."'\n";
  getHostInfo($con, $date, $time, $host, $ip, $port, $api_key);
  echo "INFO : gathering vms from '".$host."'\n";
  getVirtualMachines($con, $date, $time, $host, $ip, $port, $api_key);
  echo "\n";
}

?>
