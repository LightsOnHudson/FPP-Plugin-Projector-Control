#!/usr/bin/php
<?php
error_reporting(0);
include 'php_serial.class.php';
include_once('projectorCommands.inc');

$skipJSsettings = 1;
include_once '/opt/fpp/www/config.php';

$projectorControlSettingsFile = $settings['mediaDirectory'] . "/config/plugin.projectorControl";

$logFile = $settings['logDirectory'] . "/projectorcontrol.log";

logEntry("opening log file: ".$logFile);
//$cfgServer="192.168.192.15";
$cfgPort="3001";
$cfgTimeOut=10;
$DEBUG=false;
$SERIAL_DEVICE="";

$options = getopt("c:d:h:p:s:");


if($options["d"] == "") {
	echo "Must specify device type using -d: (-dIP or -dSERIAL) \n";
	exit(0);
}

if($options["d"] == "IP" && $options["h"] == "") {
	echo "If using -dIP must supply hostname or IP xxx.xxx.xxx.xxx\n";
	exit(0);
}

if($options["h"] != "" && $options["p"] == "") {
	$PORT = "3001";
}

if($options["d"] == "SERIAL" && $options["s"] =="" ) {
	logEntry("MUST SPECIFY PORT -sttyUSB");
	exit(0);
	
}

if($options["s"] != "" && $options["d"] == "SERIAL") {
	$SERIAL_DEVICE="/dev/".$options["s"];
}

if($options["p"] !="" && $options["d"] == "IP") {
	$PORT = $options["p"];
}

if(strtoupper($options["d"]) =="SERIAL") {
	logEntry("SERIAL DEVICE OPEN: ".$SERIAL_DEVICE);

	$serial = new phpSerial;

	$serial->deviceSet($SERIAL_DEVICE);
	$serial->confBaudRate(19200);
	$serial->confParity("none");
	$serial->confCharacterLength(8);
	$serial->confStopBits(1);
	$serial->deviceOpen();
	$DEVICE="SERIAL";
}

if(strtoupper($options["d"]) == "IP") {

	$cfgServer = $options["h"];

	$fs = fsockopen($cfgServer, $PORT, $errno, $errstr, $cfgTimeOut);

	if(!$fs) {
		logEntry("ERROR connecting to projector controller");// "Error connecting to projector controller";
	}
	$DEVICE="IP";
	
	
}



$cmd="";

switch (strtoupper($options["c"])) {
	
	case "PC1":
		$cmd = $PC1_INPUT;
		break;
		
	case "PC2":
		$cmd = $PC2_INPUT;
		break;
		
	case "VIDEO":
		$cmd = $VIDEO_INPUT;
		break;
		
	case "SVIDEO":
		$cmd = $SVIDEO_INPUT;
		break;
	
	case "ON":
		$cmd = $ON;
		break;
		
	case "OFF":
		$cmd = $OFF;
		break;
	
	case "STATUS":
		$cmd = $STATUS;
		break;
		
	default:
		logEntry("NO COMMAND RECEIVED");// "No CMD received -c \n";
		exit(1);
}


logEntry("Sending command: on dvice: ".$DEVICE." ".$cmd);

switch ($DEVICE) {
	
	case "SERIAL":
		
		
	$serial->sendMessage("$cmd");
	sleep(1);
	$serial->deviceClose();
	exit(0);
	
	break;
	
	case "IP":
		
	fputs($fs,$cmd);
	sleep(1);
	fclose($fs);
	exit(0);
	break;
	
}

	function logEntry($data) {
	
		global $logFile;
	
		$data = $_SERVER['PHP_SELF']." : ".$data;
		$logWrite= fopen($logFile, "a") or die("Unable to open file!");
		fwrite($logWrite, date('Y-m-d h:i:s A',time()).": ".$data."\n");
		fclose($logWrite);
	}
?>
