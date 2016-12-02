<?php

// local and vendor psr4 autoloaders
require __DIR__ . '/../app/autoload.php';

// Initialising db connection
DatabaseFactory::getFactory()->getConnection();