<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = '/home/gonair/university-bot/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require '/home/gonair/university-bot/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
//$app = require_once __DIR__.'/../bootstrap/app.php';
$app = require_once '/home/gonair/university-bot/bootstrap/app.php';
$app->handleRequest(Request::capture());
