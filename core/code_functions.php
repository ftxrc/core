<?

$ss_functions=array();
$ss_functions_open=array();

function ss_code_functions_register($t){
	global $system;
	global $ss_functions;
	global $ss_functions_open;
	
	$n="";
	$f="";
	$f_reg=false;
	$f_reg_run=false;
	$name=false;
	$array = preg_split("/\r\n|\n|\r/", $t);
	foreach ($array as $l){
		
		if (preg_match("|f\.([^\{]*)\{|i", $l, $var)){
			$name=$var[1];
			$f_reg_run=true;
			$f_reg=true;
			if ($system["debug"]==true){ $system["debug_log"].="\r\n> Register Function Start - Found function with name ".$name.""; }
		}
		
		if ($f_reg==true){
			if (strpos($l, '}') !== false){
				//--Reigster function now
				if ($system["debug"]==true){ $system["debug_log"].="\r\n> Register Function End - End of function found, registered ".$name.""; }
				$ss_functions["".$name.""]=removeblank($f);
				$ss_functions_open["".$name.""]=0;
				$f_reg_run=true;
				$f_reg=false;
				$name=false;
				$f="";
			}
		}
		
		//--place new line outout
		if ($f_reg==false && $f_reg_run==false){
			$n.="\r\n".$l;
		}
		
		//--place new line function
		if ($f_reg==true && $f_reg_run==false){
			$f.="\r\n".$l;
		}
		
		$f_reg_run=false;
	}
	
	return $n;
}

function ss_code_function_run($func){
	global $system;
	global $settings;
	global $ss_functions;
	global $ss_functions_open;
	
	if (isset($ss_functions["".$func.""])){
		if ($ss_functions_open["".$func.""]<=$settings["settings_function_loopmax"]){
			if ($system["debug"]==true){ $system["debug_log"].="\r\n> Run Function F - ".$func.""; }
			$ss_functions_open["".$func.""]++;
			return ss_run_linebyline($ss_functions["".$func.""]);
			$ss_functions_open["".$func.""]--;
		}else{
			if ($system["debug"]==true){ $system["debug_log"].="\r\n> Run Function F - ".$func." LIMIT HIT OF (".$settings["settings_function_loopmax"].")"; }
		}
	}else{
		if ($system["debug"]==true){ $system["debug_log"].="\r\n> Failed To Run Function F - ".$func.""; }
		return "";
	}
}


?>