<?php
# gather information from ESXi servers
# and insert into db

require_once "../common/db.php";
require_once "../conf/db.php";


$reg_retention_timestamp=date("Y-m-d", strtotime("-".$reg_data_retention." day"));
$perf_retention_timestamp=date("Y-m-d", strtotime("-".$perf_data_retention." day"));

$anag_tables=["hosts_informations","hyperv_hosts_informations","hyperv_virtual_machines","virtual_machines","hyperv_vm_snapshots","vm_network_devices","vm_snapshots","vswitch_informations","ds_content","datastores"];

$perf_tables=["hyperv_vm_stat","vm_quickstat"];

$db_con=getConnection($servername,$username,$password,$dbname);


echo "INFO : deleting registry information in tables.\n";
foreach ( $anag_tables as $anag_table ){
  echo "INFO : deleting registry informations older than $reg_retention_timestamp in table $anag_table\n"; 
  $sql="delete from $anag_table where timestamp<$reg_retention_timestamp";

  if ($db_con->query($sql) === TRUE) {
    echo "INFO : retention applied succesfully\n";
  } else {
	  echo "ERROR : error deleting record\n";
	  echo "        $sql\n\n";

  }
}


echo "INFO : deleting performance information in tables.\n";
foreach ( $perf_tables as $perf_table ){
  echo "INFO : deleting registry informations older than $perf_retention_timestamp in table $perf_table\n";
  $sql="delete from $perf_table where timestamp<$perf_retention_timestamp";

  if ($db_con->query($sql) === TRUE) {
    echo "INFO : retention applied succesfully\n";
  } else {
          echo "ERROR : error deleting record\n";
          echo "        $sql\n\n";

  }
}

echo "INFO : end\n";

?>
