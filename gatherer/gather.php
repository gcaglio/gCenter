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
      $datastore=trim($mydatastore->datastore);
      $name=trim($mydatastore->name);
      $url=trim($mydatastore->url);
      $capacity=trim($mydatastore->capacity);
      $freeSpace=trim($mydatastore->freeSpace);

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
            $vmid=trim(substr($line,0,7));
            $name=trim(substr($line,7,15));
            $file=trim(substr($line,23,42));
            $datastore=trim(substr($file,1,strpos($file,"]")-1));
            $path=trim(substr($file,strpos($file,"] ")+2));
            $guestos=trim(substr($line,66,22));
            $version=trim(substr($line,88,10));

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
	    getVmDevices( $db_con, $date, $time, $host, $user, $passwd, $private_key, $vmid);
      }

    }
  }else{
    echo "INFO : no vm found\n";
  }


}

function getNetwork( $db_con, $date, $time, $host, $user, $passwd, $private_key){
  $debug=true;
  $output=null;
  $retval=null;
  $command="esxcli --formatter=csv network vswitch standard portgroup list";
  $ssh_options="-o StrictHostKeyChecking=no";
  exec("sshpass -p $passwd  ssh $ssh_options $user@$host $command", $output, $retval);
  echo "INFO : retval $retval\n";
  if ($debug){
    echo "DEBUG : output:\n";
    print_r($output);
  }

  // for every line extract values
  // ActiveClients,Name,VLANID,VirtualSwitch,
  // 1,Management Network,0,vSwitch0,
  // 3,VM Network,0,vSwitch0,


  if ( count($output) >1 ) {

    for ($i=1; $i<count($output); $i++){
      $line=$output[$i];
      $params=explode(",",$line);
      
      $active_client=$params[0];
      $portgroup_name=$params[1];
      $vswitch_name=$params[3];
      $vlan_id=$params[2];


      if ($debug){
        echo "DEBUG : found entry \n";
        echo "        vswitch       = $vswitch_name\n";
        echo "        portgroup     = $portgroup_name\n";
        echo "        vlan          = $vlan_id\n";
        echo "        active client = $active_client\n";
      }

      $sql="insert into vswitch_informations (timestamp,date, time, hostname, vswitch_name, portgroup_name, active_clients, vlan_id ) values ('$date $time', '$date','$time', '$host', '$vswitch_name', '$portgroup_name','$active_client','$vlan_id'); ";

       if ($db_con->query($sql) === TRUE) {
         echo "INFO : vswitch/portgroup '$vswitch_name'/'$porgroup_name' on '$host' inserted.\n";
       } else {
         echo "ERROR :  " . $sql . "\n" . $db_con->error."\n";
       }

    }

  }
}




function getVmSnapshots(  $db_con, $date, $time, $host, $user, $passwd, $private_key) {
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
    $command="vim-cmd vmsvc/get.snapshotinfo $vm_id | sed 's/= (.*)/:/g' ";
    $ssh_options="-o StrictHostKeyChecking=no ";
    exec("sshpass -p $passwd  ssh $ssh_options $user@$host $command", $output, $retval);
    echo "INFO : retval $retval\n";
    if ($debug){
      echo "DEBUG : output:\n";
      print_r($output);
    }

    $vmsnap_output=jsonify($output);
    $vmsnap_json=json_decode($vmsnap_output);

    // if parsing ok
    if ( json_last_error()===JSON_ERROR_NONE ) {

      if (isset($vmsnap_json->rootSnapshotList)){
        $root_snapshot_list=$vmsnap_json->rootSnapshotList;

	// first item is not a snapshot datastructure
	for ( $s=0; $s<count($root_snapshot_list); $s++){
          $current_snap=$root_snapshot_list[$s];
          drillDownSnapTree($db_con, $db_ts, $date, $time, $host, $vm_id, $current_snap,'');
	}

      }

    }
  }
}

function drillDownSnapTree( $db_con, $db_ts, $date, $time, $host, $vm_id, $current_snap, $parent_snap){


  $name=$current_snap->name;
  $description=$current_snap->description;
  $snapshot=$current_snap->snapshot;
  $create_time=$current_snap->createTime;
  $quiesced=$current_snap->quiesced;

  $sql_snap="insert into vm_snapshots (timestamp,date,time,hostname,vmid,name,snapshot,create_time, parent_snap, description, quiesced) values ('$db_ts', '$date','$time', '$host' ,'$vm_id', '$name','$snapshot','$create_time','$parent_snap' ,'$description' , $quiesced ) ; ";
  if ($db_con->query($sql_snap) === TRUE) {
    echo "INFO : vm snap '$snapshot' for '$vm_id' on '$host' inserted.\n";
  } else {
    echo "ERROR : insert vm snap :  " . $sql_snap . "\n" . $db_con->error."\n";
  }

  if ( isset($current_snap->childSnapshotList) && (!($current_snap->childSnapshotList == "<unset>"   ))    ){
    $snap_list=$current_snap->childSnapshotList;

    for ( $s=0; $s<count($snap_list); $s++){    
      $loop_snap=$snap_list[$s];
      drillDownSnapTree( $db_con, $db_ts, $date, $time, $host, $vm_id, $loop_snap, $snapshot);
    }
  }
}


function getDatastoreContent(  $db_con, $date, $time, $host, $user, $passwd, $private_key) {
  $debug=true;

  # get vms
  $sql_ds="select timestamp, date, time, url, datastore, name from datastores where hostname='$host' and timestamp= (select max(timestamp) from datastores where hostname='$host'); ";

  $result_ds=mysqli_query($db_con,$sql_ds);
  while ($row = $result_ds->fetch_assoc()) {
    $ds_name=$row["name"];
    $ds_url=$row["url"];
    $ds_datastore=$row["datastore"];
    $ds_ts=$row["timestamp"];
    $ds_date=$row["date"];
    $ds_time=$row["time"];

    $output=null;
    $retval=null;
    $command="find \"$ds_url\" -type f -exec \"ls -lh {} \\;\"  | awk '{ print $5 \";\" $6 \" \" $7 \";\" $8 \";\" substr($0,index($0,$9)) }'  ";
    echo $command;
    $ssh_options="-o StrictHostKeyChecking=no ";
    exec("sshpass -p $passwd  ssh $ssh_options $user@$host $command", $output, $retval);
    echo "INFO : retval $retval\n";
    if ($debug){
      echo "DEBUG : output:\n";
      print_r($output);
    }

    $sql_ds_content="insert into ds_content (timestamp,date,time,hostname, datastore, content_ls) values ('$ds_ts', '$ds_date','$ds_time','$host','$ds_name', '".mysqli_real_escape_string($db_con, implode("\n",$output))."'  ) ; ";
    if ($db_con->query($sql_ds_content) === TRUE) {
      echo "INFO : datastore content for datastore '$name' on '$host' inserted.\n";
    } else {
      echo "ERROR : insert datastore :  " . $sql_ds_content . "\n" . $db_con->error."\n";
    }

  }
}




function getVmDevices(  $db_con, $date, $time, $host, $user, $passwd, $private_key, $vm_id) {
  $debug=true;

  $output=null;
  $retval=null;
  $command="vim-cmd vmsvc/device.getdevices $vm_id | sed 's/= (.*)/:/g' ";
  $ssh_options="-o StrictHostKeyChecking=no ";
  exec("sshpass -p $passwd  ssh $ssh_options $user@$host $command", $output, $retval);
  echo "INFO : retval $retval\n";
  if ($debug){
    echo "DEBUG : output:\n";
    print_r($output);
  }

  $vmconfig_output=jsonify($output);
  $vmconfig_json=json_decode($vmconfig_output);

  // if parsing ok
  if ( json_last_error()===JSON_ERROR_NONE ) {

      if ($debug){
        echo "DEBUG : found config\n";
        echo "  ".$vmconfig_output."\n";
      }

      foreach($vmconfig_json->device as $device) {
        echo "INFO : device ".$device->deviceInfo->label."\n";
	// is network interface
	if (isset($device->macAddress)){
	  $backing_portgroup=$device->backing->deviceName;
          $mac_address=$device->macAddress;
	  echo "INFO : device type = network interface '".$mac_address."' connected to '".$backing_portgroup."' \n";

          $sql_vm_net="insert into vm_network_devices (timestamp,date,time,hostname,vmid,netdevice_id ,macaddress , backing_portgroup) values ('$date $time','$date','$time', '$host' ,'$vm_id', '','$mac_address','$backing_portgroup'   ) ; ";
          if ($db_con->query($sql_vm_net) === TRUE) {
            echo "INFO : vm net device for vm  '$vm_id' on '$host' inserted.\n";
          } else {
            echo "ERROR insert vm_network_devices :  " . $sql_vm_net . "\n" . $db_con->error."\n";
          }
	  
	}	

      }
      
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
      $cfg_guestfullname=$vmsummary_json->config->guestFullName;
      $runtime_powerstate=$vmsummary_json->runtime->powerState;
      $runtime_boottime=$vmsummary_json->runtime->bootTime;
      $overall_status=$vmsummary_json->overallStatus;

      if ($debug){
        echo "DEBUG : found summary\n";
        echo "        config.numCpu         =$cfg_numcpu\n";
        echo "        config.memorySizeMB   =$cfg_memory_mb\n";
        echo "        runtime.powerState    =$runtime_powerstate\n";
        echo "        runtime.bootTime      =$runtime_boottime\n";
        echo "        config.guestFullName  =$cfg_guestfullname\n";
        echo "        overallStatus         =$overall_status\n";

      } //debug

      $sql="update virtual_machines set config_numCpu=$cfg_numcpu, config_memorySizeMB=$cfg_memory_mb, runtime_powerstate='$runtime_powerstate', runtime_lastboottime='$runtime_boottime', guest_guestfullname='$cfg_guestfullname', overall_status='$overall_status'  where timestamp='$db_ts' and hostname='$host' and vmid='$vm_id';";

      if ($db_con->query($sql) === TRUE) {
        echo "INFO : virtual_machines '$name' on '$host' record updated with config and runtime info.\n";
      } else {
        echo "ERROR update virtual_machines :  " . $sql . "\n" . $db_con->error."\n";
      }

      if ($runtime_powerstate == "poweredOn" ){
        // update statics
        $overallCpuUsage=$vmsummary_json->quickStats->overallCpuUsage;
        $guestMemoryUsage=$vmsummary_json->quickStats->guestMemoryUsage;
        $hostMemoryUsage=$vmsummary_json->quickStats->hostMemoryUsage;
        $guestHeartbeatStatus=$vmsummary_json->quickStats->guestHeartbeatStatus;
        $grantedMemory=$vmsummary_json->quickStats->grantedMemory;
        $sharedMemory=$vmsummary_json->quickStats->sharedMemory;
        $swappedMemory=$vmsummary_json->quickStats->swappedMemory;
        $balloonedMemory=$vmsummary_json->quickStats->balloonedMemory;
        $consumedOverheadMemory=$vmsummary_json->quickStats->consumedOverheadMemory;
        $compressedMemory=$vmsummary_json->quickStats->compressedMemory;
        $uptimeSeconds=$vmsummary_json->quickStats->uptimeSeconds;

        $sql_stat="insert into vm_quickstat (timestamp,hostname,vmid,overallCpuUsage,guestMemoryUsage,hostMemoryUsage,guestHeartbeatStatus,grantedMemory,sharedMemory,swappedMemory,balloonedMemory,consumedOverheadMemory,compressedMemory,uptimeSeconds) values ('$db_ts', '$host' ,'$vm_id', $overallCpuUsage, $guestMemoryUsage, $hostMemoryUsage, '$guestHeartbeatStatus', $grantedMemory, $sharedMemory, $swappedMemory, $balloonedMemory, $consumedOverheadMemory, $compressedMemory, $uptimeSeconds   ) ; ";
        if ($db_con->query($sql_stat) === TRUE) {
          echo "INFO : vm stats for  '$name' on '$host' inserted.\n";
        } else {
          echo "ERROR insert vm_quickstat :  " . $sql_stat . "\n" . $db_con->error."\n";
        }
      }else{
        echo "INFO : vm '$vm_id' on host '$host' is '$runtime_powerstate', no stats will be collected\n";
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

  echo "INFO : gathering datastore content from '$host'\n";
  getDatastoreContent( $con, $date, $time, $host, $user, $passwd, $private_key);

  echo "INFO : gathering host informations from '".$host."'\n";
  getHostInfo($con, $date, $time, $host, $user, $passwd, $private_key);
  
  echo "INFO : gathering VMs snapshot info from '".$host."'\n";
  getVmSnapshots($con, $date, $time, $host, $user, $passwd, $private_key);

  echo "\n";
}

echo "INFO : gathering network vSwitch configurations from '".$host."'\n";
getNetwork( $con, $date, $time, $host, $user, $passwd, $private_key);

echo "END\n"
?>
