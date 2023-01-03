<?php
require_once "../common/db.php";
require_once "../conf/db.php";
# manage vm status start/stop
$con=getConnection($servername,$username,$password,$dbname);


if (  isset($_GET["hostname"]) && isset($_GET["vmid"])  ) {
  $host=$_GET["hostname"];
  $vm_id=$_GET["vmid"];
  $action=$_GET["action"];


  $sql="select hostname, username, password, private_key from hosts where hostname='$host';";
  $result=mysqli_query($con,$sql);
  $user=null;
  $passwd=null;
  $private_key=null;
  while ($row = $result->fetch_assoc()) {
    $host=$row["hostname"];
    $user=$row["username"];
    $passwd=$row["password"];
    $private_key=$row["private_key"];
  }

  $debug=false;
  $output=null;
  $retval=null;
  $command="";

  if ("power_off"==$_GET["action"] )
  {
    $command="vim-cmd  vmsvc/power.off $vm_id";
  }else if ( "power_on"==$_GET["action"] ){
    $command="vim-cmd  vmsvc/power.on $vm_id";
  }




  $ssh_options="-o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null ";
  exec("sshpass -p $passwd  ssh $ssh_options $user@$host $command 2>&1", $output, $retval);
  echo "INFO : retval $retval\n";
  if ($debug){
    echo "DEBUG : output:\n";
    print_r($output);
  }

?>
  VM is transitioning... please verify new status in a while. <br/><br/>
  <b>Command : </b><pre><?php print $command ?></pre> <br/>
  <pre>
    <?php print_r($output) ?>
  </pre>
<?php
}else{
?>
Wrong invocation
<?php
}
?>
