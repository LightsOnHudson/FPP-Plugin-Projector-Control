#!/usr/bin/php
<?php

include 'php_serial.class.php';
require_once 'projectorCommands.inc'; 


$logFile = "/home/pi/media/logs/projectorcontrol.log";
//$cfgServer="192.168.192.15";
$cfgPort="3001";
$cfgTimeOut=10;
$DEBUG=false;



logEntry("COMMDNDS LOADED?? ".$ON);
$options = getopt("c:d:h:p:s:");
$DEVICE="serial";


if($DEBUG)
	var_dump($options);

if($options["d"] == "") {
	echo "Must specify device type using -d: (-dIP or -dSERIAL) \n";
	exit(1);
}

if($options["d"] != "" && $options["h"] == "") {
	echo "If using -dIP must supply hostname or IP xxx.xxx.xxx.xxx\n";
	exit(1);
}

if($options["h"] != "" && $options["p"] == "") {
	$PORT = "3001";
}

if($options["s"] != "" && $options["d"] == "SERIAL") {
	$SERIAL_DEVICE=$options["s"];
}

if(strtoupper($options["d"]) =="SERIAL") {

	$serial = new phpSerial;

	$serial->deviceSet($SERIAL_DEVICE);
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


logEntry("Sending command: ".$cmd);

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