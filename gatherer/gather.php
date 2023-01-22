<?php
# gather information from ESXi servers
# and insert into db

require_once "../common/db.php";
require_once "../common/jsonify.php";
require_once "../conf/db.php";


function getDatastores(  $db_con, $date, $time, $host, $user, $passwd, $private_key) {
  $debug=true;
  $output=null;
  $retval=null;
  $command="vim-cmd hostsvc/datastore/listsummary";
  $ssh_options="-o StrictHostKeyChecking=no";
  exec("sshpass -p $passwd  ssh $ssh_options $user@$host $command", $output, $retval);
  echo "INFO : retval $retval\n";
  if ($debug){
    echo "DEBUG : output:\n";
    print_r($output);
  }

  // replace a lot of string to have a valid json
  $datastore_json_string=str_replace("(vim.Datastore.Summary)","",implode($output));
  $datastore_json_string=str_replace(" = "," : ",$datastore_json_string);
  $datastore_json_string=str_replace(" capacity"," \"capacity\" ",$datastore_json_string);
  $datastore_json_string=str_replace(" datastore"," \"datastore\" ",$datastore_json_string);
  $datastore_json_string=str_replace(" name "," \"name\" ",$datastore_json_string);
  $datastore_json_string=str_replace(" url "," \"url\" ",$datastore_json_string);
  $datastore_json_string=str_replace(" freeSpace "," \"freeSpace\"",$datastore_json_string);
  $datastore_json_string=str_replace(" uncommitted "," \"uncommitted\"",$datastore_json_string);
  $datastore_json_string=str_replace(" accessible "," \"accessible\"",$datastore_json_string);
  $datastore_json_string=str_replace(" multipleHostAccess "," \"multipleHostAccess\"",$datastore_json_string);
  $datastore_json_string=str_replace(" type "," \"type\"",$datastore_json_string);
  $datastore_json_string=str_replace(" maintenanceMode "," \"maintenanceMode\"",$datastore_json_string);
  $datastore_json_string=str_replace("true"," \"true\"",$datastore_json_string);
  $datastore_json_string=str_replace("false"," \"false\"",$datastore_json_string);
  $datastore_json_string=str_replace("<unset>","\"<unset>\"",$datastore_json_string);
  $datastore_json_string=str_replace("'","\"",$datastore_json_string);

  $datastore_json=json_decode($datastore_json_string);

  // for every datastore
  if ( count($datastore_json) >0 ) {
    foreach($datastore_json as $mydatastore)
    {
      $datastore=$mydatastore->datastore;
      $name=$mydatastore->name;
      $url=$mydatastore->url;
      $capacity=$mydatastore->capacity;
      $freeSpace=$mydatastore->freeSpace;

      if ($debug){
        echo "DEBUG : found datastore\n";
        echo "        datastore=$datastore\n";
        echo "        name     =$name\n";
        echo "        url      =$url\n";
        echo "        capacity =$capacity\n";
        echo "        freeSpace=$freeSpace\n";
      }
      $sql="insert into datastores (timestamp, date, time, hostname, datastore, name, url, capacity, freespace) values ('$date $time','$date','$time', '$host', '$datastore', '$name','$url','$capacity','$freeSpace' ); ";

            if ($db_con->query($sql) === TRUE) {
              echo "INFO : datastore  '$name' on '$host' inserted.\n";
            } else {
              echo "ERROR :  " . $sql . "\n" . $db_con->error."\n";
            }
      
    }
  }else{
    echo "INFO : no datastore found\n";
  }

}


function getHostInfo( $db_con, $date, $time, $host, $user, $passwd, $private_key){

  $debug=true;
  $output=null;
  $retval=null;
  $command="vim-cmd  hostsvc/hostsummary | sed 's/ *//' | sed 's/ = / : /' | sed 's/(.*)//' ";
  $ssh_options="-o StrictHostKeyChecking=no";
  exec("sshpass -p $passwd  ssh $ssh_options $user@$host $command", $output, $retval);
  echo "INFO : retval $retval\n";
  if ($debug){
    echo "DEBUG : output:\n";
    print_r($output);
  }

  // replace a lot of string to have a valid json
  $output_2=array();
  for ($i=0;$i<count($output);$i++){
    if (strpos($output[$i]," : ")>1){
      $output_2[$i]= "\"".substr($output[$i],0,strpos($output[$i]," : "))."\"".substr($output[$i],strpos($output[$i]," : "));
    }else{
      $output_2[$i]=$output[$i];
    }
  }
  $host_json_string=implode($output_2);
  $host_json_string=str_replace("true"," \"true\"",$host_json_string);
  $host_json_string=str_replace("false"," \"false\"",$host_json_string);
  $host_json_string=str_replace("<unset>","\"<unset>\"",$host_json_string);
  $host_json_string=str_replace("'","\"",$host_json_string);


  $host_json=json_decode($host_json_string);

  // if parsing ok
  if ( json_last_error()===JSON_ERROR_NONE ) {
    $hw_vendor=$host_json->hardware->vendor;
    $hw_model=$host_json->hardware->model;
    $hw_uuid=$host_json->hardware->uuid;
    $hw_memory_size=$host_json->hardware->memorySize;
    $hw_cpu_model=$host_json->hardware->cpuModel;
    $hw_cpu_mhz=$host_json->hardware->cpuMhz;
    $hw_cpu_cores=$host_json->hardware->numCpuCores;
    $hw_cpu_threads=$host_json->hardware->numCpuThreads;
    $hw_nics=$host_json->hardware->numNics;
    $hw_hbas=$host_json->hardware->numHBAs;
    $is_maintenance_mode=$host_json->runtime->inMaintenanceMode;
    $is_quarantine_mode=$host_json->runtime->inQuarantineMode;
    $boot_time=$host_json->runtime->bootTime;
    $host_name=$host_json->config->name;
    $esxi_name=$host_json->config->product->name;
    $esxi_fullname=$host_json->config->product->fullName;
    $esxi_version=$host_json->config->product->version;
    $esxi_ostype=$host_json->config->product->osType;
    $esxi_product_line=$host_json->config->product->productLineId;
    $esxi_vmotion_enabled=$host_json->config->vmotionEnabled;

    if ($debug){
      echo "DEBUG : found host info\n";
      echo "        hardware vendor     = $hw_vendor\n";
      echo "        hardware model      = $hw_model\n";
      echo "        hardware uuid       = $hw_uuid\n";
      echo "        memory size         = $hw_memory_size\n";
      echo "        cpu model           = $hw_cpu_model\n";
      echo "        cpu mhz             = $hw_cpu_mhz\n";
      echo "        cpu cores           = $hw_cpu_cores\n";
      echo "        cpu threads         = $hw_cpu_threads\n";
      echo "        nics                = $hw_nics\n";
      echo "        hbas                = $hw_hbas\n";
      echo "        in maintenance mode = $is_maintenance_mode\n";
      echo "        in quarantine mode  = $is_quarantine_mode\n";
      echo "        boot time           = $boot_time\n";
      echo "        host name           = $host_name\n";
      echo "        product name        = $esxi_name\n";
      echo "        product fullname    = $esxi_fullname\n";
      echo "        product version     = $esxi_version\n";
      echo "        os type             = $esxi_ostype\n";
      echo "        product line        = $esxi_product_line\n";
      echo "        vmotion enabled     = $esxi_vmotion_enabled\n";
    }

    $sql="insert into hosts_informations (timestamp, date, time, hostname, hw_vendor,hw_model,hw_uuid,memory_size,cpu_model,cpu_mhz,cpu_cores,cpu_threads,nics,hbas,is_in_maintenance,is_in_quarantine,boot_time, host_name,product_name,product_fullname,product_version,os_type,product_line,vmotion_enabled  ) values('$date $time', '$date','$time','$host','$hw_vendor','$hw_model','$hw_uuid','$hw_memory_size','$hw_cpu_model','$hw_cpu_mhz','$hw_cpu_cores','$hw_cpu_threads','$hw_nics','$hw_hbas',$is_maintenance_mode,'$is_quarantine_mode','$boot_time','$host_name','$esxi_name','$esxi_fullname','$esxi_version','$esxi_ostype','$esxi_product_line',$esxi_vmotion_enabled );";

    if ($db_con->query($sql) === TRUE) {
      echo "INFO : host '$host' informations inserted.\n";
    } else {
      echo "ERROR :  " . $sql . "\n" . $db_con->error."\n";
    }


  }else{
        echo "INFO : no host info found / json error\n";

        if ($debug){

          echo "DEBUG : raw output\n";
          echo implode($output,"\n")."\n";
          echo "DEBUG : fixed json\n";
          echo implode($output_2,"\n")."\n";
          echo "DEBUG : last json parse error message\n";
          echo json_last_error_msg();
        }
  }


}





function getVm( $db_con, $date, $time, $host, $user, $passwd, $private_key){
  $debug=true;
  $output=null;
  $retval=null;
  $command="vim-cmd vmsvc/getallvms";
  $ssh_options="-o StrictHostKeyChecking=no";
  exec("sshpass -p $passwd  ssh $ssh_options $user@$host $command", $output, $retval);
  echo "INFO : retval $retval\n";
  if ($debug){
    echo "DEBUG : output:\n";
    print_r($output);
  }

  // for every line extract values
  if ( count($output) >0 ) {

    for ($i=0; $i<count($output); $i++){
      $line=$output[$i];
      if ( !( strpos($line,"Vmid")>=0 && strpos($line,"Annotation")>0 && strpos($line,"Name")>0)){
            $vmid=substr($line,0,7);
            $name=substr($line,7,15);
            $file=substr($line,23,42);
            $datastore=substr($file,1,strpos($file,"]")-1);
            $path=substr($file,strpos($file,"] ")+2);
            $guestos=substr($line,66,22);
            $version=substr($line,88,10);

            if ($debug){
              echo "DEBUG : found vm\n";
              echo "        vmid     =$vmid\n";
              echo "        name     =$name\n";
              echo "        file     =$file\n";
              echo "        datastore=$datastore\n";
              echo "        path     =$path\n";
              echo "        guestos  =$guestos\n";
              echo "        version  =$version\n";
	    }

	    $sql="insert into virtual_machines (timestamp,date, time, hostname, vmid, name, file, datastore, path, guest_os, version) values ('$date $time', '$date','$time', '$host', '$vmid', '$name','$file','$datastore','$path','$guestos','$version'); ";

	    if ($db_con->query($sql) === TRUE) {
              echo "INFO : vm '$name' on '$host' inserted.\n";
            } else {
              echo "ERROR :  " . $sql . "\n" . $db_con->error."\n";
            }

      }

    }
  }else{
    echo "INFO : no vm found\n";
  }


}




function getVmSummary(  $db_con, $date, $time, $host, $user, $passwd, $private_key) {
  $debug=true;

  # get vms
  $sql_vm="select vmid, name, timestamp from virtual_machines where hostname='$host' and timestamp= (select max(timestamp) from virtual_machines where hostname='$host'); ";

  $result_vm=mysqli_query($db_con,$sql_vm);
  while ($row = $result_vm->fetch_assoc()) {
    $vm_id=$row["vmid"];
    $name=$row["name"];
    $db_ts=$row["timestamp"];

    $output=null;
    $retval=null;
    $command="vim-cmd vmsvc/get.summary $vm_id | sed 's/= (.*)/:/g' ";
    $ssh_options="-o StrictHostKeyChecking=no ";
    exec("sshpass -p $passwd  ssh $ssh_options $user@$host $command", $output, $retval);
    echo "INFO : retval $retval\n";
    if ($debug){
      echo "DEBUG : output:\n";
      print_r($output);
    }

    $vmsummary_output=jsonify($output);
    $vmsummary_json=json_decode($vmsummary_output);

    // if parsing ok
    if ( json_last_error()===JSON_ERROR_NONE ) {

      $cfg_numcpu=$vmsummary_json->config->numCpu;
      $cfg_memory_mb=$vmsummary_json->config->memorySizeMB;

      if ($debug){
        echo "DEBUG : found summary\n";
        echo "        config.numCpu         =$cfg_numcpu\n";
        echo "        config.memorySizeMB   =$cfg_memory_mb\n";

      } //debug

      $sql="update virtual_machines set config_numCpu=$cfg_numcpu, config_memorySizeMB=$cfg_memory_mb where timestamp='$db_ts' and hostname='$host' and vmid='$vm_id';";

      if ($db_con->query($sql) === TRUE) {
        echo "INFO : datastore  '$name' on '$host' inserted.\n";
      } else {
        echo "ERROR :  " . $sql . "\n" . $db_con->error."\n";
      }
      
    } //json last error
  } //while sql_vm

} //getVmSummary function

#date and time of this scan
$date=date("Y-m-d");
$time=date("H:i:s");

# gather info from esxi hosts
$con=getConnection($servername,$username,$password,$dbname);

$sql="select hostname, username, password, private_key from hosts;";
$result=mysqli_query($con,$sql);
while ($row = $result->fetch_assoc()) {
  $host=$row["hostname"];
  $user=$row["username"];
  $passwd=$row["password"];
  $private_key=$row["private_key"];

  echo "INFO : gathering VMs from '".$host."'\n";
  getVm( $con, $date, $time, $host, $user, $passwd, $private_key);
  echo "INFO : get VMs summary from '".$host."'\n";
  getVmSummary( $con, $date, $time, $host, $user, $passwd, $private_key);

  echo "INFO : gathering datastore from '".$host."'\n";
  getDatastores( $con, $date, $time, $host, $user, $passwd, $private_key);

  echo "INFO : gathering host informations from '".$host."'\n";
  getHostInfo($con, $date, $time, $host, $user, $passwd, $private_key);
  echo "\n";
}

?>
