<?php

function redirectnow($url){
  header("Location: ".$url."");
  die();
}

function logincheck(){
  global $settings;
  if ($_SESSION["admin_loggedin"]==false){
    redirectnow("".$settings["admin_url"]."?page=login&message=Sorry you need to login first");
  }
}

?>
