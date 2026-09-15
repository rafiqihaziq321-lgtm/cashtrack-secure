<?php

require __DIR__.'/../vendor/autoload.php';

if (file_exists(__DIR__.'/../bootstrap/cache/config.php') || getenv('APP_CONFIG_CACHE')) {
    throw new RuntimeException('Remove the Plan B config cache before testing.');
}
if (getenv('DB_DATABASE') !== 'cashtrack_plan_b_testing' || getenv('DB_HOST') !== '127.0.0.1' || getenv('DB_PORT') !== '3307') {
    throw new RuntimeException('Tests require the isolated Plan B tester on 127.0.0.1:3307.');
}

$viewPath = dirname(__DIR__).'/storage/framework/testing/views-'.getmypid();
if (! is_dir($viewPath) && ! mkdir($viewPath, 0775, true) && ! is_dir($viewPath)) {
    throw new RuntimeException('Cannot create isolated compiled views for tests.');
}
putenv('VIEW_COMPILED_PATH='.$viewPath);
$_ENV['VIEW_COMPILED_PATH'] = $_SERVER['VIEW_COMPILED_PATH'] = $viewPath;
