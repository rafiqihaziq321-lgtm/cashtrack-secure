<?php

require __DIR__.'/../vendor/autoload.php';

if (file_exists(__DIR__.'/../bootstrap/cache/config.php') || getenv('APP_CONFIG_CACHE')) {
    throw new RuntimeException('Remove the Plan B config cache before testing.');
}
if (getenv('DB_DATABASE') !== 'cashtrack_plan_b_testing' || getenv('DB_HOST') !== '127.0.0.1' || getenv('DB_PORT') !== '3307') {
    throw new RuntimeException('Tests require the isolated Plan B tester on 127.0.0.1:3307.');
}
