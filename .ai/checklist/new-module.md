# Checklist — Modul Baru

## Struktur

- [ ] `Modules/NamaModul/` dibuat via `php artisan module:make NamaModul`
- [ ] `app/Domain/` lengkap (Models, Enums, Events, Exceptions, Contracts)
- [ ] `app/Application/` lengkap (Actions, DTO, Services)
- [ ] `app/Infrastructure/` lengkap (Persistence, Providers)
- [ ] `app/Http/` lengkap (Controllers/Web, Controllers/Api, Requests, Resources)
- [ ] `database/` (migrations, factories, seeders)
- [ ] `routes/` (web.php, api.php) — dimuat via ServiceProvider
- [ ] `tests/` (Unit, Feature)
- [ ] `modules_statuses.json` ada & modul `active: 1` bila fondasi pertama kali

## Manifest

- [ ] `module.json` diisi lengkap
- [ ] `version` diset `1.0.0`
- [ ] `requires` mencantumkan minimal `core`
- [ ] `permissions` didaftarkan (nama `modul.bagian.aksi`)

## Kode

- [ ] `declare(strict_types=1);`
- [ ] Model pakai `HasUlids` (tabel bisnis baru)
- [ ] Model pakai `SoftDeletes` bila relevan
- [ ] Migration pakai `foreignUlid` untuk `school_id`
- [ ] Semua query bisnis terfilter `school_id`
- [ ] Service pakai `DB::transaction()` untuk operasi multi-tabel
- [ ] DTO pakai `readonly`
- [ ] FormRequest validasi whitelist + `authorize()` permission
- [ ] Controller Web mengembalikan Inertia response; Api → Resource
- [ ] Komunikasi antar modul via Domain Event, bukan direct call
- [ ] Docblock & komentar Bahasa Indonesia

## Test

- [ ] Unit test setiap Service method
- [ ] Feature test setiap endpoint (Web & API)
- [ ] Happy path + minimal 1 edge case
- [ ] Test otorisasi (tanpa permission → 403)

## Seeding & Otorisasi (spatie/laravel-permission)

- [ ] `PermissionSeeder` deterministik — naming konsisten `modul.bagian.aksi` (mis. `core.role.index`), tanpa faker/random
- [ ] Assignment Role↔Permission dilakukan di seeder, bukan manual/tinker
- [ ] Verifikasi jalan tanpa error: `php artisan db:seed --class=PermissionSeeder`
- [ ] Feature test otorisasi: dengan permission → sukses; tanpa → 403
- [ ] Ralat klaim "belum ada role" di `.ai/context.md` di commit yang sama saat seeder pertama dirilis

## Dokumentasi

- [ ] Update `docs/modules/{slug}.md`
- [ ] Daftarkan permission di `PermissionSeeder`
- [ ] Update `.ai/` bila ada perubahan arsitektur (sinkron garis status `.ai/context.md` — lihat `.ai/workflow.md` → "Menjaga `.ai/` Tetap Aktual")

## Final Check

- [ ] `vendor/bin/pint --parallel --test` ✅
- [ ] `vendor/bin/phpstan analyse` ✅
- [ ] `bun run types:check` ✅
- [ ] `php artisan test --compact` ✅