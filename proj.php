#!/usr/bin/php
<?php
error_reporting(0);
include 'php_serial.class.php';
include_once('projectorCommands.inc');

$skipJSsettings = 1;
include_once '/opt/fpp/www/config.php';
include_once '/opt/fpp/www/common.php';

$pluginName  = "ProjectorControl";

include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';

$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);

$logFile = $settings['logDirectory'] . "/".$pluginName.".log";
$myPid = getmypid();

//$cfgServer="192.168.192.15";
$cfgPort="3001";
$cfgTimeOut=10;
$DEBUG=false;
$SERIAL_DEVICE="";
$callBackPid="";

//$DEVICE = ReadSettingFromFile("DEVICE",$pluginName);
$DEVICE = $pluginSettings['DEVICE'];

//$DEVICE_CONNECTION_TYPE = ReadSettingFromFile("DEVICE_CONNECTION_TYPE",$pluginName);
$DEVICE_CONNECTION_TYPE = $pluginSettings['DEVICE_CONNECTION_TYPE'];

//$IP = ReadSettingFromFile("IP",$pluginName);
$IP = $pluginSettings['IP'];

//$PORT = ReadSettingFromFile("PORT",$pluginName);
$PORT = $pluginSettings['PORT'];

//$ENABLED = ReadSettingFromFile("ENABLED",$pluginName);
$ENABLED = $pluginSettings['ENABLED'];

//$PROJECTOR = urldecode(ReadSettingFromFile("PROJECTOR",$pluginName));
$PROJECTOR = $pluginSettings['PROJECTOR'];

logEntry("PROJECTOR: ".$PROJECTOR);

if(trim($PROJECTOR == "" )) {
	logEntry("No Projector configured in plugin, exiting");
	exit(0);
	
}

$options = getopt("c:");


$SERIAL_DEVICE="/dev/".$DEVICE;


if($options["z"] != "") {
	$callBackPid = $options["z"];
}
//logEntry("callback pid: ".$callBackPid);


logEntry("option C: ".$options["c"]);
$cmd= strtoupper(trim($options["c"]));

//loop through the array of projectors to get the command
$projectorIndex = 0;
//set the found flag, do not send a command if the name and command cannot be found


//print_r($PROJECTORS);
$PROJECTOR_FOUND=false;

for($projectorIndex=0;$projectorIndex<=count($PROJECTORS)-1;$projectorIndex++) {

	
	if($PROJECTORS[$projectorIndex]['NAME'] == $PROJECTOR) {
			logEntry("Projector index: ".$projectorIndex);
			logEntry("Looking for command string for cmd: ".$cmd);

			while (list($key, $val) = each($PROJECTORS[$projectorIndex])) {
				//logEntry( "Key: ".$key. "  \t VAL:".$val);

				if(strtoupper(trim($key)) == $cmd) {
					$PROJECTOR_FOUND=true;
					$PROJECTOR_CMD = $val;
					$PROJECTOR_BAUD=$PROJECTORS[$projectorIndex]['BAUD_RATE'];
					$PROJECTOR_CHAR_BITS=$PROJECTORS[$projectorIndex]['CHAR_BITS'];
					$PROJECTOR_STOP_BITS=$PROJECTORS[$projectorIndex]['STOP_BITS'];
					$PROJECTOR_PARITY=$PROJECTORS[$projectorIndex]['PARITY'];
					if($PROJECTOR[$projectorIndex]['PROTOCOL'] == "PJLINK") {
						logEntry("PJLINK Projector");
					}
					logEntry("--------------");
					logEntry("PROJECTOR FOUND");
					logEntry("PROJECTOR: ".$PROJECTOR_READ);
					$PCMD = hex_dump($PROJECTOR_CMD, $newline="\n");
					logEntry("PROJECTOR CMD2: ".$PCMD);
					logEntry("BAUD RATE: ".$PROJECTOR_BAUD);
					logEntry("CHAR BITS: ".$PROJECTOR_CHAR_BITS);
					logEntry("STOP BITS: ".$PROJECTOR_STOP_BITS);
					logEntry("PARITY: ".$PROJECTOR_PARITY);
				}



			}
	
	}
}

if(!$PROJECTOR_FOUND) {
	logEntry("projector command not found: exiting");
	exit(0);
}

logEntry("-------");
logEntry("Sending command");
logEntry(" COMMAND: ".$PCMD);

switch ($DEVICE_CONNECTION_TYPE) {
	
	case "SERIAL":
	logEntry("Sending SERIAL COMMAND");
	logEntry("SERIAL DEVICE: ".$SERIAL_DEVICE);
        $serial = new phpSerial;

        $serial->deviceSet($SERIAL_DEVICE);
        $serial->confBaudRate($PROJECTOR_BAUD);
        $serial->confParity($PROJECTOR_PARITY);
        $serial->confCharacterLength($PROJECTOR_CHAR_BITS);
        $serial->confStopBits($PROJECTOR_STOP_BITS);
        $serial->deviceOpen();
	
		
	$serial->sendMessage("$PROJECTOR_CMD");
	sleep(1);
	logEntry("RETURN DATA: ".hex_dump($serial->readPort()));
	$serial->deviceClose();
	exit(0);
	
	break;
	
	case "IP":
        $cfgServer = $options["h"];
	logEntry("SENDING IP COMMAND");
        $fs = fsockopen($cfgServer, $PORT, $errno, $errstr, $cfgTimeOut);

        if(!$fs) {
                logEntry("ERROR connecting to projector controller");// "Error connecting to projector controller";
        }
	fputs($fs,$PROJECTOR_CMD);
	sleep(1);
	fclose($fs);
	exit(0);
	break;
	
}
?>
