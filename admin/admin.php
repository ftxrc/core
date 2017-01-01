<?php
include ("admin/top.php");

//------------------------------------------Set Vars
if (!isset($_SESSION["admin_loggedin"])){
  $_SESSION["admin_loggedin"]=false;
}

include("functions.php");

//------------------------------------------Check Password Sent In
if (isset($_GET["password"])){
  if ($settings["admin_password"]=="changeme"){
    $_SESSION["admin_loggedin"]=false;
    redirectnow("".$settings["admin_url"]."?page=login&message=You must change the default password in the CONF.JSON file first.");
  }else{
    if ($_GET["password"]==$settings["admin_password"]){
      $_SESSION["admin_loggedin"]=true;
      redirectnow("".$settings["admin_url"]."?page=dash");
    }else{
      $_SESSION["admin_loggedin"]=false;
      redirectnow("".$settings["admin_url"]."?page=login&message=Login Incorrect");
    }
  }
}

//------------------------------------------Get Page Info
if (!isset($_GET["page"])){
  if ($_SESSION["admin_loggedin"]==true){
    redirectnow("".$settings["admin_url"]."?page=dash");
  }else{
    redirectnow("".$settings["admin_url"]."?page=login");
  }
}else{
  $page=makesafe($_GET["page"]);
}

//------------------------------------------Display Messages
if (isset($_GET["message"])){
  echo "<div class=\"form-group\"><label>Hey You!</label>".makesafe($_GET["message"])."</div>";
}

include("page_".$page.".php");

include ("admin/bottom.php");
?>
