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
	$system["url_code"]=false;
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
	$s=$_SERVER["REQUEST_URI"];
	$u=explode("/",ltrim($s, '/'));
	while(list($key,$val)=each($u)){
		if (checkpreg("|([^\.]*)\.ssc|i",$val)==true){ $system["url_code"]=true; }
		if ($val==""){ $val="index.ssc"; $system["url_code"]=true; }
		$system["url"].="/".$val."";
	}
	
//##############################################################################################################
//##############################################################################################################-- SS Functions
//##############################################################################################################

	require 'core/sys_functions.php';
	require 'core/code_functions.php';
	require 'core/code_variables.php';
	require 'core/run_linebyline.php';
	
	function ss_runscript($file){
		if (file_exists('.'.$file)){
			$r="";
			
			$t=file_get_contents('.'.$file, FILE_USE_INCLUDE_PATH);
			$t=removeblank($t);
			$t=ss_code_functions_register($t);
			$r=ss_run_linebyline($t);
			
			echo $r;
		}else{
			customError(404,"Cant find file ".$file.".");
		}
	}
	
	//--Check for what page we are loading
	if ($system["url_code"]==true){
		ss_runscript($settings["location_code"].$system["url"]);
	}else{
		$filedownload=$settings["location_code"].$system["url"];
		if (file_exists($filedownload)) {
		    header('Content-Type: application/octet-stream');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize($filedownload));
		    readfile($filedownload);
		    exit;
		}
	}
	
	if ($system["debug"]==true){ echo "<!-- ".$system["debug_log"]." \r\n-->"; }
	
	
	//--Clean up
	unset($ss_variables);
	unset($ss_functions);
	?>