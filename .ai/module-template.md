# Template Modul Baru

> Status: `nwidart/laravel-modules` **sudah dikonfigurasi tetapi belum ada `Modules/`** — modul pertama perlu menyiapkan fondasi (lihat catatan akhir).

## Langkah

1. `php artisan module:make NamaModul`
2. Rapikan folder sesuai struktur di `.ai/architecture.md`.
3. Isi `module.json`.
4. Buat migrasi, model, service, action, controller, route.
5. Tambah test (Unit + Feature).
6. Daftarkan permission di `PermissionSeeder` (spatie/laravel-permission).
7. Update `docs/modules/{slug}.md` jika ada.

## Struktur Folder

```
Modules/NamaModul/
├── app/
│   ├── Domain/          # Models, Enums, Events, Exceptions, Contracts
│   ├── Application/     # Actions/, DTO/, Services/
│   ├── Infrastructure/  # Persistence/, Providers/
│   └── Http/            # Controllers/Web, Controllers/Api, Requests, Resources
├── database/            # migrations/, factories/, seeders/
├── routes/              # web.php, api.php (load via ServiceProvider)
├── tests/               # Unit/, Feature/
└── module.json
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
        "namamodul.resource.view",
        "namamodul.resource.create",
        "namamodul.resource.update",
        "namamodul.resource.delete"
    ],
    "active": 1
}
```

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
    }
}
```

## Koneksi Vite untuk Modul

- `vite-module-loader.js` membaca `modules_statuses.json` (root) + `Modules/*/vite.config.js`.
- **Status sekarang:** `vite.config.ts` BELUM meng-import `vite-module-loader` — loader belum aktif di build. Urutan mengaktifkannya:
  1. Kolok `collectModuleAssetsPaths` ke `vite.config.ts` (plugin `vite-module-loader`).
  2. Pastikan `modules_statuses.json` di root ada — tanpa file ini loader **error saat dijalankan** (bukan warning). File ini **wajib di-commit ke VCS** (state modul aktif untuk semua dev) — jangan masuk `.gitignore`.
  3. Modul yang aktif menambahkan path asset lewat `vite.config.js` miliknya sendiri.
- Tanpa langkah di atas, asset/halaman modul **tidak ikut ter-build** → frontend modul 404.
- Baris loader harus terhubung di entry `resources/js` bila modul membawa asset sendiri.

## Checklist

Lihat `.ai/checklist/new-module.md`.

## Catatan Fondasi (modul pertama)

Karena `Modules/` belum ada, menyiapkan modul pertama berarti sekaligus menyiapkan:

1. Buat folder `Modules/` + `modules_statuses.json` (isi `{"NamaModul": true}`).
2. Aktifkan koneksi Vite untuk modul (lihat bagian "Koneksi Vite untuk Modul" di atas) bila modul punya asset.
3. Pastikan konfigurasi modules sudah sesuai (`config/modules.php` sudah ada).
4. Pastikan `AppServiceProvider` meng-load `::class`/assets modul yang diperlukan (atau pakai ServiceProvider modul seperti di atas).
5. Migrasi global yang sudah ada (**users, passkeys, permission_tables**) TIDAK dipindah ke modul tanpa instruksi.