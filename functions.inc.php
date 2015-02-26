<?php

//print the different projectors for plugin setup
function printProjectorSelect() {
	
	global $PROJECTORS,$PROJECTOR_READ;
	
	//print_r($PROJECTORS);
	
	echo "<select name=\"PROJECTOR\"> \n";
	
	foreach ($PROJECTORS as $projector) {
		
		if($projector['NAME'] == $PROJECTOR_READ) {
			echo "<option selected value=\"".$projector['NAME']."\">".$projector['NAME']."</option> \n";
		} else {
			echo "<option value=\"".$projector['NAME']."\">".$projector['NAME']."</option> \n";
		}
	}
	
	
	
	echo "</select> \n";
	
}

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
	echo "new index: ".$newIndex."\n";
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
function createProjectorEventFiles() {
	
	global $eventDirectory,$PROJECTORS,$PROJECTOR_READ;
	
	
		
	//echo "next event file name available: ".$nextEventFilename."\n";

	$PROJECTOR_FOUND=false;
	
	for($projectorIndex=0;$projectorIndex<=count($PROJECTORS)-1;$projectorIndex++) {
	
		if($PROJECTORS[$projectorIndex]['NAME'] == $PROJECTOR_READ) {
		
		//	echo "CMD: ".$cmd."\n";
			//	print_r($PROJECTORS[$projectorIndex]);
			while (list($key, $val) = each($PROJECTORS[$projectorIndex])) {
			//	echo "key: ".$key." -- value: ".$val."\n";

				if($key != "NAME" && $key != "BAUD_RATE" && $key != "CHAR_BITS" && $key != "PARITY" && $key != "STOP_BITS" && $key != "VALID_STATUS_0" && $key != "VALID_STATUS_1" && $key != "VALID_STATUS_2")
				{
					$nextEventFilename = getNextEventFilename();
					$MAJOR=substr($nextEventFilename,0,2);
					$MINOR=substr($nextEventFilename,3,2);
					$eventData  ="";
					$eventData  = "majorID=".(int)$MAJOR."\n";
					$eventData .= "minorID=".(int)$MINOR."\n";
					$eventData .= "name='PROJECTOR-".$key."'\n";
					$eventData .= "effect=''\n";
					$eventData .= "startChannel=\n";
					$eventData .= "script=''\n";
					
					echo "eventData: ".$eventData."\n";
					file_put_contents($eventDirectory."/".$nextEventFilename, $eventData);
				}
				
				//echo "$key => $val\n";
			}
		}
	}
	
	
	
}

function logEntry($data) {

	global $logFile,$myPid,$callBackPid;
	
	if($callBackPid != "") {
		$data = $_SERVER['PHP_SELF']." : [".$callBackPid.":".$myPid."] ".$data;
	} else { 
	
		$data = $_SERVER['PHP_SELF']." : [".$myPid."] ".$data;
	}
	$logWrite= fopen($logFile, "a") or die("Unable to open file!");
	fwrite($logWrite, date('Y-m-d h:i:s A',time()).": ".$data."\n");
	fclose($logWrite);
}


function escapeshellarg_special($file) {
	return "'" . str_replace("'", "'\"'\"'", $file) . "'";
}


//function send the message

function sendCommand($projectorCommand) {

	global $pluginName,$myPid,$pluginDirectory,$DEVICE,$DEVICE_CONNECTION_TYPE,$IP,$PORT;
	
	//$DEVICE = ReadSettingFromFile("DEVICE",$pluginName);
	//$DEVICE_CONNECTION_TYPE = ReadSettingFromFile("DEVICE_CONNECTION_TYPE",$pluginName);
	//$IP = ReadSettingFromFile("IP",$pluginName);
	//$PORT = ReadSettingFromFile("PORT",$pluginName);
	
	//$ENABLED = ReadSettingFromFile("ENABLED",$pluginName);

//	logEntry("reading config file");
//	logEntry(" DEVICE: ".$DEVICE." DEVICE_CONNECTION_TYPE: ".$DEVICE_CONNECTION_TYPE." IP: ".$IP. " PORT: ".$PORT);

	//logEntry("INSIDE SEND");
	//# Send line to  Projector
	$cmd = $pluginDirectory."/".$pluginName."/proj.php ";

	$cmd .= "-d".$DEVICE_CONNECTION_TYPE;


	switch ($DEVICE_CONNECTION_TYPE) {

		case "SERIAL":

			$SERIALCMD = " -s".$DEVICE." -c".$projectorCommand;
			$cmd .= $SERIALCMD;
				
			break;
				
		case "IP":
			$IPCMD = " -h".$IP. " -c".$projectorCommand;
			$cmd .= $IPCMD;
			break;
				
	}

	$cmd .= " -z".$myPid;
	
	logEntry("COMMAND: ".$cmd);
	system($cmd,$output);

	//system($cmd."\"".$line."\" ".$DEVICE,$output);
}

function processSequenceName($sequenceName) {
	
	global $projectorONSequence, $projectorOFFSequence,$projectorVIDEOSequence;
	
	logEntry("Sequence name: ".$sequenceName);

	$sequenceName = strtoupper($sequenceName);

	switch ($sequenceName) {

		case "PROJ-ON.FSEQ":

			logEntry("Projector On");
			sendCommand("ON");
			break;
			exit(0);
			
			case "PROJ-OFF.FSEQ":
			
				logEntry("Projector OFF");
				sendCommand("OFF");
				break;
				exit(0);
				
				case "PROJ-VIDEO-INPUT.FSEQ":
				
					logEntry("Projector Video Input Select");
					sendCommand("VIDEO");
					break;
					exit(0);
				
		default:
			logEntry("We do not support sequence name: ".$sequenceName." at this time");
				
			exit(0);
				
	}
	


}
function processCallback($argv) {

	global $DEBUG,$pluginName;
	
	
	if($DEBUG)
		print_r($argv);
	//argv0 = program
	
	//argv2 should equal our registration // need to process all the rgistrations we may have, array??
	//argv3 should be --data
	//argv4 should be json data
	
	$registrationType = $argv[2];
	$data =  $argv[4];
	
	logEntry("PROCESSING CALLBACK");
	$clearMessage=FALSE;
	
	switch ($registrationType)
	{
		case "media":
			if($argv[3] == "--data")
			{
				$data=trim($data);
				logEntry("DATA: ".$data);
				$obj = json_decode($data);
	
				$type = $obj->{'type'};
	
				switch ($type) {
						
					case "sequence":
	
						//$sequenceName = ;
						processSequenceName($obj->{'Sequence'});
							
						break;
					case "media":
							
						logEntry("We do not understand type media at this time");
							
						exit(0);
	
						break;
	
					default:
						logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
						exit(0);
						break;
	
				}
	
	
			}
	
			break;
			exit(0);
				
		default:
			exit(0);
	
	}
	


}
?>