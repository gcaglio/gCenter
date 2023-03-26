<?php
require_once "../common/db.php";
require_once "../conf/db.php";
session_start();
# check login and authenticate
$con=getConnection($servername,$username,$password,$dbname);


if (  isset($_POST["uname"]) && isset($_POST["passwd"]) && "login"==$_POST["action"]  ) {
  $uname=mysqli_real_escape_string($con,$_POST["uname"]);
  $passwd=mysqli_real_escape_string($con,$_POST["passwd"]);

  $sql_login="select * from users where username='$uname' and password=md5('$passwd') ; ";

  $result_login=mysqli_query($con,$sql_login);
  if ( mysqli_num_rows($result_login)>0 ){	  
    $row = $result_login->fetch_assoc();
    $_SESSION["_CURRENT_USER"]=$row["username"];
    $_SESSION["_CURRENT_ROLE"]=$row["role"];
    $_SESSION["message"]="Welcome ".$row["username"]." .";
    header('Location: ./home.php');
    exit;
  } else {
    $_SESSION["message"]="Cannot complete login due to an incorrect user name or password.";
  }
  header('Location: ./index.php');
  exit;
}else if ("logout"==$_GET["action"]){
  unset($_SESSION["_CURRENT_USER"]);
  unset($_SESSION["_CURRENT_ROLE"]);
  $_SESSION["message"]="User successfully logged out.";
  header('Location: ./index.php');
  exit;
}else{
  $_SESSION["message"]="Wrong invocation. Please login.";
  header('Location: ./index.php');
  exit;
}
?>
