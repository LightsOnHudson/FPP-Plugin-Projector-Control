<?php
//$DEBUG=true;

$skipJSsettings = 1;
//include_once '/opt/fpp/www/config.php';
include_once '/opt/fpp/www/common.php';

$pluginName = "ProjectorControl";

include_once 'functions.inc.php';

//arg0 is  the program
//arg1 is the first argument in the registration this will be --list
//$DEBUG=true;
$logFile = $settings['logDirectory']."/".$pluginName.".log";
$sequenceExtension = ".fseq";

//logEntry("open log file: ".$logFile);



$DEBUG = false;
$projectorONSequence = "PROJ-ON";
$projectorOFFSequence = "PROJ-OFF";
$projectorVIDEOSequence = "PROJ-VIDEO-INPUT";

createProjectorSequenceFiles($settings);

if(isset($_POST['submit']))
{
	

	$IP=trim($_POST["IP"]);
	$PORT=trim($_POST["PORT"]);
	
	$DEVICE=trim($_POST["DEVICE"]);
	$DEVICE_CONNECTION_TYPE=trim($_POST["DEVICE_CONNECTION_TYPE"]);
	
	$ENABLED=$_POST["ENABLED"];

	//	echo "Writring config fie <br/> \n";

	WriteSettingToFile("DEVICE",$DEVICE,$pluginName);
	WriteSettingToFile("DEVICE_CONNECTION_TYPE",$DEVICE_CONNECTION_TYPE,$pluginName);
	WriteSettingToFile("IP",$IP,$pluginName);
	WriteSettingToFile("PORT",$PORT,$pluginName);
	
	WriteSettingToFile("ENABLED",$ENABLED,$pluginName);


} 


	
	$DEVICE = ReadSettingFromFile("DEVICE",$pluginName);
	$DEVICE_CONNECTION_TYPE = ReadSettingFromFile("DEVICE_CONNECTION_TYPE",$pluginName);
	$IP = ReadSettingFromFile("IP",$pluginName);
	$PORT = ReadSettingFromFile("PORT",$pluginName);
	
	$ENABLED = ReadSettingFromFile("ENABLED",$pluginName);




?>

<html>
<head>
</head>

<div id="projector" class="settings">
<fieldset>
<legend>Projector control Support Instructions</legend>

<p>Known Issues:
<ul>
<li>NONE</li>
</ul>

<p>Configuration:
<ul>
<li>Configure your connection type, IP, Serial</li>
</ul>

<form method="post" action="http://<? echo $_SERVER['SERVER_NAME']?>/plugin.php?plugin=ProjectorControl&page=plugin_setup.php">
Manually Set Station ID<br>
<p/>

<?
echo "ENABLE PLUGIN: ";

if($ENABLED == "on" || $ENABLED == 1) {
	echo "<input type=\"checkbox\" checked name=\"ENABLED\"> \n";
	//PrintSettingCheckbox("Radio Station", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
} else {
	echo "<input type=\"checkbox\"  name=\"ENABLED\"> \n";
}
echo "<p/>\n";

echo "Connection type: \n";

echo "<select name=\"DEVICE_CONNECTION_TYPE\"> \n";
                        if($DEVICE_CONNECTION_TYPE != "")
                        {
				switch ($DEVICE_CONNECTION_TYPE)
				{
					case "SERIAL":
                                		echo "<option selected value=\"".$DEVICE_CONNECTION_TYPE."\">".$DEVICE_CONNECTION_TYPE."</option> \n";
                                		echo "<option value=\"IP\">IP</option> \n";
                                		break;
					case "IP":
                                		echo "<option selected value=\"".$DEVICE_CONNECTION_TYPE."\">".$DEVICE_CONNECTION_TYPE."</option> \n";
                                		echo "<option value=\"SERIAL\">SERIAL</option> \n";
                        			break;
			
				
	
				}
	
			} else {

                                echo "<option value=\"SERIAL\">SERIAL</option> \n";
                                echo "<option value=\"IP\">IP</option> \n";
			}
                
        
echo "</select> \n";
echo "<p/> \n";

echo "<p/> \n";
echo "SERIAL DEVICE: \n";
echo "<select name=\"DEVICE\"> \n";
        foreach(scandir("/dev/") as $fileName)
        {
                if (preg_match("/^ttyUSB[0-9]+/", $fileName)) {
			$filename .= "/dev/";
			if($device == $filename)
			{
                        	echo "<option selected value=\"".$fileName."\">".$fileName."</option> \n";
			} else {
                       		echo "<option value=\"".$fileName."\">".$fileName."</option> \n";
			}
                }
        }
echo "</select> \n";
?>

<p/>
IP: 
<input type="text" value="<? if($IP !="" ) { echo $IP; } else { echo "";}?>" name="IP" id="PORT"></input>

<p/>

PORT:
<input type="text" value="<? if($PORT !="" ) { echo $PORT; } else { echo "";}?>" name="PORT" id="PORT"></input>


<p/>
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">
</form>


<p>To report a bug, please file it against the Projector Control plugin project on Git: https://github.com/LightsOnHudson/FPP-Plugin-Projector-Control

</fieldset>
</div>
<br />
</html>
