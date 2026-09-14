# Plan: super-admin + monitoring (Pulse access + spatie/laravel-health)

## Konteks
- Permission/role belum ada di DB (0 role, 0 permission); User model belum pakai `HasRoles`.
- Pulse v1.8.1 terpasang, recorder Reverb (connections+messages) sudah aktif.
- Monitoring tambahan untuk health PostgreSQL, Redis, Meilisearch, disk, Horizon memakai spatie/laravel-health (disetujui user).

## Daftar Tugas
1. `composer require spatie/laravel-health` + publish config/migration, publish assets.
2. Buat `database/seeders/PermissionSeeder.php` idempotent:
   - permission kernel (user/role/setting/school/module/audit-log, action English, domain Bahasa Indonesia).
   - role `super-admin` (guard `web`) + sync semua permission.
   - assign user evelin (findOrCreate email `evelin@school.jmediatech.online`) ke `super-admin`.
   - panggil dari `DatabaseSeeder`.
3. `app/Models/User.php` + trait `HasRoles`.
4. `app/Providers/AppServiceProvider.php`:
   - `Gate::before` super-admin → true (permission all, termasuk future permission).
   - define `viewPulse` (super-admin / permission).
5. `config/health.php`: checks DB(postgres), Redis, Cache, Meilisearch, UsedDiskSpace, Horizon; simpan hasil ke DB.
6. Route/dashboard health (`/health`) dengan authorization super-admin (sesuaikan API paket).
7. Test Pest: gate super-admin + seeder idempotent.
8. Sinkron `.ai/context.md` (permission ter-seed, health terpasang) di commit yang sama.
9. Gate lokal: pint + phpstan + pest.

## Target
Role `super-admin` aktif, evelin jadi super-admin, `/pulse`, `/telescope`, `/horizon`, `/health` bisa diakses evelin; Postgres/Redis/Meilisearch/disk/Horizon termonitor; Reverb live traffic lewat Pulse.

## Catatan Eksekusi
- `pulse:check` perlu berjalan untuk card Servers; `reverb:start` untuk Reverb.
- Jangan ubah migrasi lama.