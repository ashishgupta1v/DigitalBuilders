<?php

declare(strict_types=1);

// Vercel serverless entry point for Laravel.
// The deployment file system is read-only except /tmp, so route all writable
// framework paths (views/cache/sessions/logs) to /tmp.
$storagePath = '/tmp/storage';
$bootstrapCachePath = '/tmp/bootstrap/cache';

foreach ([
	$storagePath,
	$storagePath.'/framework',
	$storagePath.'/framework/cache',
	$storagePath.'/framework/cache/data',
	$storagePath.'/framework/sessions',
	$storagePath.'/framework/views',
	$storagePath.'/logs',
	'/tmp/bootstrap',
	$bootstrapCachePath,
] as $dir) {
	if (! is_dir($dir)) {
		@mkdir($dir, 0777, true);
	}
}

putenv('LARAVEL_STORAGE_PATH='.$storagePath);
$_ENV['LARAVEL_STORAGE_PATH'] = $storagePath;
$_SERVER['LARAVEL_STORAGE_PATH'] = $storagePath;

putenv('VIEW_COMPILED_PATH='.$storagePath.'/framework/views');
$_ENV['VIEW_COMPILED_PATH'] = $storagePath.'/framework/views';
$_SERVER['VIEW_COMPILED_PATH'] = $storagePath.'/framework/views';

// Ensure framework cache files are writable in serverless runtime.
putenv('APP_SERVICES_CACHE='.$bootstrapCachePath.'/services.php');
$_ENV['APP_SERVICES_CACHE'] = $bootstrapCachePath.'/services.php';
$_SERVER['APP_SERVICES_CACHE'] = $bootstrapCachePath.'/services.php';

putenv('APP_PACKAGES_CACHE='.$bootstrapCachePath.'/packages.php');
$_ENV['APP_PACKAGES_CACHE'] = $bootstrapCachePath.'/packages.php';
$_SERVER['APP_PACKAGES_CACHE'] = $bootstrapCachePath.'/packages.php';

putenv('APP_CONFIG_CACHE='.$bootstrapCachePath.'/config.php');
$_ENV['APP_CONFIG_CACHE'] = $bootstrapCachePath.'/config.php';
$_SERVER['APP_CONFIG_CACHE'] = $bootstrapCachePath.'/config.php';

putenv('APP_ROUTES_CACHE='.$bootstrapCachePath.'/routes.php');
$_ENV['APP_ROUTES_CACHE'] = $bootstrapCachePath.'/routes.php';
$_SERVER['APP_ROUTES_CACHE'] = $bootstrapCachePath.'/routes.php';

putenv('APP_EVENTS_CACHE='.$bootstrapCachePath.'/events.php');
$_ENV['APP_EVENTS_CACHE'] = $bootstrapCachePath.'/events.php';
$_SERVER['APP_EVENTS_CACHE'] = $bootstrapCachePath.'/events.php';

// Serverless-safe runtime defaults: on Vercel, avoid hard dependency on
// database-backed session/cache/queue drivers for basic web requests.
if (getenv('VERCEL')) {
	putenv('SESSION_DRIVER=cookie');
	$_ENV['SESSION_DRIVER'] = 'cookie';
	$_SERVER['SESSION_DRIVER'] = 'cookie';

	putenv('CACHE_STORE=file');
	$_ENV['CACHE_STORE'] = 'file';
	$_SERVER['CACHE_STORE'] = 'file';

	putenv('QUEUE_CONNECTION=sync');
	$_ENV['QUEUE_CONNECTION'] = 'sync';
	$_SERVER['QUEUE_CONNECTION'] = 'sync';
}

require __DIR__.'/../public/index.php';
