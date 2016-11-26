<?

function ss_run_linebyline($t){
	global $system;
	
	$time_start = microtime_float();
	$r=""; //--return
	$system["id"]=$system["id"]+1;
	$id=$system["id"]; //--process id, used for varible and external function memory duing linebyline
	
	if ($system["debug"]==true){ $system["debug_log"].="\r\n> LineByLine Invoke Start"; }
	
	$v=array();
	$v["function"]=false;
	$v["ran"]=false;
	$v["if"]=false; //--Turn true if in a IF statement
	$v["if_disabled"]=false; //--Turn true if first part of statement is false so the code until end of IF does not run
	$v["backquote"]=false;
	
	$var=array();
	
	$array = preg_split("/\r\n|\n|\r/", $t); //--break up lines into array
	foreach ($array as $l){
		$l=ltrim($l);
		if ($l!=""){
			if ($system["debug"]==true){ $system["debug_log"].="\r\n> Line - \"".$l."\" - IF (".$v["if"].") If disabled (".$v["if_disabled"].")"; }
			
			//--Backquote areas are auto return without processing
			if (strpos($l, '`') !== false && $v["if_disabled"]==false){
				$v["ran"]=true;
				if ($v["backquote"]==false){
					if ($system["debug"]==true){ $system["debug_log"].="\r\n> Backquote ON with backtrack to `"; } $v["backquote"]=true; $r.=substr($l, strpos($l, "`") + 1);
				}else{
					if ($system["debug"]==true){ $system["debug_log"].="\r\n> Backquote OFF with forward check before `"; } $v["backquote"]=false; $r.=strtok($l, "`");
				}
			}else{
				if ($v["backquote"]==true){ $v["backquote"]=false; $v["ran"]=true; $r.=$l; }
			}
			
			//--Standard processing
			if ($v["backquote"]==false && $v["if_disabled"]==false && $v["ran"]==false){
				
				//--Find IF statements
				if (strpos(substr($l, 0, 6), 'if ') !== false){
					$ifcheck=ss_run_linebyline_if($id,$l);
					//--Check if if statement (I know)
					if ($ifcheck!=false && $v["ran"]==false){
						if ($ifcheck=="yes"){
							$v["ran"]=true;
							$v["if"]=true;
							$v["if_disabled"]=false;
						}
						if ($ifcheck=="no"){
							$v["ran"]=true;
							$v["if"]=true;
							$v["if_disabled"]=true;
						}
					}
				}
				
				//--Check if system function
				if (checkpreg("|s\.([^\(]*)\(|i",$l)==true && $v["ran"]==false){
					$r.=ss_sys_function($id,$l);
					$v["ran"]=true;
				}
				
				//--Check if function
				if (checkpreg("|f\.([^\(]*)\(\)|i",$l)==true && $v["ran"]==false){
					$r.=ss_code_function_run(fetchpreg("|f\.([^\(]*)\(\)|i",$l));
					$v["ran"]=true;
				}
				
				//--Check if gv.variable set
				if (checkpreg("|gv\.([^=]*)=|i",$l)==true && $v["ran"]==false){
					$var=fetchpreg("|gv\.([^=]*)=|i",$l);
					$value=ltrim(substr($l, strpos($l, "gv.".$var."=") + strlen("gv.".$var."=")));
					$value=trim($value,'"');
					$value=trim($value,'\'');
					ss_code_variables_save("global",$var,$value);
					$v["ran"]=true;
				}
				
				//--Check if gv.variable add
				if (checkpreg("|gv\.([^\+]*)\+|i",$l)==true && $v["ran"]==false){
					$var=fetchpreg("|gv\.([^\+]*)\+|i",$l);
					$value=ltrim(substr($l, strpos($l, "gv.".$var."+") + strlen("gv.".$var."+")));
					$value=trim($value,'"');
					$value=trim($value,'\'');
					$value=ss_code_variables_get("global",$var)+$value;
					ss_code_variables_save("global",$var,$value);
					$v["ran"]=true;
				}
				
				//--Check if gv.variable take
				if (checkpreg("|gv\.([^-]*)-|i",$l)==true && $v["ran"]==false){
					$var=fetchpreg("|gv\.([^-]*)-|i",$l);
					$value=ltrim(substr($l, strpos($l, "gv.".$var."-") + strlen("gv.".$var."-")));
					$value=trim($value,'"');
					$value=trim($value,'\'');
					$value=ss_code_variables_get("global",$var)-$value;
					ss_code_variables_save("global",$var,$value);
					$v["ran"]=true;
				}
				
				//--Check if v.variable set
				if (checkpreg("|v\.([^=]*)=|i",$l)==true && $v["ran"]==false){
					$var=fetchpreg("|v\.([^=]*)=|i",$l);
					$value=ltrim(substr($l, strpos($l, "v.".$var."=") + strlen("v.".$var."=")));
					$value=trim($value,'"');
					$value=trim($value,'\'');
					ss_code_variables_save($id,$var,$value);
					$v["ran"]=true;
				}
				
				//--Check if v.variable add
				if (checkpreg("|v\.([^\+]*)\+|i",$l)==true && $v["ran"]==false){
					$var=fetchpreg("|v\.([^\+]*)\+|i",$l);
					$value=ltrim(substr($l, strpos($l, "v.".$var."+") + strlen("v.".$var."+")));
					$value=trim($value,'"');
					$value=trim($value,'\'');
					$value=ss_code_variables_get($id,$var)+$value;
					ss_code_variables_save($id,$var,$value);
					$v["ran"]=true;
				}
				
				//--Check if v.variable take
				if (checkpreg("|v\.([^-]*)-|i",$l)==true && $v["ran"]==false){
					$var=fetchpreg("|v\.([^-]*)-|i",$l);
					$value=ltrim(substr($l, strpos($l, "v.".$var."-") + strlen("v.".$var."-")));
					$value=trim($value,'"');
					$value=trim($value,'\'');
					$value=ss_code_variables_get($id,$var)-$value;
					ss_code_variables_save($id,$var,$value);
					$v["ran"]=true;
				}
				
			}
			
			//--IF statement ELSE
			if (strpos($l, 'else') !== false && $v["ran"]==false && $v["if"]==true){
				if ($system["debug"]==true){ $system["debug_log"].="\r\n> LineByLine ELSE"; }
				if ($v["if"]==true){
					if ($v["if_disabled"]==true){
						$v["if_disabled"]=false;
					}else{
						$v["if_disabled"]=true;
					}
				}
			}
			
			//--IF statement END
			if (strpos($l, 'end') !== false && $v["ran"]==false && $v["if"]==true){
				if ($system["debug"]==true){ $system["debug_log"].="\r\n> LineByLine END"; }
				if ($v["if"]==true){
					$v["ran"]=false;
					$v["if_disabled"]=false;
					$v["if"]=false;
				}
			}
		}
		$v["ran"]=false;
	}
	
	$time_end = microtime_float();
	if ($system["debug"]==true){ $system["debug_log"].="\r\n> LineByLine Invoke Finished, took (".round(($time_end-$time_start),2)." seconds)"; }
	return $r;
}

//--Fetch LineByLine IF
function ss_run_linebyline_if($id,$l){
	global $system;
	if ($system["debug"]==true){ $system["debug_log"].="\r\n> Checking For IF In ID Range ".$id.""; }
	$found=false;
	
	//--Match rule (if not a == b)
	if (preg_match("|if not ([^=]*)==(.*)|i", $l, $var) && $found==false){
		if ($system["debug"]==true){ $system["debug_log"].="\r\n> Found IF Statement Match - (if not a == b)"; }
		$found1=ss_code_variables_string_value($id,ltrim($var[1]));
		$found2=ss_code_variables_string_value($id,ltrim($var[2]));
		if ($found1==$found2){
			$found="no";
		}else{
			$found="yes";
		}
	}
	
	//--Match rule (if a false)
	if (preg_match("|if ([^=\s]*) false|i", $l, $var) && $found==false){
		if ($system["debug"]==true){ $system["debug_log"].="\r\n> Found IF Statement Match - (if a false) using var ".ltrim($var[1]).""; }
		$found1=ss_code_variables_string_value($id,ltrim($var[1]));
		$found2="false";
		if ($found1==$found2){
			$found="yes";
		}else{
			$found="no";
		}
	}
	
	//--Match rule (if a true)
	if (preg_match("|if ([^=\s]*) true|i", $l, $var) && $found==false){
		if ($system["debug"]==true){ $system["debug_log"].="\r\n> Found IF Statement Match - (if a true) using var ".ltrim($var[1]).""; }
		$found1=ss_code_variables_string_value($id,ltrim($var[1]));
		$found2="true";
		if ($found1==$found2){
			$found="yes";
		}else{
			$found="no";
		}
	}
	
	//--Match rule (if a == b)
	if (preg_match("|if ([^=]*)==(.*)|i", $l, $var) && $found==false){
		if ($system["debug"]==true){ $system["debug_log"].="\r\n> Found IF Statement Match - (if a == b)"; }
		$found1=ss_code_variables_string_value($id,ltrim($var[1]));
		$found2=ss_code_variables_string_value($id,ltrim($var[2]));
		if ($found1==$found2){
			$found="yes";
		}else{
			$found="no";
		}
	}
	
	//--Match rule (if a >= b)
	if (preg_match("|if ([^>]*)>=([^=]*)|i", $l, $var) && $found==false){
		if ($system["debug"]==true){ $system["debug_log"].="\r\n> Found IF Statement Match - (if a >= b)"; }
		$found1=ss_code_variables_string_value($id,ltrim($var[1]));
		$found2=ss_code_variables_string_value($id,ltrim($var[2]));
		if ($found1>=$found2){
			$found="yes";
		}else{
			$found="no";
		}
	}
	
	//--Match rule (if a <= b)
	if (preg_match("|if ([^<]*)<=([^=]*)|i", $l, $var) && $found==false){
		if ($system["debug"]==true){ $system["debug_log"].="\r\n> Found IF Statement Match - (if a <= b)"; }
		$found1=ss_code_variables_string_value($id,ltrim($var[1]));
		$found2=ss_code_variables_string_value($id,ltrim($var[2]));
		if ($found1<=$found2){
			$found="yes";
		}else{
			$found="no";
		}
	}
	
	//--Match rule (if a > b)
	if (preg_match("|if ([^>]*)>([^=]*)|i", $l, $var) && $found==false){
		if ($system["debug"]==true){ $system["debug_log"].="\r\n> Found IF Statement Match - (if a > b)"; }
		$found1=ss_code_variables_string_value($id,ltrim($var[1]));
		$found2=ss_code_variables_string_value($id,ltrim($var[2]));
		if ($found1>=$found2){
			$found="yes";
		}else{
			$found="no";
		}
	}
	
	//--Match rule (if a < b)
	if (preg_match("|if ([^<]*)<([^=]*)|i", $l, $var) && $found==false){
		if ($system["debug"]==true){ $system["debug_log"].="\r\n> Found IF Statement Match - (if a < b)"; }
		$found1=ss_code_variables_string_value($id,ltrim($var[1]));
		$found2=ss_code_variables_string_value($id,ltrim($var[2]));
		if ($found1<=$found2){
			$found="yes";
		}else{
			$found="no";
		}
	}
	
	if ($system["debug"]==true){ $system["debug_log"].="\r\n> IF Statement Match Result (".$found.")"; }
	return $found;
}

?>