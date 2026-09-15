<?php

require __DIR__.'/../../vendor/autoload.php';

putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = $_SERVER['APP_ENV'] = 'testing';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$db = $app['db']->connection();
if ($db->getDatabaseName() !== 'cashtrack_plan_b_testing' || (string) $db->getConfig('port') !== '3307'
    || $db->selectOne('SELECT DATABASE() AS name')->name !== 'cashtrack_plan_b_testing') {
    throw new RuntimeException('Unsafe worker database');
}
$input = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
$user = App\Models\User::findOrFail($input['user_id']);
echo "READY\n";
flush();
try {
    $service = app(App\Services\InventoryService::class);
    $id = match ($input['action']) {
        'sell' => $service->sell($user, $input['data']),
        'receive' => $service->receive($user, $input['data']),
        'count' => $service->count($user, $input['data']),
        'review' => $service->review($user, $input['alert_id'], $input['data']),
    };
    echo json_encode(['result' => 'saved', 'id' => $id]);
    exit(0);
} catch (Illuminate\Validation\ValidationException $e) {
    echo json_encode(['result' => 'rejected', 'errors' => array_keys($e->errors())]);
    exit(2);
} catch (Throwable $e) {
    echo json_encode(['result' => 'error', 'type' => $e::class]);
    exit(1);
}
