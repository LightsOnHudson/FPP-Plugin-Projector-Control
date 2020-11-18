#!/usr/bin/php
<?php
error_reporting(0);
//added Dec 3 2015
ob_implicit_flush();

include 'php_serial.class.php';
include_once('projectorCommands.inc');

$skipJSsettings = 1;
include_once '/opt/fpp/www/config.php';
include_once '/opt/fpp/www/common.php';

//$pluginName  = "ProjectorControl";
$pluginName = basename(dirname(__FILE__));  //pjd 8-10-2019   added per dkulp


include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';

//added TCPIP network control to port configured in projectorCommands.inc.php

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
$ENABLED = urldecode($pluginSettings['ENABLED']);

//$PROJECTOR = urldecode(ReadSettingFromFile("PROJECTOR",$pluginName));
$PROJECTOR = urldecode($pluginSettings['PROJECTOR']);
//$IP = urldecode($pluginSettings['PROJECTOR']);
$PROJ_PASSWORD = urldecode($pluginSettings['PROJ_PASSWORD']);

logEntry("PROJECTOR: ".$PROJECTOR);
//

if(trim($PROJECTOR) == "") {
	logEntry("No Projector configured in plugin, exiting");
	exit(0);
	
}

if($ENABLED != "ON") {
	logEntry("Plugin is DISABLED, exiting");
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
					
					logEntry("--------------");
					logEntry("PROJECTOR FOUND");
					logEntry("PROJECTOR: ".$PROJECTOR);
					
					if($PROJECTORS[$projectorIndex]['PROTOCOL'] == "PJLINK") {
						$DEVICE_CONNECTION_TYPE = "PJLINK";
						logEntry("PJLINK Projector");
						
						if(trim($PROJECTORS[$projectorIndex]['IP']) != "") {
							$IP= $PROJECTORS[$projectorIndex]['IP'];
						}
						
						
						if(trim($PROJECTORS[$projectorIndex]['PASSWORD']) != "") {
							$PROJ_PASSWORD = $PROJECTORS[$projectorIndex]['PASSWORD'];
						}
						
						$PJLINK_CMD =  $settings['pluginDirectory'] . "/" .$pluginName. "/pjlinkutil.pl ";
						$PJLINK_CMD .= $IP." ";
						$PJLINK_CMD .= $PROJECTOR_CMD." ";
						$PJLINK_CMD .= "-p ".$PROJ_PASSWORD;
						
						logEntry("PJLINK CMD: ".$PJLINK_CMD);
						$PROJECTOR_CMD = $PJLINK_CMD;
						
					} elseif ($PROJECTORS[$projectorIndex]['PROTOCOL'] == "TCP") {
						$DEVICE_CONNECTION_TYPE = "IP";
						logEntry("TCP/IP Projector");
						
						if(trim($PROJECTORS[$projectorIndex]['IP']) != "") {
							$IP= $PROJECTORS[$projectorIndex]['IP'];
						}
						
						
						if(trim($PROJECTORS[$projectorIndex]['PASSWORD']) != "") {
							$PROJ_PASSWORD = $PROJECTORS[$projectorIndex]['PASSWORD'];
						}
						$PORT= $PROJECTORS[$projectorIndex]['PORT'];
						
						//$IP_CMD =  $settings['pluginDirectory'] . "/" .$pluginName. "/pjlinkutil.pl ";
						//$IP_CMD .= $IP." ";
						//$IP_CMD = $PROJECTOR_CMD;//." ";
						//$IP_CMD .= "-p ".$PROJ_PASSWORD;
						
						logEntry("TCPIP IP: ".$IP." PORT: ".$PORT." CMD: ".$PROJECTOR_CMD);
						//$PROJECTOR_CMD = $IP_CMD;
						
					} else {
						
						if($pluginSettings['BAUD_RATE'] !="")
						{
							$PROJECTOR_BAUD = $pluginSettings['BAUD_RATE'];
						} else {
							$PROJECTOR_BAUD=$PROJECTORS[$projectorIndex]['BAUD_RATE'];
						}

						if($pluginSettings['CHAR_BITS'] !="")
						{
							$PROJECTOR_CHAR_BITS = $pluginSettings['CHAR_BITS'];
						} else {
							$PROJECTOR_CHAR_BITS=$PROJECTORS[$projectorIndex]['CHAR_BITS'];
						}

						if($pluginSettings['STOP_BITS'] !="")
						{
							$PROJECTOR_STOP_BITS = $pluginSettings['STOP_BITS'];
						} else {
							$PROJECTOR_STOP_BITS=$PROJECTORS[$projectorIndex]['STOP_BITS'];
						}

						if($pluginSettings['PARITY'] !="")
						{
							$PROJECTOR_PARITY = $pluginSettings['PARITY'];
						} else {
							$PROJECTOR_PARITY=$PROJECTORS[$projectorIndex]['PARITY'];
						}
						
					
						logEntry("BAUD RATE: ".$PROJECTOR_BAUD);
						logEntry("CHAR BITS: ".$PROJECTOR_CHAR_BITS);
						logEntry("STOP BITS: ".$PROJECTOR_STOP_BITS);
						logEntry("PARITY: ".$PROJECTOR_PARITY);
						
						
					}
					
					
					
				}



			}
	
	}
}

if(!$PROJECTOR_FOUND) {
	logEntry("projector command not found: exiting");
	exit(0);
}

logEntry("-------");
$PCMD = hex_dump($PROJECTOR_CMD, $newline="\n");
//logEntry("PROJECTOR CMD2: ".$PCMD);
logEntry("Sending command");
logEntry("HEX DECODED COMMAND: ".$PCMD);

switch ($DEVICE_CONNECTION_TYPE) {
	
	case "PJLINK":
		logEntry("Sending PJLINK Command");
		logEntry("PJLINK CMD: ".$PROJECTOR_CMD);
		
		system($PROJECTOR_CMD,$PJLINK_RESULT);
		logEntry("PJLINK RESULT: ".$PJLINK_RESULT[0]);
		
		exit(0);
		
		break;
		
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
       // $cfgServer = $options["h"];
	logEntry("SENDING IP COMMAND");
       
		sendTCP($IP, $PORT, $PROJECTOR_CMD);
	exit(0);
	break;
	
}
?>
