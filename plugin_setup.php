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
	<script src="./js/projector_control.js"></script>
</head>
<script src="./js/projector_controls.js"></script>
<div id="projector" class="settings">
<legend>Projector control Support Instructions</legend>
<h2>Version <?echo $VERSION;?></h2>

<p>Known Issues:
<ul>
	<li>NONE</li>
</ul>
 <!-- Check Plugin -->
 <div class="justify-content-md-center row setting-item">
        <div class="col-md-6">
          <div class="card-title h6">
            Check Plugin
          </div>
          <div class="mb-2 text-muted card-subtitle">
            This will run a check on the plugin configuration and automatically report any issues.
          </div>
        </div>
        <div class="col-md-6">
          <div id="checkPluginResults">
            
          </div>
        </div>
 </div>
 
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
<div id="updatesAvailable"></div>
<div id="enabled">ENABLE PLUGIN <?PrintSettingCheckbox("Projector Control", "ENABLED",0, 0, "ON", "OFF", $pluginName);?></div></p>
<div id="proj">Projector: <? PrintSettingSelect("ProjectorType", "PROJECTOR", 1, 0, $defaultValue="-- Select Projector --", $values = getProjectors(), $pluginName, "projectorChanged"); ?></div></p>
<div id="serial">Serial Device: <? PrintSettingSelect("Device", "DEVICE", 0, 0, "", $values = get_serialDevices(), $pluginName); ?></div></p>
<div id="baud" style="display:none">Baud Rate: <? PrintSettingSelect("BaudRate", "BAUD_RATE", 0, 0, "19200", $values = getBaudRates(), $pluginName); ?></div></p>	
<div id="char" style="display:none">Char Bits: <? PrintSettingSelect("CharBits", "CHAR_BITS", 0, 0, "8", $values = getCharBits(), $pluginName); ?></div></p>	
<div id="stop" style="display:none">Stop Bits: <? PrintSettingSelect("StopBits", "STOP_BITS", 0, 0, "1", array("1"=>"1","2"=>"2"), $pluginName); ?></div></p>	
<div id="parity" style="display:none">Parity: <? PrintSettingSelect("Parity", "PARITY", 0, 0, $defaultValue="none", array("none"=>"none","even"=>"even","odd"=>"odd"), $pluginName); ?></div></p>	
<div class="alert alert-warning" id="IP_Warning" style="color:Red; display:none">
    <strong>Warning!</strong> This is an invalid IP
</div>
<div id="ip" style="display:none">Projector IP: <?  PrintSettingTextSaved("IP", 0,0, 15, 15, $pluginName, "", "validateIP"); ?>
	<input type="button" class="buttons" onClick='PingIP($("#IP").val(), 3);' value='Ping'>
</div></p>
<div id="pass" style="display:none">Projector Password: <?  PrintSettingTextSaved("PROJ_PASSWORD", 0,0, 30, 30, $pluginName); ?></div></p>
<div class="alert alert-warning" id="Port_Warning" style="color:Red; display:none">
    <strong>Warning!</strong> This is an invalid Port. Only numbers are valid
</div>
<div id="port" style="display:none">Port: <?  PrintSettingTextSaved("PORT", 0,0, 6, 6, $pluginName, "", "validatePort"); ?></div></p>
 
<p>To report a bug, please file it against the Projector Control plug-in project on Git:<a href="https://github.com/FalconChristmas/FPP-Plugin-Projector-Control/issues"> Projector Control Issues Link</a>

<script>

updateVisibility(); //show/hide boxes according to settings

function validateIP(){
	var ipAddress = document.getElementById("IP").value;
    var ipRegex = /^(?:|(?:(?:25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[0-1]?[0-9][0-9]?)))$/;

    if (ipRegex.test(ipAddress)) {
        document.getElementById('IP_Warning').style.display = "none";        
    } else {
        document.getElementById('IP_Warning').style.display = "block";
    }
}

function validatePort(){
	var ipAddress = document.getElementById("PORT").value;
	var ipRegex = /^(?:|(?:(?:\d+)))$/;
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
			break;
		default:
			document.getElementById('ip').style.display = "none";
			document.getElementById('pass').style.display = "none";
			document.getElementById('serial').style.display = "none";
			document.getElementById('baud').style.display = "none";
			document.getElementById('char').style.display = "none";
			document.getElementById('stop').style.display = "none";
			document.getElementById('parity').style.display = "none";
			document.getElementById('port').style.display = "none";
	}
}
</script>
</html>
