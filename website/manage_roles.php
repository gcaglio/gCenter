<?php
require_once "../common/db.php";
require_once "../conf/db.php";
require_once( "../common/check_roles.php");
# return roles informations

if(!isset($_SESSION)) session_start();

if ( ! (isset($_SESSION["_CURRENT_USER"]) ) ){
  $_GET["message"]="Session not valid. Please login.";
  header('Location: ./index.php');
  exit;
}




$con=getConnection($servername,$username,$password,$dbname);


if ( isAdmin($con) && "get_roles"==$_GET["action"]  ) {
?>
    <h2>Roles  </h2>


<?php 
   if ( isset($_SESSION["successful_message"]) ){
?>
    <span class="success_message">
      <?php print $_SESSION["successful_message"]; unset($_SESSION["successful_message"]); ?>
    </span>
<?php
   }
?>




<?php
   if ( isset($_SESSION["error_message"]) ){
?>
    <span class="error_message">
      <?php print $_SESSION["error_message"]; unset($_SESSION["error_message"]); ?>
    </span>
<?php
   }
?>



    <span class="spn_100">
      <form action="./manage_roles.php?action=insert_role" method="POST">

        <table class="tbl_insert_role">
          <tr>
	    <th>Username</th>
	    <th>Role</th>
	    <th>Object</th>
	  </tr>

          <tr>
            <td>
		<select class="select_role" name="username" >
  		  <option value=""></option>
<?php
                  $sql_user="select username from users";
                  $result_user=mysqli_query($con,$sql_user);
                  while ($row_user = $result_user->fetch_assoc()) {
                    $username=$row_user["username"];
?>

                   <option value="<?php print $username?>"><?php print $username ?></option>

<?php 
		  }
?>
                </select>
            </td>



            <td>
		<select class="select_role" name="role" >
                   <option value=""></option>
                   <option value="ADMIN">ADMIN</option>
                   <option value="POWER_MGMT">POWER_MGMT</option>
                   <option value="SNAP_MGMT">SNAP_MGMT</option>
                   <option value="VIEWER">VIEWER</option>
                </select>
            </td>



            <td>
		<select class="select_role" name="object" >
		  <option value=""></option>
<?php
		  # esxi hosts
                  $sql_hosts="select hostname from hosts order by hostname";
                  $result_hosts=mysqli_query($con,$sql_hosts);
                  while ($row_hosts = $result_hosts->fetch_assoc()) {
                    $hostname=$row_hosts["hostname"];
?>
                    <option value="/<?php print $hostname?>/*">[/<?php print $hostname ?>/*]  (Vmware) host and all its resources </option>

<?php
                    # esxi vms
                    $sql_vm="select hostname, name from virtual_machines where timestamp=(select max(timestamp) from virtual_machines) and hostname='$hostname' order by hostname,name";
		    $result_vm=mysqli_query($con,$sql_vm);
		    while ($row_vm = $result_vm->fetch_assoc()) {
		      $hostname=$row_vm["hostname"];
		      $name=$row_vm["name"];
?>
                      <option value="/<?php print $hostname?>/<?php print $name?>">[/<?php print $hostname ?>/<?php print $name?>]  this vm only </option>
<?php
		    }

                  }
?>


<?php
                  # hyperv hosts
                  $sql_hosts="select hostname from hyperv_hosts order by hostname";
                  $result_hosts=mysqli_query($con,$sql_hosts);
                  while ($row_hosts = $result_hosts->fetch_assoc()) {
                    $hostname=$row_hosts["hostname"];
?>
                    <option value="/<?php print $hostname?>/*">[/<?php print $hostname ?>/*]  (HyperV) host and all its resources </option>

<?php
                    # hyperv vms
                    $sql_vm="select hostname, vm_name from hyperv_virtual_machines where timestamp=(select max(timestamp) from hyperv_virtual_machines) and hostname='$hostname' order by hostname, vm_name";
                    $result_vm=mysqli_query($con,$sql_vm);
                    while ($row_vm = $result_vm->fetch_assoc()) {
                      $hostname=$row_vm["hostname"];
                      $name=$row_vm["vm_name"];
?>
                      <option value="/<?php print $hostname?>/<?php print $name?>">[/<?php print $hostname ?>/<?php print $name?>]  this vm only </option>
<?php
                    }

                  }
?>
                




                </select>
	    </td>
            <td>
              <button type="submit" value="Insert role">Insert role</button>
            </td>


          </tr>

        </table>
        <input type="hidden" name="action" value="insert_role"/>
      </form>
    </span>
 
    <br/>
    <br/>
    <span class="spn_100">
    <table class="tbl_role_info">
      <tr>
        <th>Username</th>
        <th>Role</th>
	<th>Object</th>
        <th></th>
      </tr>
<?php

  $sql="select * from roles order by username, object; ";
  $result=mysqli_query($con,$sql);
  while ($row = $result->fetch_assoc()) {
    $username=$row["username"];
    $role=$row["role"];
    $object=$row["object"];

?>
      <tr>
        <td><?php print $username ?></td>
        <td><?php print $role ?></td>
	<td><?php print $object ?></td> 
	<td><img onclick="deleteRole('<?php print md5( $username.";".$role.";".$object ) ?>')" src="./images/delete_role.png" title="delete role"/></td>
      </tr>
  
<?php
  } // while
?>

   </table>
   </span>




<?php
}else if ( isAdmin($con) && "delete_role"==$_GET["action"] && isset($_GET["hash"])  ) {
  $hash=mysqli_real_escape_string($con,$_GET["hash"]);
  $sql_delete_role="delete from roles where '".$hash."' =  md5( concat(username,concat(';',concat(role,concat(';',object)))) );";
#  echo $sql_delete_role;
  if (mysqli_query($con,$sql_delete_role)) {
    $_SESSION["successful_message"]="Role deleted";
    
  }else{
    $_SESSION["error_message"]="Error deleting role";
  }

  header('Location: ./show_message.php');
  exit;






}else if ( isAdmin($con) && isset($_POST["username"]) && isset($_GET["action"]) && "insert_role" == $_GET["action"]  && isset($_POST["object"]) && isset($_POST["role"]) && strlen($_POST["username"])>0 && strlen($_POST["object"])>0  && strlen($_POST["role"])>0  ) {
  # insert new role
  $username=mysqli_real_escape_string($con,$_POST["username"]);
  $object=mysqli_real_escape_string($con,$_POST["object"]);
  $role=mysqli_real_escape_string($con,$_POST["role"]);

  $sql_insert="insert into roles (username,object,role) values ('".$username."','".$object."','".$role."'); ";
  if (mysqli_query($con,$sql_insert)) {
    $_SESSION["successful_message"]="Role inserted";
  }else{
    $_SESSION["error_message"]="Error inserting role";
  }

  header('Location: ./home.php?action=show_manage_roles');
  exit;

}else{
?>
  <span class="error_message">
    User is not admin on '*'
  </span>



<?php
}

?>
