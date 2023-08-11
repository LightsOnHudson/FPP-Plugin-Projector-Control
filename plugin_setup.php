<?php
//$DEBUG=true;

$skipJSsettings = 1;
//include_once '/opt/fpp/www/config.php';
include_once '/opt/fpp/www/common.php';

//$pluginName = "ProjectorControl";
//$pluginName = "FPP-Plugin-Projector-Control";
$pluginName = basename(dirname(__FILE__));  //pjd 7-10-2019   added per dkulp 

$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";


include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';
include 'projectorCommands.inc';
include_once 'version.inc';

$myPid = getmypid();

$gitURL = "https://github.com/FalconChristmas/FPP-Plugin-Projector-Control.git";

//arg0 is  the program
//arg1 is the first argument in the registration this will be --list
//$DEBUG=true;
$logFile = $settings['logDirectory']."/".$pluginName.".log";
$sequenceExtension = ".fseq";

logEntry("plugin update file: ".$pluginUpdateFile);

//logEntry("open log file: ".$logFile);



$DEBUG = false;

if(isset($_POST['updatePlugin']))
{
	$updateResult = updatePluginFromGitHub($gitURL, $branch="master", $pluginName);
	
	echo $updateResult."<br/> \n";
}

if(isset($_POST['submit']))
{
	

	$IP=trim($_POST["IP"]);
	$PORT=trim($_POST["PORT"]);

	$BAUD_RATE=trim($_POST["BAUD_RATE"]);
	$CHAR_BITS=trim($_POST["CHAR_BITS"]);
	$STOP_BITS=trim($_POST["STOP_BITS"]);
	$PARITY=trim($_POST["PARITY"]);
	
	$DEVICE=trim($_POST["DEVICE"]);
	$DEVICE_CONNECTION_TYPE=trim($_POST["DEVICE_CONNECTION_TYPE"]);
	
	//$ENABLED=$_POST["ENABLED"];

	//	echo "Writring config fie <br/> \n";

	WriteSettingToFile("BAUD_RATE",$BAUD_RATE,$pluginName);
	WriteSettingToFile("CHAR_BITS",$CHAR_BITS,$pluginName);
	WriteSettingToFile("STOP_BITS",$STOP_BITS,$pluginName);
	WriteSettingToFile("PARITY",$PARITY,$pluginName);
	
	WriteSettingToFile("DEVICE",$DEVICE,$pluginName);
	WriteSettingToFile("DEVICE_CONNECTION_TYPE",$DEVICE_CONNECTION_TYPE,$pluginName);
	WriteSettingToFile("IP",urlencode($IP),$pluginName);
	WriteSettingToFile("PORT",$PORT,$pluginName);
	WriteSettingToFile("PROJECTOR",urlencode($_POST["PROJECTOR"]),$pluginName);
	//WriteSettingToFile("ENABLED",$ENABLED,$pluginName);
	WriteSettingToFile("PROJ_PASSWORD",urlencode($_POST["PROJ_PASSWORD"]),$pluginName);

	

} 


	
	//$DEVICE = ReadSettingFromFile("DEVICE",$pluginName);
	$DEVICE = $pluginSettings['DEVICE'];
	
	$BAUD_RATE = $pluginSettings['BAUD_RATE'];
	$CHAR_BITS = $pluginSettings['CHAR_BITS'];
	$STOP_BITS = $pluginSettings['STOP_BITS'];
	$PARITY = $pluginSettings['PARITY'];
	
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
	$PROJ_PASSWORD = urldecode($pluginSettings['PROJ_PASSWORD']);
	$PROJECTOR_READ = $PROJECTOR;
	
			createProjectorEventFiles();
	
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
<li>Configure your connection type, IP/PJLINK or Serial</li>
<li>If using <b>PJLINK</b>, you will also need to install Python <b>PJLINK</b> library.</li>
<li>SSH into the Pi that is running the plugin, and type the following command:  <b>sudo cpan Net::PJLink</b></li>
This should download the library and then the plugin should work.
</ul>

<form method="post" action="http://<? echo $_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT']?>/plugin.php?plugin=<?php echo $pluginName;?>&page=plugin_setup.php">
<br>
<p/>

<?

echo "VER: ".$VERSION;
echo "<br/> \n";

echo "ENABLE PLUGIN: ";

//if($ENABLED == "on" || $ENABLED == 1) {
//	echo "<input type=\"checkbox\" checked name=\"ENABLED\"> \n";
	PrintSettingCheckbox("Projector Control", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
//} else {
//	echo "<input type=\"checkbox\"  name=\"ENABLED\"> \n";
//}
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
                if (preg_match("/tty[ASU][A-Z0-9]+/", $fileName)) {
//echo "DEVICE: ".$DEVICE. " -- ".$fileName
	if ($DEVICE == $fileName) {
			
			
                        	echo "<option selected value=\"".$fileName."\">".$fileName."</option> \n";
			} else {
                       		echo "<option value=\"".$fileName."\">".$fileName."</option> \n";
			}
                }
        }
echo "</select> \n";

echo "<p/> \n";
echo "Projector: \n";
printProjectorSelect();


echo "<p/> \n";
echo "Baud Rate: \n";
echo "<input type=\"text\" size=\"8\" value=\"".$BAUD_RATE."\" name=\"BAUD_RATE\"> \n";

echo "<p/> \n";
echo "CHAR BITS: \n";
echo "<input type=\"text\" size=\"2\" value=\"".$CHAR_BITS."\" name=\"CHAR_BITS\"> \n";

echo "<p/> \n";
echo "STOP BITS: \n";
echo "<input type=\"text\" size=\"2\" value=\"".$STOP_BITS."\" name=\"STOP_BITS\"> \n";

echo "<p/> \n";
echo "Parity (none, even, odd): \n";
echo "<input type=\"text\" size=\"8\" value=\"".$PARITY."\" name=\"PARITY\"> \n";

?>

  
<p/>
PJLINK IP: 
<input type="text" value="<? if($IP !="" ) { echo $IP; } else { echo "";}?>" name="IP" id="IP"></input>

<p/>
<p/>
PJLINK PASSWORD: 
<input type="text" value="<? if($PROJ_PASSWORD !="" ) { echo $PROJ_PASSWORD; } else { echo "";}?>" name="PROJ_PASSWORD" id="PROJ_PASSWORD"></input>

<p/>

<!-- 
PORT:
<input type="text" value="<? if($PORT !="" ) { echo $PORT; } else { echo "";}?>" name="PORT" id="PORT"></input>

-->
<p/>
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">
</form>


<p>To report a bug, please file it against the Projector Control plug-in project on Git: https://github.com/FalconChristmas/FPP-Plugin-Projector-Control

<form method="post" action="http://<? echo $_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT']?>/plugin.php?plugin=<?php echo $pluginName;?>&page=plugin_setup.php">
<?
 if(file_exists($pluginUpdateFile))
 {
 	//echo "updating plugin included";
	include $pluginUpdateFile;
}
?>
</form>
</fieldset>
</div>
<br />
</html>
