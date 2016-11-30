#!/usr/bin/php
<?php

$Vagrantfile = <<<EOF
# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|

  # Every Vagrant virtual environment requires a box to build off of.
  config.vm.box = "ubuntu/trusty64"

  # Create a private network, which allows host-only access to the machine using a specific IP.
  config.vm.network "private_network", ip: "192.168.33.111"

  # Share an additional folder to the guest VM. The first argument is the path on the host to the actual folder.
  # The second argument is the path on the guest to mount the folder.
  config.vm.synced_folder "./", "/var/www/html/pho"

  # Define the bootstrap file: A (shell) script that runs after first setup of your box (= provisioning)
  config.vm.provision :shell, path: "src/_install/bootstrap.sh"

end
EOF;

define("VERSION_INFO", "Pho Networks v".trim(file_get_contents("VERSION")).PHP_EOL);
define("SEPARATOR", "--------------------".PHP_EOL);

function help() {
	echo VERSION_INFO.SEPARATOR;
	echo "Available commands".PHP_EOL;
	echo "help, version: outputs this help screen".PHP_EOL;
	echo "vagrant: installs and/or runs the app on vagrant virtual environment".PHP_EOL;
	echo "build, rebuild: builds (or rebuilds, the same thing) the templates, content types to make the app ready to serve".PHP_EOL;
	exit(0);
}

function vagrant() {
	global $Vagrantfile;
	if(!file_exists("Vagrantfile"))
			file_put_contents("Vagrantfile", $Vagrantfile) 
				or die("Error writing the Vagrantfile in this directory, 
					make sure you have the privileges to write in this directory 
					or try reinstalling in a system folder of your own.".PHP_EOL);
	else
		echo "Vagrantfile already exists. Skipping.".PHP_EOL;
	passthru("vagrant up"); // not exec, because we want the user to see the output
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
	case "vagrant":
	case "--vagrant":
		vagrant();
	default:
		unknown();
}