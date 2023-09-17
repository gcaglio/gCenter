<?php
session_start();


   if ( isset($_SESSION["successfull_message"]) ){
?>
    <span class="success_message">
      <?php print $_SESSION["successfull_message"]; unset($_SESSION["successfull_message"]); ?>
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



<?php
   if ( isset($_SESSION["message"]) ){
?>
    <span class="info_message">
      <?php print $_SESSION["message"]; unset($_SESSION["message"]); ?>
    </span>
<?php
   }
?>

