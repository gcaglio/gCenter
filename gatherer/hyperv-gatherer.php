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
    $memory_limit=$element->memory_limit;
    $memory_reservation=$element->memory_reservation;
    $memory_virtualquantity=$element->memory_virtualquantity;

    $summary=$element->vm_summary;

    $num_cpu=$summary->number_cpu;
    $cpu_load=$summary->processor_load;
    $heartbeat=$summary->heartbeat;
    $memory_usage=$summary->memory_usage;
    $memory_available=$summary->memory_available;
    $available_memory_buffer=$summary->available_memory_buffer;


    if (strlen($cpu_load)==0)
    {
      $cpu_load=0;
    }

    if (strlen($heartbeat)==0)
    {
      $heartbeat=0;
    }    

    if (strlen($memory_usage)==0)
    {
      $memory_usage=0;
    }


    $sql="insert into hyperv_virtual_machines (timestamp,date,time, hostname, vm_name, vm_id, health_state, status, status_descriptions, enabled_state, uptime_millisec, memory_limit, memory_reservation, memory_virtualquantity, num_cpu) values ('$date $time', '$date','$time','$hostname','$vm_name', '$vm_id', '$health_state','$status','$status_descriptions','$enabled_state', '$uptime_millisec', $memory_limit, $memory_reservation, $memory_virtualquantity, $num_cpu);";

    if ($db_con->query($sql) === TRUE) {
      echo "INFO : vm '$vm_name' on host '$hostname' informations inserted.\n";
    } else {
      echo "ERROR :  " . $sql . "\n" . $db_con->error."\n";
    }


    $sql_stat="insert into hyperv_vm_stat (timestamp,hostname,vmid,cpu_load,memory_usage,memory_available,available_memory_buffer,heartbeat,memory_limit,memory_reservation,memory_virtualquantity) values ('$date $time','$hostname','$vm_id', $cpu_load,$memory_usage,$memory_available,$available_memory_buffer,$heartbeat,$memory_limit,$memory_reservation,$memory_virtualquantity ); ";
    echo $sql_stat;
    if ($db_con->query($sql_stat) === TRUE) {
      echo "INFO : vm stat '$vm_name' on host '$hostname' informations inserted.\n";
    } else {
      echo "ERROR :  " . $sql_stat . "\n" . $db_con->error."\n";
    }


    getVmSnapshots( $db_con, $date, $time, $hostname, $ip, $port, $apikey, $vm_name );
  }

}


function getVmSnapshots( $db_con, $date, $time, $hostname, $ip, $port, $apikey, $vm_name ){

  $debug=true;
  $output=null;
  $retval=null;
  $protocol="http";

  $json = file_get_contents($protocol.'://'.$ip.":".$port."/hyperv/api_v1/getsnapshots/".$vm_name);
  $obj = json_decode($json);

  echo "INFO : getting snapshots of '$vm_name' from host '$hostname'. ";

  if ($debug){
    echo "DEBUG : output:\n";
    print_r($json."\n");

  }

  // replace a lot of string to have a valid json
  for ($i=0;$i<count($obj);$i++){
    $element=$obj[$i];

    $id=$element->id;
    $name=$element->name;
    $parent=$element->parent;
    $creation_date=$element->creation_date;
    $creation_time=$element->creation_time;

    $sql="insert into hyperv_vm_snapshots (timestamp,date,time, hostname, name, vmid, creation_date, creation_time, snap_id, parent_snap) values ('$date $time', '$date','$time','$hostname','$name', '$vm_name', '$creation_date','$creation_time','$id','$parent');";

    if ($db_con->query($sql) === TRUE) {
      echo "INFO : snap '$name' of vm '$vm_name' on host '$hostname' informations inserted.\n";
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
