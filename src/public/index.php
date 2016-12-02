<?php

/**
 * A super-simple user-authentication solution, embedded into a small framework.
 *
 * HUGE
 *
 * @link https://github.com/panique/huge
 * @license http://opensource.org/licenses/MIT MIT License
 */

// Autoloader and initialization db
require __DIR__ . '/../app/bootstrap.php';

// start our application
new Application();
