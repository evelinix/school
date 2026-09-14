<?php

namespace App\Providers;

use App\Health\Checks\HorizonCheck as AppHorizonCheck;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\MeilisearchCheck;
use Spatie\Health\Checks\Checks\RedisCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthorization();
        $this->configureHealthChecks();
    }

    /**
     * Daftarkan bypass otorisasi untuk super-admin dan gate dashboard monitoring.
     */
    protected function configureAuthorization(): void
    {
        Gate::before(fn ($user, $ability) => $user->isSuperAdmin() ? true : null);

        // Gate dashboard monitoring dibuat default-mengizinkan. Penegakan
        // permission sesungguhnya ditangani satu titik: middleware 'superaccess'
        // (EnsureSuperAccess) di config/{horizon,telescope,pulse}.php.
        //
        // Sentinel (route dashboard) membiarkan akses pada env non-'local'
        // (APP_ENV=development), jadi tidak perlu di-override.
        Gate::define('viewHorizon', fn () => true);
        Gate::define('viewPulse', fn () => true);
        Gate::define('viewTelescope', fn () => true);
    }

    /**
     * Daftarkan health check infrastruktur (database, redis, meilisearch, dll).
     */
    protected function configureHealthChecks(): void
    {
        Health::checks([
            DatabaseCheck::new()
                ->connectionName(config('database.default'))
                ->label('PostgreSQL'),

            RedisCheck::new()
                ->connectionName('default')
                ->label('Redis'),

            MeilisearchCheck::new()
                ->url(config('scout.meilisearch.host').'/health')
                ->token(config('scout.meilisearch.key'))
                ->label('Meilisearch'),

            CacheCheck::new()
                ->driver(config('cache.default'))
                ->label('Cache'),

            UsedDiskSpaceCheck::new()
                ->warnWhenUsedSpaceIsAbovePercentage(70)
                ->failWhenUsedSpaceIsAbovePercentage(90)
                ->label('Disk Usage'),

            AppHorizonCheck::new()
                ->label('Horizon'),
        ]);
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

        Password::defaults(
            fn (): ?Password => app()->isProduction()
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
