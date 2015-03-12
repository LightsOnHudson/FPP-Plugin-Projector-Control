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


function createProjectorEventFiles() {
	
	global $eventDirectory,$PROJECTORS,$PROJECTOR_READ,$pluginDirectory,$pluginName,$scriptDirectory,$DEVICE_CONNECTION_TYPE,$DEVICE;
	
	
		
	//echo "next event file name available: ".$nextEventFilename."\n";

	$PROJECTOR_FOUND=false;
	
	for($projectorIndex=0;$projectorIndex<=count($PROJECTORS)-1;$projectorIndex++) {
	
		if($PROJECTORS[$projectorIndex]['NAME'] == $PROJECTOR_READ) {
		
		//	echo "CMD: ".$cmd."\n";
		//iterate through the various keys and make a file for them
			//	print_r($PROJECTORS[$projectorIndex]);
		//	echo "Processing files for projector name : ".$PROJECTOR_READ."<br/> \n";
			while (list($key, $val) = each($PROJECTORS[$projectorIndex])) {
			//	echo "key: ".$key." -- value: ".$val."\n";

				if($key != "NAME" && $key != "BAUD_RATE" && $key != "CHAR_BITS" && $key != "PARITY" && $key != "STOP_BITS" && $key != "VALID_STATUS_0" && $key != "VALID_STATUS_1" && $key != "VALID_STATUS_2")
				{
					
					//check to see that the file doesnt already exist - do a grep and return contents
					$EVENT_CHECK = checkEventFilesForKey("PROJECTOR-".$key);
					if(!$EVENT_CHECK)
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
						$eventData .= "script='PROJECTOR-".$key.".sh'\n";
						
					//	echo "eventData: ".$eventData."<br/>\n";
						file_put_contents($eventDirectory."/".$nextEventFilename, $eventData);
						
						$scriptCMD = $pluginDirectory."/".$pluginName."/"."proj.php -d".$DEVICE_CONNECTION_TYPE." -s".$DEVICE." -c".$key;
						createScriptFile("PROJECTOR-".$key.".sh",$scriptCMD);
					}
				}
				
				//echo "$key => $val\n";
			}
		}
	}
	
	
	
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