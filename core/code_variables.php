<?

$ss_variables=array();

//--Fetch LineByLine VAR
function ss_code_variables_get($id,$var){
	global $system;
	global $ss_variables;

	if (isset($ss_variables["".$id.""])){
		if (isset($ss_variables["".$id.""]["".$var.""])){
			if ($system["debug"]==true){ $system["debug_log"].="\r\n> LineByLine Var GET #".$id." - ".$var." [".$ss_variables["".$id.""]["".$var.""]."]"; }
			return $ss_variables["".$id.""]["".$var.""];
		}else{
			return false;
		}
	}else{
		return false;
	}
}

//--Register LineByLine VAR
function ss_code_variables_save($id,$var,$value){
	global $system;
	global $ss_variables;

	if ($system["debug"]==true){ $system["debug_log"].="\r\n> LineByLine Var Save #".$id." - ".$var." [".$value."]"; }

	if (isset($ss_variables["".$id.""])){
		$ss_variables["".$id.""]["".$var.""]=$value;
	}else{
		$ss_variables["".$id.""]=array();
		$ss_variables["".$id.""]["".$var.""]=$value;
	}
}

function ss_code_variables_string_replace($id,$l){
	global $system;
	$l=trim_clean($l);

	if (checkpreg("|f\.([A-Za-z0-9_-]*)\(|i",$l)==true){ //--Check if function
		preg_match_all("|f\.([A-Za-z0-9_-]*)\(|i",$l, $got);
		foreach ($got[1] as $func){
			$data=ss_code_function_run($func);
			$l=str_replace("f.".$func."()",$data,$l);
		}
	}

	if (checkpreg("|s\.([A-Za-z0-9_-]*)\(([^\)]*)\)|i",$l)==true){ //--Check if system function
		preg_match_all("|s\.([A-Za-z0-9_-]*)\(([^\)]*)\)|i",$l, $got);
		foreach ($got[0] as $func){
			$data=ss_sys_function($id,$func);
			$l=str_replace($func,$data,$l);
		}
	}

	if (checkpreg("|gv\.([A-Za-z0-9_-]*)|i",$l)==true){
		preg_match_all("|gv\.([A-Za-z0-9_-]*)|i",$l, $got);
		foreach ($got[1] as $var){
			$va=ss_code_variables_get("global",$var);
			if ($va!==false){
				$l=str_replace("gv.".$var."",$va,$l);
			}
		}
	}

	if (checkpreg("|v\.([A-Za-z0-9_-]*)|i",$l)==true){
		preg_match_all("|v\.([A-Za-z0-9_-]*)|i",$l, $got);
			foreach ($got[1] as $var){
				$va=ss_code_variables_get($id,$var);
				if ($va!==false){
					$l=str_replace("v.".$var."",$va,$l);
				}
			}
	}

	return trim_clean($l);
}

function ss_code_variables_string_value($id,$l){
	$l=trim_clean($l);

	if (checkpreg("|f\.([A-Za-z0-9_-]*)\(|i",$l)==true){ //--Check if function
		$func=fetchpreg("|f\.([A-Za-z0-9_-]*)\(|i",$l);
		$data=ss_code_function_run($func);
		$l=$data;
	}

	if (checkpreg("|s\.([A-Za-z0-9_-]*)\(([^\)]*)\)|i",$l)==true){ //--Check if system function
		preg_match_all("|s\.([A-Za-z0-9_-]*)\(([^\)]*)\)|i",$l, $got);
		foreach ($got[0] as $func){
			$data=ss_sys_function($id,$func);
			$l=$data;
		}
	}

	if (checkpreg("|gv\.([A-Za-z0-9_-]*)|i",$l)==true){
		$var=fetchpreg("|gv\.([A-Za-z0-9_-]*)|i",$l);
		$va=ss_code_variables_get("global",$var);
		if ($va!==false){
			$l=$va;
		}
	}

	if (checkpreg("|v\.([A-Za-z0-9_-]*)|i",$l)==true){
		$var=fetchpreg("|v\.([A-Za-z0-9_-]*)|i",$l);
		$va=ss_code_variables_get($id,$var);
		if ($va!==false){
			$l=$va;
		}
	}

	return trim_clean($l);
}

?>
