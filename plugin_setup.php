<?php
//$DEBUG=true;

$sequenceFolder = "/home/pi/media/sequences/";
$projectorControlSettingsFile = "/home/pi/media/plugins/projectorControl.settings";

$projectorONSequence = "PROJ-ON.fseq";
$projectorOFFSequence = "PROJ-OFF.fseq";
$projectorVIDEOSequence = "PROJ-VIDEO-INPUT.fseq";

//create blank sequence files
fopen($sequenceFolder.$projectorONSequence, "w") or die("Unable to open file!");
fclose($sequenceFolder.$projectorONSequence);

fopen($sequenceFolder.$projectorOFFSequence, "w") or die("Unable to open file!");
fclose($sequenceFolder.$projectorOFFSequence);

fopen($sequenceFolder.$projectorVIDEOSequence, "w") or die("Unable to open file!");
fclose($sequenceFolder.$projectorVIDEOSequence);

if(isset($_POST['submit']))
{
    
    $device = htmlspecialchars($_POST['device']);
    $device_connection_type = htmlspecialchars($_POST['device_connection_type']);
   
    $ip= htmlspecialchars($_POST['ip']);
    $port= htmlspecialchars($_POST['port']);
   
		//echo "Station Id set to: ".$name;

		$projectorSettings = fopen($projectorControlSettingsFile, "w") or die("Unable to open file!");
		
		$txt .= "DEVICE=".$device."\r\n";
		$txt .= "DEVICE_CONNECTION_TYPE=".$device_connection_type."\r\n";

		$txt .= "IP=".trim($ip)."\r\n";
		$txt .= "PORT=".trim($port)."\r\n";
		
		fwrite($projectorSettings, $txt);
		fclose($projectorSettings);
		
		$DEVICE=$device;
		$DEVICE_CONNECTION_TYPE=$device_connection_type;
		$IP =$ip;
		$PORT =$port;
		

	//add the ability for GROWL to show changes upon submit :)
	//	$.jGrowl("Station Id: $STATION_ID");	
        
  
 

} else {

	if($DEBUG)
		echo "READING FILE: <br/> \n";
	//try to read the settings file if available


	if (file_exists($projectorControlSettingsFile)) {
		$filedata=file_get_contents($projectorControlSettingsFile);
	} 
	
	if($filedata !="" )
	{
		$settingParts = explode("\r",$filedata);

		
		$configParts=explode("=",$settingParts[0]);
		$DEVICE = $configParts[1];
		
		$configParts=explode("=",$settingParts[1]);
		$DEVICE_CONNECTION_TYPE = $configParts[1];
	
		$configParts=explode("=",$settingParts[2]);
		$IP = $configParts[1];
	
		$configParts=explode("=",$settingParts[3]);
		$PORT = $configParts[1];


                
	}
	fclose($file_handle);

}
        if($DEBUG) {
	
		echo "DEVICE: ".$DEVICE."<br/> \n";
		
                echo "IP: ".$IP."<br/> \n";
                echo "PORT: ".$PORT."<br/> \n";
                echo "DEVICE CONNECTION TYPE: ".$DEVICE_CONNECTION_TYPE."<br/> \n";
              
                }
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

echo "Connection type: \n";

echo "<select name=\"device_connection_type\"> \n";
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
echo "<select name=\"device\"> \n";
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
<input type="text" value="<? if($IP !="" ) { echo $IP; } else { echo "";}?>" name="ip" id="ip"></input>

<p/>

PORT:
<input type="text" value="<? if($PORT !="" ) { echo $PORT; } else { echo "";}?>" name="port" id="port"></input>


<p/>
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">
</form>


<p>To report a bug, please file it against the Projector Control plugin project on Git: https://github.com/LightsOnHudson/FPP-Plugin-Projector-Control

</fieldset>
</div>
<br />
</html>
