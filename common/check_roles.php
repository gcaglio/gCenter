<?php

# these are facilities functions to check for privileges of the current session user.
function canManagePower($con, $host,$vm){
  if ( ! (isset($_SESSION["_CURRENT_USER"]) ) ){
    return false;
  }

  $current_user=$_SESSION["_CURRENT_USER"];
  $sql_get_roles="select *  from roles where username = '".mysqli_real_escape_string($con,$current_user)."' and ( ( object='*' or object like '/".mysqli_real_escape_string($con,$host)."' or object like '/".mysqli_real_escape_string($con,$host)."/' or object like '/".mysqli_real_escape_string($con,$host)."/*' or object like '/".mysqli_real_escape_string($con,$host)."/".mysqli_real_escape_string($con,$vm)."' ) and ( role='ADMIN' or role='POWER_MGMT' )   )  ;";  
# echo $sql_get_roles;
  $result_roles=mysqli_query($con,$sql_get_roles);
  $rowcount=mysqli_num_rows($result_roles);
  if ($rowcount>0){
    return true;
  }else{
    return false;
  }
}
?>
