<?php
  ini_set('memory_limit','60M');
  set_time_limit(1200);
  $today = date("m.d.y");

	function nhlog($line){
		error_log("- ".$line."\n", 3, "system_update_".$today.".log");
	}
	function percent($num_amount, $num_total) {
		$count1 = $num_amount / $num_total;
		$count2 = $count1 * 100;
		$count = number_format($count2, 0);
		return $count;
	}
	nhlog("** Install Started **");

  if (!isset($_GET["part"])){
    $source = "https://github.com/codesimplescript/core/archive/master.zip";
    $dest = "installme.zip";
    copy($source, $dest);
  }

	$path = 'installme.zip';
	$zip = new ZipArchive;
	if (isset($_GET["part"])){
		$i=$_GET["part"];
	}else{
		$i=0;
	}
	$p=100;
	$d=false;
	if ($zip->open($path) === true){
		$files=$zip->numFiles;
		while ($p >= 1){
			$filename = $zip->getNameIndex($i);
			if ($filename!=""){
				$fileinfo = pathinfo($filename);
				$whatIWant = substr($filename, strpos($filename, "/") + 1);
        if ($whatIWant!="update.php" && $whatIWant!="page_update_install.php"  && $whatIWant!="conf.json" && $whatIWant!="admin/admin.php" && $whatIWant!=""){
          if (!preg_match("|www|i", $whatIWant, $var)){
            $copy=false;
            if (file_exists($whatIWant)){
              if (is_dir($whatIWant)){
                //--Already made folder
              }else{
                unlink($whatIWant);
                $copy=true;
              }
            }else{
              $copy=true;
            }
            if ($copy==true){
              copy("zip://".$path."#".$filename, $whatIWant);
              nhlog("File copy ".$filename." with ID ".$i.", part ".$p.".");
            }
          }else{
            nhlog("File skip ".$filename." with ID ".$i.", part ".$p.". - Demo Web Files, we don't update this via the updater.");
          }
        }else{
          nhlog("File skip ".$filename." with ID ".$i.", part ".$p.". - If this needs to be updated you must update manualy");
        }
				$p=$p-1;
				$i=$i+1;
			}else{
				$p=0;
				$d=true;
				nhlog("All files installed with final file count of ".$i.".");
			}
		}
		$zip->close();
		if ($d==true){
			echo "<h1>We have just finished updating</h1><script>window.setTimeout(function(){ window.location.href = \"".$settings["admin_url"]."?page=update_done\"; }, 100);</script>";
			unlink('installme.zip');
			nhlog("Clear install files.");
		}else{
			echo "<h1>Install in progress</h1><h2>Leave this page open, we are installing files...</h2><BR><BR><h3>".percent($i,$files)."%</h3></div><script>window.setTimeout(function(){ window.location.href = \"".$settings["admin_url"]."?page=update_install&part=".$i."\"; }, 100);</script>";
		}
	}else{
		echo "Doh! We couldn't open $file";
		nhlog("Not able to open installer zip file.");
	}
?>
