<?php

function loginPrint()
{
    ob_start('mb_output_handler');
?>
  <h2>User Login </h2>
  <form name="login" method="post" action="index.php">
   Username: <input type="text" name="username"><br>
   Password: <input type="password" name="password"><br>
   <input type="submit" name="submit" value="Login!">
  </form>
  
 <?php
 
$login = ob_get_contents();
ob_end_clean();
return $login;
 
}
?>