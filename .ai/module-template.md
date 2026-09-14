# Template Modul Baru

Modul fitur hidup di `Modules/{Nama}/`. Setiap modul memiliki siklus
hidup dan **wajib** mengikuti template ini.

## Langkah

1. `php artisan module:make NamaModul`
2. Rapikan folder sesuai struktur di `.ai/architecture.md`.
3. Isi `module.json` dengan benar (lihat di bawah).
4. Buat migrasi, model, service, controller, route.
5. Tambah test.
6. Daftarkan permission di `PermissionSeeder`.
7. Update dokumentasi modul di `docs/modules/{slug}.md`.
8. Jalankan `.ai/checklist/new-module.md`.

## Struktur Folder

```
Modules/NamaModul/
├── app/
│   ├── Domain/
│   │   ├── Models/
│   │   ├── Enums/
│   │   ├── Events/
│   │   ├── Exceptions/
│   │   └── Contracts/
│   ├── Application/
│   │   ├── Actions/
│   │   ├── DTO/
│   │   └── Services/
│   ├── Infrastructure/
│   │   ├── Persistence/
│   │   └── Providers/
│   └── Http/
│       ├── Controllers/
│       │   ├── Web/
│       │   └── Api/
│       ├── Requests/
│       ├── Resources/
│       └── Policies/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── routes/
│   ├── web.php
│   └── api.php
├── resources/
│   ├── js/
│   │   └── Pages/
│   └── lang/
├── tests/
│   ├── Unit/
│   └── Feature/
├── module.json
└── composer.json
```

## `module.json` Standar

```json
{
    "name": "NamaModul",
    "alias": "namamodul",
    "description": "Deskripsi singkat modul dalam Bahasa Indonesia.",
    "version": "1.0.0",
    "priority": 10,
    "providers": [
        "Modules\\NamaModul\\Providers\\NamaModulServiceProvider"
    ],
    "requires": {
        "core": "^1.0"
    },
    "permissions": [
        "namamodul.resource.lihat",
        "namamodul.resource.tambah",
        "namamodul.resource.ubah",
        "namamodul.resource.hapus"
    ],
    "active": 1
}
```

**Catatan penting:**
- `requires.core` **wajib** ada sebagai penanda Kernel Core.
- `requires` untuk modul lain hanya bila memang ada dependency.
- `permissions` didaftarkan di sini DAN di `PermissionSeeder` Kernel Core.

## Service Provider Modul

```php
<?php

declare(strict_types=1);

namespace Modules\NamaModul\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\NamaModul\Domain\Contracts\NamaModulRepositoryInterface;
use Modules\NamaModul\Infrastructure\Persistence\EloquentNamaModulRepository;

final class NamaModulServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            NamaModulRepositoryInterface::class,
            EloquentNamaModulRepository::class,
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'namamodul');
    }
}
```

## Route Modul Wajib Dilindungi

**`Modules/NamaModul/routes/web.php`**

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\NamaModul\Http\Controllers\Web\ResourceWebController;

Route::middleware(['auth', 'school.context', 'module.enabled:namamodul'])
    ->prefix('app/namamodul')
    ->name('namamodul.')
    ->group(function (): void {
        Route::resource('resources', ResourceWebController::class);
    });
```

**`Modules/NamaModul/routes/api.php`**

```php
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\NamaModul\Http\Controllers\Api\ResourceApiController;

Route::middleware(['auth:sanctum', 'school.context', 'module.enabled:namamodul'])
    ->prefix('v1')
    ->group(function (): void {
        Route::apiResource('resources', ResourceApiController::class);
    });
```

## Koneksi Vite untuk Modul

- `vite-module-loader.js` membaca `modules_statuses.json` (root) + `Modules/*/vite.config.js`.
- **Status sekarang:** `vite.config.ts` BELUM meng-import `vite-module-loader` — loader belum aktif di build. Urutan mengaktifkannya:
  1. Kolok `collectModuleAssetsPaths` ke `vite.config.ts` (plugin `vite-module-loader`).
  2. Pastikan `modules_statuses.json` di root ada — tanpa file ini loader **error saat dijalankan** (bukan warning). File ini **wajib di-commit ke VCS** (state modul aktif untuk semua dev) — jangan masuk `.gitignore`.
  3. Modul yang aktif menambahkan path asset lewat `vite.config.js` miliknya sendiri.
- Tanpa langkah di atas, asset/halaman modul **tidak ikut ter-build** → frontend modul 404.

## Checklist

Lihat `.ai/checklist/new-module.md`.
