<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Vercel functions have a read-only application filesystem. Laravel's
// transient files therefore live under /tmp for each function instance.
$runtime = '/tmp/family-quest';
foreach (['framework/cache', 'framework/sessions', 'framework/views', 'logs'] as $directory) {
    if (! is_dir("{$runtime}/{$directory}")) {
        mkdir("{$runtime}/{$directory}", 0777, true);
    }
}

putenv("VIEW_COMPILED_PATH={$runtime}/framework/views");
putenv("APP_SERVICES_CACHE={$runtime}/services.php");
putenv("APP_PACKAGES_CACHE={$runtime}/packages.php");
putenv("APP_CONFIG_CACHE={$runtime}/config.php");
putenv("APP_ROUTES_CACHE={$runtime}/routes.php");
putenv("APP_EVENTS_CACHE={$runtime}/events.php");

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->useStoragePath($runtime);
$app->handleRequest(Request::capture());
