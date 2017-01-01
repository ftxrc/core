<?php

echo '<div class="form-header"><h1>Update</h1></div><div class="form-content">';
echo '<div class="form-group">Let me check for updates first...</div>';

//--Get current version from GITHUB
$data=json_decode(utf8_encode(file_get_contents('https://raw.githubusercontent.com/codesimplescript/core/master/conf.json')), true); //Fetch Config Data

echo '<div class="form-group">You are running version '.$settings["version"].', and the curent version is '.$data["version"].'</div>';

if (strval($settings["version"])!=strval($data["version"])){
  echo '<div class="form-group"><label for="username">Update Avalable</label>';
  echo '<a href="'.$settings["admin_url"].'?page=update_install">Install Update</a>';
  echo '</div>';
}

echo '</div>';

?>
