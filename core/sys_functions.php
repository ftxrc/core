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
			$code=fetchpreg("|s\.".$func."\(([^\)]*)\)|i",$script); //--Take that patern that was returned and fetch from it the function content.

			$code_part=ss_sys_function_inputarray($id,$code);//--break comma seperate parts into an array
			$code=ss_code_variables_string_replace($id,trim_clean($code));

			if ($system["debug"]==true){ $system["debug_log"].="\r\n> Run Function S - ".$func." | ".$t.""; }

			//-------------------------------------------------------------- ECHO
			if ($func=="echo"){
				return $code;
			}

			//-------------------------------------------------------------- STRING_LENGTH
			if ($func=="string_length"){
				return strlen($code);
			}

			//-------------------------------------------------------------- STRING_WORD_COUNT
			if ($func=="string_word_count"){
				return str_word_count($code);
			}

			//-------------------------------------------------------------- STRING_INVERT
			if ($func=="string_invert"){
				return strrev($code);
			}

			//-------------------------------------------------------------- STRING_WORD_UPPERCASE
			if ($func=="string_word_uppercase"){
				return ucwords($code);
			}

			//-------------------------------------------------------------- STRING_UPPERCASE
			if ($func=="string_uppercase"){
				return strtoupper($code);
			}

			//-------------------------------------------------------------- STRING_LOWERCASE
			if ($func=="string_lowercase"){
				return strtolower($code);
			}

			//-------------------------------------------------------------- STRING_LOWERCASE
			if ($func=="string_lowercase"){
				return strtolower($code);
			}

			//-------------------------------------------------------------- STRING_REPLACE
			if ($func=="string_replace"){
				return str_replace($code_part[0],$code_part[1],$code_part[2]);
			}

			//-------------------------------------------------------------- STRING_TRIM
			if ($func=="string_trim"){
				return substr($code_part[0],$code_part[1]);
			}

			//-------------------------------------------------------------- STRING_REPEAT
			if ($func=="string_repeat"){
				return str_repeat($code_part[0],0,$code_part[1]);
			}

			//-------------------------------------------------------------- FILE_COPY
			if ($func=="file_copy"){
				if (file_exists($system["runpath"].$code_part[0])){
					if (!is_dir($system["runpath"].$code_part[0])){
						if (copy($system["runpath"].$code_part[0],$system["runpath"].$code_part[1])){
							return true;
						}else{
							return false;
							log_error("System Function (file_copy): unable to copy file",$t);
						}
					}else{
						log_error("System Function (file_copy): cant run file copy on a folder",$t);
						return false;
					}
				}else{
					log_error("System Function (file_copy): cant original find file given",$t);
					return false;
				}
			}

			//-------------------------------------------------------------- FILE_EXISTS
			if ($func=="file_exists"){
				if (file_exists($system["runpath"].$code_part[0])){
					if (!is_dir($system["runpath"].$code_part[0])){
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}

			//-------------------------------------------------------------- FILE_DELETE
			if ($func=="file_delete"){
				if (file_exists($system["runpath"].$code_part[0])){
					if (!is_dir($system["runpath"].$code_part[0])){
						if (unlink($system["runpath"].$code_part[0])){
							return true;
						}else{
							log_error("System Function (file_delete): unable to delete file",$t);
							return false;
						}
					}else{
						log_error("System Function (file_delete): cant run file delete on a folder",$t);
						return false;
					}
				}else{
					log_error("System Function (file_delete): cant find file given",$t);
					return false;
				}
			}

			//-------------------------------------------------------------- FILE_WRITE
			if ($func=="file_write"){
				if (file_exists($system["runpath"].$code_part[0])){
					if (!is_dir($system["runpath"].$code_part[0])){
						if (file_put_contents($system["runpath"].$code_part[0], $code_part[1])){
							return true;
						}else{
							log_error("System Function (file_write): unable to write to the file",$t);
							return false;
						}
					}else{
						log_error("System Function (file_write): cant write on a folder",$t);
						return false;
					}
				}else{
					log_error("System Function (file_write): cant find file given",$t);
					return false;
				}
			}

			//-------------------------------------------------------------- FILE_ADD
			if ($func=="file_add"){
				if (file_exists($system["runpath"].$code_part[0])){
					if (!is_dir($system["runpath"].$code_part[0])){
						if (file_put_contents($system["runpath"].$code_part[0], $code_part[1], FILE_APPEND | LOCK_EX)){
							return true;
						}else{
							log_error("System Function (file_add): unable to add content to the file",$t);
							return false;
						}
					}else{
						log_error("System Function (file_add): cant write on a folder",$t);
						return false;
					}
				}else{
					log_error("System Function (file_add): cant find file given",$t);
					return false;
				}
			}

			//-------------------------------------------------------------- FILE_CREATE
			if ($func=="file_create"){
				if (!file_exists($system["runpath"].$code_part[0])){
					$file = fopen($system["runpath"].$code_part[0], 'w') or log_error("System Function (file_create): file create failed",$t);
					fclose($file);
					return true;
				}else{
					log_error("System Function (file_create): we already have a file with this name",$t);
					return false;
				}
			}

			//-------------------------------------------------------------- FILE_READ
			if ($func=="file_read"){
				if (file_exists($system["runpath"].$code_part[0])){
					if (!is_dir($system["runpath"].$code_part[0])){
						$data=file_get_contents($system["runpath"].$code_part[0]);
						return $data;
					}else{
						log_error("System Function (file_read): cant read folder path",$t);
						return false;
					}
				}else{
					log_error("System Function (file_read): cant find file to read",$t);
					return false;
				}
			}

			//-------------------------------------------------------------- FILE_SIZE
			if ($func=="file_size"){
				if (file_exists($system["runpath"].$code_part[0])){
					if (!is_dir($system["runpath"].$code_part[0])){
						$data=filesize($system["runpath"].$code_part[0]);
						return $data;
					}else{
						log_error("System Function (file_size): cant read folder path",$t);
						return false;
					}
				}else{
					log_error("System Function (file_size): cant find file to read",$t);
					return false;
				}
			}

			//-------------------------------------------------------------- FILE_RENAME
			if ($func=="file_rename"){
				if (file_exists($system["runpath"].$code_part[0])){
					if (!is_dir($system["runpath"].$code_part[0])){
						if (!file_exists($system["runpath"].$code_part[1])){
							if (rename($system["runpath"].$code_part[0],$system["runpath"].$code_part[1])){
								return true;
							}else{
								log_error("System Function (folder_rename): unable to rename file",$t);
								return false;
							}
						}else{
							log_error("System Function (folder_rename): a folder or file already has the new name",$t);
							return false;
						}
					}else{
						log_error("System Function (folder_rename): filename given is folder not a file",$t);
						return false;
					}
				}else{
					log_error("System Function (folder_rename): no folder found to renmae",$t);
					return false;
				}
			}

			//-------------------------------------------------------------- FOLDER_DELETE
			if ($func=="folder_delete"){
				if (file_exists($system["runpath"].$code_part[0])){
					if (is_dir($system["runpath"].$code_part[0])){
						if (rmdir($system["runpath"].$code_part[0])){
							return true;
						}else{
							log_error("System Function (folder_delete): unable to delete folder",$t);
							return false;
						}
					}else{
						log_error("System Function (folder_delete): cant run folder delete on a file",$t);
						return false;
					}
				}else{
					log_error("System Function (folder_delete): cant find folder given",$t);
					return false;
				}
			}

			//-------------------------------------------------------------- FOLDER_CREATE
			if ($func=="folder_create"){
				if (!file_exists($system["runpath"].$code_part[0])){
					if (!is_dir($system["runpath"].$code_part[0])){
						if (mkdir($system["runpath"].$code_part[0])){
							return true;
						}else{
							log_error("System Function (folder_create): unable to create folder",$t);
							return false;
						}
					}else{
						log_error("System Function (folder_create): a folder already has this name",$t);
						return false;
					}
				}else{
					log_error("System Function (folder_create): a file already has this name",$t);
					return false;
				}
			}

			//-------------------------------------------------------------- FOLDER_RENAME
			if ($func=="folder_rename"){
				if (file_exists($system["runpath"].$code_part[0])){
					if (is_dir($system["runpath"].$code_part[0])){
						if (!file_exists($system["runpath"].$code_part[1])){
							if (rename($system["runpath"].$code_part[0],$system["runpath"].$code_part[1])){
								return true;
							}else{
								log_error("System Function (folder_rename): unable to rename folder",$t);
								return false;
							}
						}else{
							log_error("System Function (folder_rename): a folder or file already has the new name",$t);
							return false;
						}
					}else{
						log_error("System Function (folder_rename): filename given is not a folder",$t);
						return false;
					}
				}else{
					log_error("System Function (folder_rename): no folder found to renmae",$t);
					return false;
				}
			}

			//-------------------------------------------------------------- FOLDER_EXISTS
			if ($func=="folder_exists"){
				if (file_exists($system["runpath"].$code_part[0])){
					if (is_dir($system["runpath"].$code_part[0])){
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}

			//-------------------------------------------------------------- ARRAY
			if ($func=="array"){
				$onnow=0;
				$namepart="array";
				foreach ($code_part as $parts){
					if ($onnow==0){
						$namepart=$parts;
					}else{
						ss_code_variables_save($id,$namepart."_".$onnow,$parts);
					}
					$onnow+=1;
				}
			}

			//-------------------------------------------------------------- TEMPLATE
			if ($func=="template"){
				ss_template_set($code);
			}

			//-------------------------------------------------------------- RUN
			if ($func=="run"){
				if (file_exists($system["runpath"].$code)){
					if (!is_dir($system["runpath"].$code)){
						return ss_run_linebyline(file_get_contents($system["runpath"].$code, FILE_USE_INCLUDE_PATH));
					}else{
						log_error("System Function (run): cant run folder path",$t);
						return false;
					}
				}else{
					log_error("System Function (run): cant find file to run",$t);
					return false;
				}
			}

		}
	}
}

//#########################################################################################################
//######################################################################################################### - SYSTEM FUNCTIONS INPUTARRAY
//#########################################################################################################
//######################################################################################################### - Splits comma seperated parts into an array for use by muti part detect functions
//############################## - $id = ID of the function/area for variable storage
//############################## - $code = The content we are seperating

function ss_sys_function_inputarray($id,$code){
	$code_split="{{".$code."}}";
	$code_part=array();
	$string=preg_match_all("/(?:(?:\"(?:\\\\\"|[^\"])+\")|(?:'(?:\\\'|[^'])+'))/is",$code_split,$match); //--Match quoted areas and skipping slashed out quites matching all ex ('yay it's working')
	$temp_part=array(); //--We are storing "" matched areas for later so we dont capture string commas for replacement
	foreach ($match[0] as $part){ //--Save all quote seperated parts and save for later
		$key=codegenerate(20); //--Generate key so we can recall later
		$temp_part[$key]=trim_clean($part); //--Save with key in our temp parts array
		$code_split=str_replace($part,"{{key:".$key."}}",$code_split); //--Place temp code in our string
	}
	//--Comma split the string, and then clean it from blank areas
	$code_split=str_replace(",","}}{{",$code_split);
	$code_split=str_replace("}}}}","}}",$code_split);
	$code_split=str_replace("{{{{","{{",$code_split);
	//--Place Content back in
	preg_match_all("|\{\{key:([A-Za-z0-9]*)\}\}|i",$code_split, $match); //--Fetch each instance of a function on it's own so we dont mix them up
	foreach ($match[1] as $keycode){
		$code_split=str_replace("{{key:".$keycode."}}","{{".$temp_part[$keycode]."}}",$code_split);
	}
	//--Seperate {{}} areas into array
	preg_match_all("|\{\{([^\}]*)\}\}|i",$code_split, $match); //--Fetch each instance of a function on it's own so we dont mix them up
	$code_part_on=0;
	foreach ($match[1] as $splits){
		$code_part[$code_part_on]=ss_code_variables_string_replace($id,$splits);
		$code_part_on+=1;
	}
	return $code_part;
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

	if (checkpreg("|s\.template\(([^\)]*)\)|i",$t)==true){
		$code_org=fetchpreg("|s\.template\(([^\)]*)\)|i",$t);
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
