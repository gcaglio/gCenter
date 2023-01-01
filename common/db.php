<?php

function getConnection($servername,$username,$password,$dbname){
  $mysqli = new mysqli($servername,$username,$password,$dbname);

  // Check connection
  if ($mysqli -> connect_errno) {
  #  echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
    return NULL;
  }
  return $mysqli;
}
#echo "Connected successfully";
?>
