<?php

function createProjectorSequenceFiles($settings) {
	
	//print_r($settings);
	
	//logEntry("Creating projector shell files");
	

	global $projectorONSequence, $projectorOFFSequence,$projectorVIDEOSequence,$sequenceExtension;

	
	//create blank sequence files
	fopen($settings['sequenceDirectory']."/".$projectorONSequence.$sequenceExtension, "w") or die("Unable to open file!");
	fclose($settings['sequenceDirectory']."/".$projectorONSequence);
	
	fopen($settings['sequenceDirectory']."/".$projectorOFFSequence.$sequenceExtension, "w") or die("Unable to open file!");
	fclose($settings['sequenceDirectory']."/".$projectorOFFSequence);
	
	fopen($settings['sequenceDirectory']."/".$projectorVIDEOSequence.$sequenceExtension, "w") or die("Unable to open file!");
	fclose($settings['sequenceDirectory']."/".$projectorVIDEOSequence);
	
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

	global $pluginName,$myPid;
	
	$DEVICE = ReadSettingFromFile("DEVICE",$pluginName);
	$DEVICE_CONNECTION_TYPE = ReadSettingFromFile("DEVICE_CONNECTION_TYPE",$pluginName);
	$IP = ReadSettingFromFile("IP",$pluginName);
	$PORT = ReadSettingFromFile("PORT",$pluginName);
	
	$ENABLED = ReadSettingFromFile("ENABLED",$pluginName);

//	logEntry("reading config file");
//	logEntry(" DEVICE: ".$DEVICE." DEVICE_CONNECTION_TYPE: ".$DEVICE_CONNECTION_TYPE." IP: ".$IP. " PORT: ".$PORT);

	logEntry("INSIDE SEND");
	//# Send line to scroller
	$cmd = "/opt/fpp/plugins/ProjectorControl/proj.php ";

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