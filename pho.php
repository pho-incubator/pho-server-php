#!/usr/bin/php
<?php

define("VERSION_INFO", "Pho Networks v".trim(file_get_contents("VERSION")).PHP_EOL);
define("SEPARATOR", "--------------------".PHP_EOL);

function help() {
	echo VERSION_INFO.SEPARATOR;
	echo "Available commands".PHP_EOL;
	echo "help, version: outputs this help screen".PHP_EOL;
	echo "vagrant-install: installs vagrant version of the app".PHP_EOL;
	echo "build-templates: builds the templates".PHP_EOL;
	exit(0);
}

function unknown() {
	global $version_info;
	echo $version_info.PHP_EOL;
	echo "WARNING: Unknown command".PHP_EOL;
	exit(1);
}

if(count($argv)==1) {
	help();
}

switch($argv[1]) {
	case "help":
	case "--help":
	case "version":
	case "--version":
		help();
	default:
		unknown();
}