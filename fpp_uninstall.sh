#!/usr/bin/php
<?php

$eventDirectory = "/home/fpp/media/events"; //once working, change to variable reference


$dir = new DirectoryIterator ($eventDirectory); //iterate through event files
foreach ($dir as $fileinfo) {
	$fileName = $eventDirectory . "/" . $fileinfo -> getfilename();
	$fileContents = file_get_contents($fileName);
	if (strpos($fileContents, "PROJECTOR-") !== false) {
		unlink($fileName);
	}
}	

?>
