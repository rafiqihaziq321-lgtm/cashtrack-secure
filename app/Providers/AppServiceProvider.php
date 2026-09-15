<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $connection = config('database.default');
        $database = config("database.connections.$connection.database");
        if ($database === 'cashtrack_secure' || (app()->environment('testing') && $database !== 'cashtrack_plan_b_testing')) {
            throw new \RuntimeException('Unsafe database for Plan B. Check environment and config cache.');
        }
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event): void {
            if ($event->user instanceof \App\Models\User) {
                app(\App\Services\InventoryService::class)->audit($event->user->id, 'akaun.log_masuk', 'user', $event->user->id);
            }
        });
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event): void {
            if ($event->user instanceof \App\Models\User) {
                app(\App\Services\InventoryService::class)->audit($event->user->id, 'akaun.log_keluar', 'user', $event->user->id);
            }
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
