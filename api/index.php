<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH) ?: '/';
if ($requestPath !== '/up' && ! str_starts_with($requestPath, '/api/')) {
    $_SERVER['REQUEST_URI'] = '/api'.($requestUri === '/' ? '' : $requestUri);
}

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
putenv('LOG_CHANNEL=stderr');
putenv('HASH_DRIVER=argon2id');

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->useStoragePath($runtime);
$app->handleRequest(Request::capture());
