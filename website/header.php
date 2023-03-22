<?php 
session_start();
if ( ! (isset($_SESSION["_CURRENT_USER"]) ) ){
  $_GET["message"]="Session not valid. Please login.";
  header('Location: ./index.php');
  exit;
}
?>
<div class="header_content">
<table width="100%">
<tr><td><h2>gCenter</h2></td><td align="right"><a href="./manage_login.php?action=logout" title="Log Out">Log Out</a></td></tr>
</table>
</div>
