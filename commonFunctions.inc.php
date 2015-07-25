<?php

//update plugin

function updatePluginFromGitHub($gitURL, $branch="master", $pluginName) {
	
	
	global $settings;
	logEntry ("updating plugin: ".$pluginName);
	
	logEntry("settings: ".$settings['pluginDirectory']);
	
	//create update script
	//$gitUpdateCMD = "sudo cd ".$settings['pluginDirectory']."/".$pluginName."/; sudo /usr/bin/git git pull ".$gitURL." ".$branch;

	$gitUpdateCMD = $settings['pluginDirectory']."/".$pluginName."/updatePlugin.sh";
	
	$file = $settings['pluginDirectory']."/".$pluginName."/updatePlugin.sh";
	
	
	$scriptInfo = "#!/bin/bash \n";
	$scriptInfo .= "cd " . $settings['pluginDirectory'] . "/".$pluginName."/ \n";
			$scriptInfo .= "sudo /usr/bin/git pull ".$gitURL." ".$branch;
			// Write the contents back to the file
			//file_put_contents($file, $scriptInfo);
	
			
	
			//give execute permissions to file
			//$execCMD = "sudo chmod +x ".$file;
			//exec($execCMD);
			
			sleep(1);
			
			$gitUpdateCMD = "sudo cd " . $settings['pluginDirectory'] . "/".$pluginName."/; sudo /usr/bin/git pull ".$gitURL." ".$branch;
			logEntry("Git Update cmd: ".$gitUpdateCMD);
			
			exec($gitUpdateCMD,$gitResult);
	
			//print_r($gitResult);
	
			logEntry("Git result: ".$gitResult[0]);
			
			if(strtoupper(trim($gitResult[0])) === "ALREADY UP-TO-DATE.")
			{
				$gitResult[0] = "Plugin is up to date";
			} else {
				$gitResult[0] = "Updated plugin from GitHub";
			}
			return $gitResult[0];
	
	
}
//create script to randmomize
function createScriptFile($scriptFilename,$scriptCMD) {


	global $scriptDirectory,$pluginName;

	$scriptFilename = $scriptDirectory."/".$scriptFilename;

	logEntry("Creating  script: ".$scriptFilename);
	
	$ext = pathinfo($scriptFilename, PATHINFO_EXTENSION);

	
	$data = "";

	$data .="#!/bin/sh\n";

	
	$data .= "\n";
	$data .= "#Script to run randomizer\n";
	$data .= "#Created by ".$pluginName."\n";
	$data .= "#\n";
	$data .= "/usr/bin/php ".$scriptCMD."\n";
	
	logEntry($data);


	$fs = fopen($scriptFilename,"w");
	fputs($fs, $data);
	fclose($fs);

}
//return the next event file available for use

//get the next available event filename
function getNextEventFilename() {

	$MAX_MAJOR_DIGITS=2;
	$MAX_MINOR_DIGITS=2;
	global $eventDirectory;

	//echo "Event Directory: ".$eventDirectory."<br/> \n";

	$MAJOR=array();
	$MINOR=array();

	$MAJOR_INDEX=0;
	$MINOR_INDEX=0;

	$EVENT_FILES = directoryToArray($eventDirectory, false);
	//print_r($EVENT_FILES);

	foreach ($EVENT_FILES as $eventFile) {

		$eventFileParts = explode("_",$eventFile);

		$MAJOR[] = (int)basename($eventFileParts[0]);
		//$MAJOR = $eventFileParts[0];

		$minorTmp = explode(".fevt",$eventFileParts[1]);

		$MINOR[] = (int)$minorTmp[0];

		//echo "MAJOR: ".$MAJOR." MINOR: ".$MINOR."\n";
		//print_r($MAJOR);
		//print_r($MINOR);

	}

	$MAJOR_INDEX = max(array_values($MAJOR));
	$MINOR_INDEX = max(array_values($MINOR));

	//echo "Major max: ".$MAJOR_INDEX." MINOR MAX: ".$MINOR_INDEX."\n";



	if($MAJOR_INDEX <= 0) {
		$MAJOR_INDEX=1;
	}
	if($MINOR_INDEX <= 0) {
		$MINOR_INDEX=1;

	} else {

		$MINOR_INDEX++;
	}

	$MAJOR_INDEX = str_pad($MAJOR_INDEX, $MAX_MAJOR_DIGITS, '0', STR_PAD_LEFT);
	$MINOR_INDEX = str_pad($MINOR_INDEX, $MAX_MINOR_DIGITS, '0', STR_PAD_LEFT);
	//for now just return the next MINOR index up and keep the same Major
	$newIndex=$MAJOR_INDEX."_".$MINOR_INDEX.".fevt";
	//echo "new index: ".$newIndex."\n";
	return $newIndex;
}


function directoryToArray($directory, $recursive) {
	$array_items = array();
	if ($handle = opendir($directory)) {
		while (false !== ($file = readdir($handle))) {
			if ($file != "." && $file != "..") {
				if (is_dir($directory. "/" . $file)) {
					if($recursive) {
						$array_items = array_merge($array_items, directoryToArray($directory. "/" . $file, $recursive));
					}
					$file = $directory . "/" . $file;
					$array_items[] = preg_replace("/\/\//si", "/", $file);
				} else {
					$file = $directory . "/" . $file;
					$array_items[] = preg_replace("/\/\//si", "/", $file);
				}
			}
		}
		closedir($handle);
	}
	return $array_items;
}


//check all the event files for a string matching this and return true/false if exist
function checkEventFilesForKey($keyCheckString) {
	global $eventDirectory;

	$keyExist = false;
	$eventFiles = array();

	$eventFiles = directoryToArray($eventDirectory, false);
	foreach ($eventFiles as $eventFile) {

		if( strpos(file_get_contents($eventFile),$keyCheckString) !== false) {
			// do stuff
			$keyExist= true;
			break;
			// return $keyExist;
		}
	}

	return $keyExist;

}
?>