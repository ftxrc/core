<?

//#########################################################################################################
//######################################################################################################### - SYSTEM FUNCTIONS
//#########################################################################################################
//############################## - $id = ID of the function/area for variable storage
//############################## - $t = The content we are checking for functions
function ss_sys_function($id,$t){
	global $system;

	if (checkpreg("|s\.([A-Za-z0-9_-]*)\(([^\)]*)\)|i",$t)==true){ //--Check if we have a match for s.[A-Za-z0-9_-]()
		preg_match_all("|s\.([A-Za-z0-9_-]*)\(([^\)]*)\)|i",$t, $got); //--Fetch each instance of a function on it's own so we dont mix them up
		foreach ($got[0] as $script){ //--For each found function that matches return only contained patern
			$func=fetchpreg("|s\.([A-Za-z0-9_-]*)\(|i",$script); //--Take that patern that was returned and fetch from it the function name.
			$code=trim_clean(fetchpreg("|s\.".$func."\(([^\)]*)\)|i",$script)); //--Take that patern that was returned and fetch from it the function content.

			if ($system["debug"]==true){ $system["debug_log"].="\r\n> Run Function S - ".$func." | ".$t.""; }

			if ($func=="echo"){
				return ss_code_variables_string_replace($id,$code);
			}

			if ($func=="run"){
				if (file_exists($system["runpath"].$code)){
					if (!is_dir($system["runpath"].$code)){
						return ss_run_linebyline(file_get_contents($system["runpath"].$code, FILE_USE_INCLUDE_PATH));
					}else{
						return false;
					}
				}else{
					return false;
				}
			}

		}
	}
}

//#########################################################################################################
//######################################################################################################### - SYSTEM FUNCTIONS PRE RUN
//#########################################################################################################
//######################################################################################################### - Pre run runs befor the line by line for things like include
//############################## - $t = The content we are checking for functions
function ss_sys_function_prerun($t){
	global $system;

	if (checkpreg("|s\.include\(([^\)]*)\)|i",$t)==true){
		$code_org=fetchpreg("|s\.include\(([^\)]*)\)|i",$t);
		$code=trim_clean($code_org);
		if (file_exists($system["runpath"].$code)){
			if (!is_dir($system["runpath"].$code)){
				$data=file_get_contents($system["runpath"].$code, FILE_USE_INCLUDE_PATH);
				$t=str_replace("s.include(".$code_org.")",$data,$t);
			}
		}
	}

	return $t;
}

?>
