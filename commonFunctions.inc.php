<?php

$myPid = getmypid();
$gitURL = "https://github.com/FalconChristmas/FPP-Plugin-Projector-Control.git";
$logFile = $settings['logDirectory']."/".$pluginName.".log";
$pluginName = basename(dirname(__FILE__));  //pjd 7-10-2019   added per dkulp
$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";

function PluginProgressDialogDone() {
    $('#pluginsProgressPopupCloseButton').prop("disabled", false);
    DisplayProgressDialog("pluginsProgressPopup", "Upgrade Plugin");
    StreamURL(url, 'pluginsProgressPopupText', 'PluginProgressDialogDone', 'PluginProgressDialogDone');
}
?>
