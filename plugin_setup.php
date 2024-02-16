<?php
$DEBUG=false;

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
logEntry("plugin update file: ".$pluginUpdateFile);

$DEBUG = false;

if(isset($_POST['updatePlugin']))
{
	$updateResult = updatePluginFromGitHub($gitURL, $branch="master", $pluginName);
	
	echo $updateResult."<br/> \n";
}	
	logEntry("In plugin setup and run each time on load???");
	$DEVICE = $pluginSettings['DEVICE'];
	$BAUD_RATE = $pluginSettings['BAUD_RATE'];
	$CHAR_BITS = $pluginSettings['CHAR_BITS'];
	$STOP_BITS = $pluginSettings['STOP_BITS'];
	$PARITY = $pluginSettings['PARITY'];	
	$DEVICE_CONNECTION_TYPE = $pluginSettings['DEVICE_CONNECTION_TYPE'];
	$IP = $pluginSettings['IP'];
	$PORT = $pluginSettings['PORT'];
	$ENABLED = urldecode($pluginSettings['ENABLED']);
	$PROJECTOR = urldecode($pluginSettings['PROJECTOR']);
	$PROJ_PASSWORD = urldecode($pluginSettings['PROJ_PASSWORD']);
	$PROJECTOR_READ = $PROJECTOR;

	// This section will create the script files  -Pat 2/3/2024
	createProjectorEventFiles();
?>

<html>
<head>
</head>

<div id="projector" class="settings">
<legend>Projector control Support Instructions</legend>
<h2>Version <?echo $VERSION;?></h2>

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

<br>
<p/>
ENABLE PLUGIN <?PrintSettingCheckbox("Projector Control", "ENABLED",0, 0, "ON", "OFF", $pluginName, "", "pluginEnabled");?>
<div>Projector: <? PrintSettingSelect("ProjectorType", "PROJECTOR", 0, 0, $defaultValue="", $values = getProjectors(), $pluginName); ?></div></p>
<div>Connection Type <? PrintSettingSelect("DEVICE_CONNECTION_TYPE", "DEVICE_CONNECTION_TYPE", 0, 0, $defaultValue="", Array("SERIAL"=>"SERIAL", "IP"=>"IP"), $pluginName); ?></div></p>
<div>Serial Device: <? PrintSettingSelect("Device", "DEVICE", 0, 0, "", $values = get_serialDevices(), $pluginName); ?></div></p>
<div>Baud Rate: <? PrintSettingSelect("BaudRate", "BAUD_RATE", 0, 0, "19200", $values = getBaudRates(), $pluginName); ?></div></p>	
<div>Char Bits: <? PrintSettingSelect("CharBits", "CHAR_BITS", 0, 0, "8", $values = getCharBits(), $pluginName); ?></div></p>	
<div>Stop Bits: <? PrintSettingSelect("StopBits", "STOP_BITS", 0, 0, "1", array("1"=>"1","2"=>"2"), $pluginName); ?></div></p>	
<div>Parity: <? PrintSettingSelect("Parity", "PARITY", 0, 0, $defaultValue="none", array("none"=>"none","even"=>"even","odd"=>"odd"), $pluginName); ?></div></p>	
<div class="alert alert-warning" id="IP_Warning" style="color:Red; display:none">
    <strong>Warning!</strong> This is an invalid IP
</div>
<div>PJLINK IP: <?  PrintSettingTextSaved("IP", 0,0, 15, 15, $pluginName, "", "validateIP"); ?></div></p>	
<div>PJLINK Password: <?  PrintSettingTextSaved("PROJ_PASSWORD", 0,0, 30, 30, $pluginName); ?></div></p>
<div class="alert alert-warning" id="Port_Warning" style="color:Red; display:none">
    <strong>Warning!</strong> This is an invalid Port. Only numbers are valid
</div>
<div>Port: <?  PrintSettingTextSaved("PORT", 0,0, 6, 6, $pluginName, "", "validatePort"); ?></div></p>
 
<p>To report a bug, please file it against the Projector Control plug-in project on Git:<a href="https://github.com/FalconChristmas/FPP-Plugin-Projector-Control/issues">Issues Link</a>

<form method="post" action="http://<? echo $_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT']?>/plugin.php?plugin=<?php echo $pluginName;?>&page=plugin_setup.php">
<?
if (!isset($pluginSettings['PROJECTOR'])){ //check to see if first run. Save displayed settings if not
	logEntry("In isset routine");
	?><script>
		function initialSave(){
			var pluginName = "<? echo $pluginName ?>";
			var temp;
			temp=document.getElementById("PROJECTOR").options[0].value;			
			SetPluginSetting(pluginName, "PROJECTOR", temp);

			temp=document.getElementById("DEVICE_CONNECTION_TYPE");
			if (temp.selectedIndex===-1){
				temp=document.getElementById("DEVICE_CONNECTION_TYPE").options[0].value;					
			}else{
				temp = temp.options[temp.selectedIndex].value;				
			}			
			SetPluginSetting(pluginName, "DEVICE_CONNECTION_TYPE", temp);

			temp=document.getElementById("DEVICE");
			if (temp.selectedIndex===-1){
				temp=document.getElementById("DEVICE").options[0].value;					
			}else{
				temp = temp.options[temp.selectedIndex].value;				
			}			
			SetPluginSetting(pluginName, "DEVICE", temp);
			
			temp=document.getElementById("BAUD_RATE");
			if (temp.selectedIndex===-1){
				temp=document.getElementById("BAUD_RATE").options[0].value;					
			}else{
				temp = temp.options[temp.selectedIndex].value;				
			}			
			SetPluginSetting(pluginName, "BAUD_RATE", temp);

			temp=document.getElementById("CHAR_BITS");
			if (temp.selectedIndex===-1){
				temp=document.getElementById("CHAR_BITS").options[0].value;					
			}else{
				temp = temp.options[temp.selectedIndex].value;				
			}			
			SetPluginSetting(pluginName, "CHAR_BITS", temp);

			temp=document.getElementById("STOP_BITS");
			if (temp.selectedIndex===-1){
				temp=document.getElementById("STOP_BITS").options[0].value;					
			}else{
				temp = temp.options[temp.selectedIndex].value;				
			}			
			SetPluginSetting(pluginName, "STOP_BITS", temp);

			temp=document.getElementById("PARITY");
			if (temp.selectedIndex===-1){
				temp=document.getElementById("PARITY").options[0].value;					
			}else{
				temp = temp.options[temp.selectedIndex].value;				
			}			
			SetPluginSetting(pluginName, "PARITY", temp);
		}	
		initialSave();
	</script><?
}
 if(file_exists($pluginUpdateFile))
 {
 	//echo "updating plugin included";
	include $pluginUpdateFile;
}
?>
<script>
//document.getElementById("PROJ_PASSWORD").value="<?echo $pluginName?>";

function validateIP(){

	var ipAddress = document.getElementById("IP").value;
    var ipRegex = /^(25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)$/;

    if (ipRegex.test(ipAddress)) {
        document.getElementById('IP_Warning').style.display = "none";        
    } else {
        document.getElementById('IP_Warning').style.display = "block";
    }
// ******* move below to enabled

GetSync("plugin.php?plugin=<?echo $pluginName ?>&page=functions.inc.php&action=create_scripts&nopage=1");
var pathToPHP = '<?echo $pluginDirectory?>'+'/'+'<? echo $pluginName?>' + '/functions.inc.php';
//var pathToPHP = 'echo $pluginDirectory?>'+'/'+' echo $pluginName' + '/functions.inc.php';
document.getElementById("PROJ_PASSWORD").value="plugin.php?plugin=<?echo $pluginName ?>&page=functions.inc.php&action=create_scripts";
}

function validatePort(){
	var ipAddress = document.getElementById("PORT").value;
	var ipRegex = /^\d+$/;
	if (ipRegex.test(ipAddress)) {
        document.getElementById('Port_Warning').style.display = "none";        
    } else {
        document.getElementById('Port_Warning').style.display = "block";
    }
}

function pluginEnabled(){
	

	//document.getElementById("IP").value=ProjectorBox;

}

</script>
</html>
