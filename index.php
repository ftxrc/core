<?php
//##############################################################################################################
//##############################################################################################################-- BASE Functions
//##############################################################################################################
	
	function codegenerate($length=8){
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$rs="";
		for ($i = 0; $i < $length; $i++){ $rs .= $characters[rand(0, strlen($characters) - 1)]; }
		return $rs;
	}
	
	function fetchpreg($fetch,$data){
		if (preg_match($fetch, $data, $var)){
			return $var[1];
		}
	}
	
	function checkpreg($fetch,$data){
		if (preg_match($fetch, $data, $var)){
			return true;
		}else{
			return false;
		}
	}
	
	function microtime_float(){
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	
	function removeblank($s){
		return preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $s);
	}
	
	function customError($errno, $errstr) {
		echo "<b>Error: </b> in script, check your log files.<BR>".$errno." - ".$errstr."<BR><br>";
		echo "Ending Script";
		die();
	}
	
	function makesafe($d,$type="basic"){
		$d = str_replace("|","&#124;",$d);
		$d = str_replace("\\","&#92;",$d);
		$d = str_replace("(c)","&#169;",$d);
		$d = str_replace("(r)","&#174;",$d);
		$d = str_replace("\"","&#34;",$d);
		$d = str_replace("'","&#39;",$d);
		$d = str_replace("<","&#60;",$d);
		$d = str_replace(">","&#62;",$d);
		$d = str_replace("`","&#96;",$d);
		return $d;
	}
	
//##############################################################################################################
//##############################################################################################################-- Startup, Arrays
//##############################################################################################################
	error_reporting(0);
	ini_set('display_errors', '0');
	set_error_handler("customError");
	session_name('coss');
	$system=array();
	$settings="";
	$system["url"]="";
	$system["debug"]=true;
	$system["debug_log"]="";
	$system["id"]=0;
	$cache=array();
	$settings=json_decode(utf8_encode(file_get_contents('./conf.json', FILE_USE_INCLUDE_PATH)), true); //Fetch Config Data
	if (isset($_COOKIE["coss"])){
		$system["session"]=$_COOKIE["coss"];
	}else{
		$system["session"]=codegenerate(40);
	}
	session_id($system["session"]);
	session_start();
	ob_start("ob_gzhandler");
	header('Access-Control-Allow-Origin: *'); 
	ini_set('date.timezone', 'America/New_York');
	header('X-Powered-By: SimpleScript');
	
	//--Break down URL to get file needed
	$s=str_replace(".ssc","",$_SERVER["REQUEST_URI"]);
	$u=explode("/",ltrim($s, '/'));
	while(list($key,$val)=each($u)){
		if ($val==""){ $val="index"; }
		$system["url"].="/".$val."";
	}
	
//##############################################################################################################
//##############################################################################################################-- SS Functions
//##############################################################################################################
	
	$ss_functions=array();
	
	function ss_function_system($id,$t){
		global $system;
		$func=fetchpreg("|s\.([^\(]*)\(|i",$t);
		if ($system["debug"]==true){ $system["debug_log"].="\r\n> Run Function S - ".$func." | ".$t.""; }
		
		if ($func=="echo"){
			if (preg_match("|s\.echo\(([^\)]*)\)|i", $t, $var)){
				if ($system["debug"]==true){ $system["debug_log"].="\r\n> Run Function S - ECHO - ".$var[1]." - ".ss_input_replacevar($id,ltrim($var[1])).""; }
				return ss_input_replacevar($id,ltrim($var[1]));
			}
		}
	}
	
	function ss_functions_register($t){
		global $system;
		global $ss_functions;
		
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
	
	function ss_functions_run($func){
		global $system;
		global $ss_functions;
		if (isset($ss_functions["".$func.""])){
			if ($system["debug"]==true){ $system["debug_log"].="\r\n> Run Function F - ".$func.""; }
			return ss_linebyline($ss_functions["".$func.""]);
		}else{
			if ($system["debug"]==true){ $system["debug_log"].="\r\n> Failed To Run Function F - ".$func.""; }
			return "";
		}
	}
	
	function ss_linebyline($t){
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
						$ifcheck=ss_linebyline_if($id,$l);
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
						$r.=ss_function_system($id,$l);
						$v["ran"]=true;
					}
					
					//--Check if function
					if (checkpreg("|f\.([^\(]*)\(\)|i",$l)==true && $v["ran"]==false){
						$r.=ss_functions_run(fetchpreg("|f\.([^\(]*)\(\)|i",$l));
						$v["ran"]=true;
					}
					
					//--Check if gv.variable set
					if (checkpreg("|gv\.([^=]*)=|i",$l)==true && $v["ran"]==false){
						$var=fetchpreg("|gv\.([^=]*)=|i",$l);
						$value=ltrim(substr($l, strpos($l, "gv.".$var."=") + strlen("gv.".$var."=")));
						$value=trim($value,'"');
						$value=trim($value,'\'');
						ss_linebyline_var_save("global",$var,$value);
						$v["ran"]=true;
					}
					
					//--Check if gv.variable add
					if (checkpreg("|gv\.([^\+]*)\+|i",$l)==true && $v["ran"]==false){
						$var=fetchpreg("|gv\.([^\+]*)\+|i",$l);
						$value=ltrim(substr($l, strpos($l, "gv.".$var."+") + strlen("gv.".$var."+")));
						$value=trim($value,'"');
						$value=trim($value,'\'');
						$value=ss_linebyline_var_get("global",$var)+$value;
						ss_linebyline_var_save("global",$var,$value);
						$v["ran"]=true;
					}
					
					//--Check if v.variable set
					if (checkpreg("|v\.([^=]*)=|i",$l)==true && $v["ran"]==false){
						$var=fetchpreg("|v\.([^=]*)=|i",$l);
						$value=ltrim(substr($l, strpos($l, "v.".$var."=") + strlen("v.".$var."=")));
						$value=trim($value,'"');
						$value=trim($value,'\'');
						ss_linebyline_var_save($id,$var,$value);
						$v["ran"]=true;
					}
					
					//--Check if v.variable add
					if (checkpreg("|v\.([^\+]*)\+|i",$l)==true && $v["ran"]==false){
						$var=fetchpreg("|v\.([^\+]*)\+|i",$l);
						$value=ltrim(substr($l, strpos($l, "v.".$var."+") + strlen("v.".$var."+")));
						$value=trim($value,'"');
						$value=trim($value,'\'');
						$value=ss_linebyline_var_get($id,$var)+$value;
						ss_linebyline_var_save($id,$var,$value);
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
	function ss_linebyline_if($id,$l){
		global $system;
		if ($system["debug"]==true){ $system["debug_log"].="\r\n> Checking For IF In ID Range ".$id.""; }
		$found=false;
		
		//--Match rule (if not a == b)
		if (preg_match("|if not ([^=]*)==(.*)|i", $l, $var) && $found==false){
			if ($system["debug"]==true){ $system["debug_log"].="\r\n> Found IF Statement Match - (if not a == b)"; }
			$found1=ss_input_checkvar($id,ltrim($var[1]));
			$found2=ss_input_checkvar($id,ltrim($var[2]));
			if ($found1==$found2){
				$found="no";
			}else{
				$found="yes";
			}
		}
		
		//--Match rule (if a false)
		if (preg_match("|if ([^=\s]*) false|i", $l, $var) && $found==false){
			if ($system["debug"]==true){ $system["debug_log"].="\r\n> Found IF Statement Match - (if a false) using var ".ltrim($var[1]).""; }
			$found1=ss_input_checkvar($id,ltrim($var[1]));
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
			$found1=ss_input_checkvar($id,ltrim($var[1]));
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
			$found1=ss_input_checkvar($id,ltrim($var[1]));
			$found2=ss_input_checkvar($id,ltrim($var[2]));
			if ($found1==$found2){
				$found="yes";
			}else{
				$found="no";
			}
		}
		
		//--Match rule (if a >= b)
		if (preg_match("|if ([^>]*)>=([^=]*)|i", $l, $var) && $found==false){
			if ($system["debug"]==true){ $system["debug_log"].="\r\n> Found IF Statement Match - (if a >= b)"; }
			$found1=ss_input_checkvar($id,ltrim($var[1]));
			$found2=ss_input_checkvar($id,ltrim($var[2]));
			if ($found1>=$found2){
				$found="yes";
			}else{
				$found="no";
			}
		}
		
		//--Match rule (if a <= b)
		if (preg_match("|if ([^<]*)<=([^=]*)|i", $l, $var) && $found==false){
			if ($system["debug"]==true){ $system["debug_log"].="\r\n> Found IF Statement Match - (if a <= b)"; }
			$found1=ss_input_checkvar($id,ltrim($var[1]));
			$found2=ss_input_checkvar($id,ltrim($var[2]));
			if ($found1<=$found2){
				$found="yes";
			}else{
				$found="no";
			}
		}
		
		//--Match rule (if a > b)
		if (preg_match("|if ([^>]*)>([^=]*)|i", $l, $var) && $found==false){
			if ($system["debug"]==true){ $system["debug_log"].="\r\n> Found IF Statement Match - (if a > b)"; }
			$found1=ss_input_checkvar($id,ltrim($var[1]));
			$found2=ss_input_checkvar($id,ltrim($var[2]));
			if ($found1>=$found2){
				$found="yes";
			}else{
				$found="no";
			}
		}
		
		//--Match rule (if a < b)
		if (preg_match("|if ([^<]*)<([^=]*)|i", $l, $var) && $found==false){
			if ($system["debug"]==true){ $system["debug_log"].="\r\n> Found IF Statement Match - (if a < b)"; }
			$found1=ss_input_checkvar($id,ltrim($var[1]));
			$found2=ss_input_checkvar($id,ltrim($var[2]));
			if ($found1<=$found2){
				$found="yes";
			}else{
				$found="no";
			}
		}
		
		if ($system["debug"]==true){ $system["debug_log"].="\r\n> IF Statement Match Result (".$found.")"; }
		return $found;
	}
	
	$ss_linebyline_var=array();
	
	//--Fetch LineByLine VAR
	function ss_linebyline_var_get($id,$var){
		global $system;
		global $ss_linebyline_var;
		
		if (isset($ss_linebyline_var["".$id.""])){
			if (isset($ss_linebyline_var["".$id.""]["".$var.""])){
				if ($system["debug"]==true){ $system["debug_log"].="\r\n> LineByLine Var GET #".$id." - ".$var." [".$ss_linebyline_var["".$id.""]["".$var.""]."]"; }
				return $ss_linebyline_var["".$id.""]["".$var.""];
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	//--Register LineByLine VAR
	function ss_linebyline_var_save($id,$var,$value){
		global $system;
		global $ss_linebyline_var;
		
		if ($system["debug"]==true){ $system["debug_log"].="\r\n> LineByLine Var Save #".$id." - ".$var." [".$value."]"; }
		
		if (isset($ss_linebyline_var["".$id.""])){
			$ss_linebyline_var["".$id.""]["".$var.""]=$value;
		}else{
			$ss_linebyline_var["".$id.""]=array();
			$ss_linebyline_var["".$id.""]["".$var.""]=$value;
		}
	}
	
	function ss_input_replacevar($id,$l){
		global $system;
		$l=trim($l,'"');
		$l=trim($l,'\'');
		
		if (checkpreg("|gv\.([A-Za-z0-9_-]*)|i",$l)==true){
			$var=fetchpreg("|gv\.([A-Za-z0-9_-]*)|i",$l);
			$va=ss_linebyline_var_get("global",$var);
			if ($va!==false){
				$l=str_replace("gv.".$var."",$va,$l);
			}
		}
		
		if (checkpreg("|v\.([A-Za-z0-9_-]*)|i",$l)==true){
			$var=fetchpreg("|v\.([A-Za-z0-9_-]*)|i",$l);
			$va=ss_linebyline_var_get($id,$var);
			if ($va!==false){
				$l=str_replace("v.".$var."",$va,$l);
			}
		}
		
		return $l;
	}
	
	function ss_input_checkvar($id,$l){
		$l=trim($l,'"');
		$l=trim($l,'\'');
		$found=$l;
		
		if (checkpreg("|gv\.([A-Za-z0-9_-]*)|i",$l)==true){
			$var=fetchpreg("|gv\.([A-Za-z0-9_-]*)|i",$l);
			$va=ss_linebyline_var_get("global",$var);
			if ($va!==false){
				$found=$va;
			}
		}
		
		if (checkpreg("|v\.([A-Za-z0-9_-]*)|i",$l)==true){
			$var=fetchpreg("|v\.([A-Za-z0-9_-]*)|i",$l);
			$va=ss_linebyline_var_get($id,$var);
			if ($va!==false){
				$found=$va;
			}
		}
		
		return $found;
	}
	
	function ss_runscript($file){
		if (file_exists('.'.$file)){
			$r="";
			
			$t=file_get_contents('.'.$file, FILE_USE_INCLUDE_PATH);
			$t=removeblank($t);
			$t=ss_functions_register($t);
			$r.=ss_linebyline($t);
			
			echo $r;
		}else{
			customError(404,"Cant find file ".$file.".");
		}
	}
	
	ss_runscript($settings["location_code"].$system["url"].".ssc");
	
	if ($system["debug"]==true){ echo "<!-- ".$system["debug_log"]." \r\n-->"; }
	
	
	//--Clean up
	$ss_linebyline_var=false;
	?>