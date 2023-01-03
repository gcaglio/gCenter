<?php
require_once "../common/db.php";
require_once "../conf/db.php";
# return vm informations
$con=getConnection($servername,$username,$password,$dbname);


if (  isset($_GET["hostname"]) && isset($_GET["vmid"]) && "get_vm_info"==$_GET["action"]  ) {
  $host=$_GET["hostname"];
  $vmid=$_GET["vmid"];
?>
    <table class="tbl_vm_info">
      <tr><th>VMid</th><td><?php print $vmid ?> <span onclick="poweronVm('<?php print $host ?>','<?php print $vmid ?>')">PowerOn</span> &nbsp; <span onclick="poweroffVm('<?php print $host ?>','<?php print $vmid ?>')">PowerOff</span>  </td></tr>
      <tr><th>Hostname</th><td><?php print $host ?></td></tr>
    </table>

<?php
}
?>
