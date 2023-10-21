<?php
require_once __DIR__ ."/../conf/eventlog.php";

if(!isset($_SESSION)) session_start();

function logEventInfo($con, $resource, $additional_notes ){
  return logEvent($con,"INFO",$resource,$additional_notes);
}

function logEventError($con, $resource, $additional_notes ){
  return logEvent($con,"ERROR",$resource,$additional_notes);
}

function logEventLoginSuccessful($con, $resource, $additional_notes ){
  print "A";
  return logEvent($con,"LOGIN_OK",$resource,$additional_notes);
}

function logEventLoginError($con, $resource, $additional_notes ){
  return logEvent($con,"LOGIN_ERR",$resource,$additional_notes);
}

function logEventLogout($con, $resource, $additional_notes ){
  return logEvent($con,"LOGOUT",$resource,$additional_notes);
}



function logEventAddHostEsxiSuccessful($con, $resource, $additional_notes ){
  return logEvent($con,"ADD_HOST_E_OK",$resource,$additional_notes);
}

function logEventAddHostEsxiError($con, $resource, $additional_notes ){
  return logEvent($con,"ADD_HOST_E_ERR",$resource,$additional_notes);
}

function logEventDeleteHostEsxiSuccessful($con, $resource, $additional_notes ){
  return logEvent($con,"DEL_HOST_E_OK",$resource,$additional_notes);
}

function logEventDeleteHostEsxiError($con, $resource, $additional_notes ){
  return logEvent($con,"DEL_HOST_E_ERR",$resource,$additional_notes);
}


function logEventAddHostHypervSuccessful($con, $resource, $additional_notes ){
  return logEvent($con,"ADD_HOST_H_OK",$resource,$additional_notes);
}

function logEventAddHostHypervError($con, $resource, $additional_notes ){
  return logEvent($con,"ADD_HOST_H_ERR",$resource,$additional_notes);
}

function logEventDeleteHostHypervSuccessful($con, $resource, $additional_notes ){
  return logEvent($con,"DEL_HOST_H_OK",$resource,$additional_notes);
}

function logEventDeleteHostHypervError($con, $resource, $additional_notes ){
  return logEvent($con,"DEL_HOST_H_ERR",$resource,$additional_notes);
}





function getIPAddress() {
    //whether ip is from the share internet
     if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    //whether ip is from the proxy
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
     }
//whether ip is from the remote address
    else{
             $ip = $_SERVER['REMOTE_ADDR'];
     }
     return $ip;
}


function logEvent($con, $severity, $resource, $additional_notes ){

  if ( ! (isset($_SESSION["_CURRENT_USER"]) ) ){
    return;
  }

  $current_user=$_SESSION["_CURRENT_USER"];
  $event=$severity;
  $ip = getIPAddress();
  $sql_insert_event="insert into events values ( NOW(), '".mysqli_real_escape_string($con,$current_user)."','".mysqli_real_escape_string($con,$ip)."','".mysqli_real_escape_string($con,$event)."','".mysqli_real_escape_string($con,$resource)."','".mysqli_real_escape_string($con,$additional_notes)."' );";
# echo $sql_get_roles;
  $result_insert=mysqli_query($con,$sql_insert_event);

  purgeEvents($con);

  if ($result_insert){
    return true;
  }else{
    return false;
  }


}


function purgeEvents($con){
  global $event_log_retention_days;
  $sql_purge="delete from events where timestamp < subdate(NOW(), INTERVAL ".mysqli_real_escape_string($con,$event_log_retention_days)." DAY);";
  mysqli_query($con,$sql_purge);
}

?>
