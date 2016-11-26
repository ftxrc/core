<?

function ss_sys_function($id,$t){
	global $system;
	$func=fetchpreg("|s\.([^\(]*)\(|i",$t);
	if ($system["debug"]==true){ $system["debug_log"].="\r\n> Run Function S - ".$func." | ".$t.""; }
	
	if ($func=="echo"){
		if (preg_match("|s\.echo\(([^\)]*)\)|i", $t, $var)){
			return ss_code_variables_string_replace($id,ltrim($var[1]));
		}
	}
}


?>