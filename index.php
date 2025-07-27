<?php

// Set the default timezone (optional, can be adjusted as needed)
date_default_timezone_set('UTC');

// Path to the Laravel application's base directory
$laravel = __DIR__.'/';

// Set up the autoloader for the application
require $laravel.'vendor/autoload.php';

// Set up the application instance
$app = require_once $laravel.'bootstrap/app.php';

// Handle the request through Laravel
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Send the response back to the browser
$response->send();

// Terminate the request
$kernel->terminate($request, $response);
