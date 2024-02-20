<?php
$DEBUG=false;

$skipJSsettings = 1;
include_once '/opt/fpp/www/common.php';
include_once 'functions.inc.php';
include_once 'commonFunctions.inc.php';
include 'projectorCommands.inc';
include_once 'version.inc';

$DEBUG = false;

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
	<li>Select your Projector</li>
	<li>The Projector Plugin will load the default settings for the projector chosen</li>
	<li>For network based projectors, you will have to enter the IP address</li>
	<li>Depending on the Projector selected, you might have to enter a password or Port</li>
	<li>The Projector Control plug in now supports using FPP Commands</li>
</ul>

<br>
<p/>
<div id="enabled">ENABLE PLUGIN <?PrintSettingCheckbox("Projector Control", "ENABLED",0, 0, "ON", "OFF", $pluginName);?></div></p>
<div id="proj">Projector: <? PrintSettingSelect("ProjectorType", "PROJECTOR", 1, 0, $defaultValue="PJLINK", $values = getProjectors(), $pluginName, "projectorChanged"); ?></div></p>
<div id="serial">Serial Device: <? PrintSettingSelect("Device", "DEVICE", 0, 0, "", $values = get_serialDevices(), $pluginName); ?></div></p>
<div id="baud">Baud Rate: <? PrintSettingSelect("BaudRate", "BAUD_RATE", 0, 0, "19200", $values = getBaudRates(), $pluginName); ?></div></p>	
<div id="char">Char Bits: <? PrintSettingSelect("CharBits", "CHAR_BITS", 0, 0, "8", $values = getCharBits(), $pluginName); ?></div></p>	
<div id="stop">Stop Bits: <? PrintSettingSelect("StopBits", "STOP_BITS", 0, 0, "1", array("1"=>"1","2"=>"2"), $pluginName); ?></div></p>	
<div id="parity">Parity: <? PrintSettingSelect("Parity", "PARITY", 0, 0, $defaultValue="none", array("none"=>"none","even"=>"even","odd"=>"odd"), $pluginName); ?></div></p>	
<div class="alert alert-warning" id="IP_Warning" style="color:Red; display:none">
    <strong>Warning!</strong> This is an invalid IP
</div>
<div id="ip">PJLINK IP: <?  PrintSettingTextSaved("IP", 0,0, 15, 15, $pluginName, "", "validateIP"); ?></div></p>	
<div id="pass">PJLINK Password: <?  PrintSettingTextSaved("PROJ_PASSWORD", 0,0, 30, 30, $pluginName); ?></div></p>
<div class="alert alert-warning" id="Port_Warning" style="color:Red; display:none">
    <strong>Warning!</strong> This is an invalid Port. Only numbers are valid
</div>
<div id="port">Port: <?  PrintSettingTextSaved("PORT", 0,0, 6, 6, $pluginName, "", "validatePort"); ?></div></p>
 
<p>To report a bug, please file it against the Projector Control plug-in project on Git:<a href="https://github.com/FalconChristmas/FPP-Plugin-Projector-Control/issues"> Projector Control Issues Link</a>
<!-- Delete this? -->
<form method="post" action="http://<? echo $_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT']?>/plugin.php?plugin=<?php echo $pluginName;?>&page=plugin_setup.php">
<?
if (!isset($pluginSettings['PROJECTOR'])){ //check to see if first run. Save displayed settings if not
	
	?><script>
		function initialSave(){
			var pluginName = "<? echo $pluginName ?>";
			var temp;			
			
			temp=document.getElementById("PROJECTOR"); //save the displayed value- if no default (selected) then save first
			temp= (temp.selectedIndex===-1) ? document.getElementById("PROJECTOR").options[0].value : temp.options[temp.selectedIndex].value;
			SetPluginSetting(pluginName, "PROJECTOR", temp,0,0,null);			

			temp=document.getElementById("DEVICE"); //save the displayed value- if no default (selected) then save first
			temp= (temp.selectedIndex===-1) ? document.getElementById("DEVICE").options[0].value : temp.options[temp.selectedIndex].value;				
			SetPluginSetting(pluginName, "DEVICE", temp,0,0,null);
			
			temp=document.getElementById("BAUD_RATE"); //save the displayed value- if no default (selected) then save first
			temp= (temp.selectedIndex===-1) ? document.getElementById("BAUD_RATE").options[0].value : temp.options[temp.selectedIndex].value;
			SetPluginSetting(pluginName, "BAUD_RATE", temp,0,0,null);

			temp=document.getElementById("CHAR_BITS"); //save the displayed value- if no default (selected) then save first
			temp= (temp.selectedIndex===-1) ? document.getElementById("CHAR_BITS").options[0].value : temp.options[temp.selectedIndex].value;
			SetPluginSetting(pluginName, "CHAR_BITS", temp,0,0,null);

			temp=document.getElementById("STOP_BITS"); //save the displayed value- if no default (selected) then save first
			temp= (temp.selectedIndex===-1) ? document.getElementById("STOP_BITS").options[0].value : temp.options[temp.selectedIndex].value;
			SetPluginSetting(pluginName, "STOP_BITS", temp,0,0,null);

			temp=document.getElementById("PARITY"); //save the displayed value- if no default (selected) then save first
			temp= (temp.selectedIndex===-1) ? document.getElementById("PARITY").options[0].value : temp.options[temp.selectedIndex].value;
			SetPluginSetting(pluginName, "PARITY", temp,0,0,null);
		}	
		initialSave();
		projectorChanged();			
	</script><?
	
}
 
?>
<script>

updateVisibility(); //show/hide boxes according to settings

function validateIP(){
	var ipAddress = document.getElementById("IP").value;
    var ipRegex = /^(25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)$/;

    if (ipRegex.test(ipAddress)) {
        document.getElementById('IP_Warning').style.display = "none";        
    } else {
        document.getElementById('IP_Warning').style.display = "block";
    }
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

function projectorChanged(){
	GetSync("plugin.php?plugin=<?echo $pluginName ?>&page=functions.inc.php&action=create_scripts&nopage=1");
	location.reload();
	updateVisibility();	
}

function updateVisibility(){	
	var protocol="<?echo $PROJ_PROTOCOL?>";
	switch (protocol){		
		case "PJLINK":
			document.getElementById('ip').style.display = "block";
			document.getElementById('pass').style.display = "block";
			document.getElementById('serial').style.display = "none";
			document.getElementById('baud').style.display = "none";
			document.getElementById('char').style.display = "none";
			document.getElementById('stop').style.display = "none";
			document.getElementById('parity').style.display = "none";
			document.getElementById('port').style.display = "none";
			break;
		case "TCP":
			document.getElementById('ip').style.display = "block";
			document.getElementById('pass').style.display = "block";
			document.getElementById('serial').style.display = "none";
			document.getElementById('baud').style.display = "none";
			document.getElementById('char').style.display = "none";
			document.getElementById('stop').style.display = "none";
			document.getElementById('parity').style.display = "none";
			document.getElementById('port').style.display = "block";
			break;
		case "SERIAL":
			document.getElementById('ip').style.display = "none";
			document.getElementById('pass').style.display = "none";
			document.getElementById('serial').style.display = "block";
			document.getElementById('baud').style.display = "block";
			document.getElementById('char').style.display = "block";
			document.getElementById('stop').style.display = "block";
			document.getElementById('parity').style.display = "block";
			document.getElementById('port').style.display = "none";
	}
}
</script>
</html>
