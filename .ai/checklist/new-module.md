# Checklist — Modul Fitur Baru

## Perencanaan

- [ ] Nama modul disetujui Tech Lead
- [ ] Modul tidak duplikat dengan yang sudah ada
- [ ] Dependency antar-modul sudah dipetakan
- [ ] Permission sudah didefinisikan (format `modul.resource.action`)

## Struktur

- [ ] `php artisan module:make NamaModul` dijalankan
- [ ] Folder `app/Domain/` lengkap (Models, Enums, Events, Exceptions, Contracts)
- [ ] Folder `app/Application/` lengkap (Actions, DTO, Services)
- [ ] Folder `app/Infrastructure/` lengkap (Persistence, Providers)
- [ ] Folder `app/Http/` lengkap (Controllers/Web, Controllers/Api, Requests, Resources)
- [ ] Folder `database/` (migrations, factories, seeders)
- [ ] Folder `routes/` (web.php, api.php)
- [ ] Folder `tests/` (Unit, Feature)

## Manifest

- [ ] `module.json` diisi lengkap
- [ ] `version` diset `1.0.0`
- [ ] `requires.core` minimal `^1.0`
- [ ] `permissions` didaftarkan

## Kode

- [ ] Model pakai `HasUlids`
- [ ] Model pakai `SoftDeletes` bila relevan
- [ ] Migration pakai `foreignUlid` untuk `school_id`
- [ ] Service menggunakan `DB::transaction()` untuk operasi multi-tabel
- [ ] Service menggunakan `SchoolContextService` untuk `school_id`
- [ ] Service menggunakan `AuditService` untuk setiap mutasi
- [ ] DTO pakai `readonly`
- [ ] FormRequest memvalidasi dengan whitelist
- [ ] Controller Web mengembalikan Inertia Response
- [ ] Controller Api mengembalikan `ApiResponse` atau Resource
- [ ] Route web dilindungi `auth`, `school.context`, `module.enabled:{slug}`
- [ ] Route api dilindungi `auth:sanctum`, `school.context`, `module.enabled:{slug}`

## Test

- [ ] Unit test untuk setiap Service method
- [ ] Feature test untuk setiap endpoint (Web & API)
- [ ] Test happy path + minimal 1 edge case
- [ ] Test otorisasi (user tanpa permission → 403)
- [ ] Test modul disabled → 404

## Dokumentasi

- [ ] `docs/modules/{slug}.md` dibuat
- [ ] Permission didaftarkan di `PermissionSeeder`
- [ ] Update `README.md` modul

## Verifikasi

```bash
php artisan school:module:list
php artisan school:module:discover
php artisan school:module:install namamodul
php artisan school:module:enable namamodul
php artisan test --filter=NamaModul
./vendor/bin/pint --test
./vendor/bin/phpstan analyse
```

- [ ] Semua perintah di atas hijau
- [ ] `tests/Architecture/ModuleBoundaryTest` hijau
