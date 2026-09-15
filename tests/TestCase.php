<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $connection = $app['db']->connection();
        if ($connection->getDatabaseName() !== 'cashtrack_plan_b_testing'
            || $connection->getConfig('host') !== '127.0.0.1'
            || (string) $connection->getConfig('port') !== '3307'
            || $connection->selectOne('SELECT DATABASE() AS name')->name !== 'cashtrack_plan_b_testing') {
            throw new \RuntimeException('Refusing to run tests against an unverified database.');
        }
        return $app;
    }

    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
