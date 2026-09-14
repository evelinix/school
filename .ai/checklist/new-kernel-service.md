# Checklist — Service Kernel Baru

Gunakan checklist ini saat menambah service/aplikasi ke **Kernel Core**.

## Sebelum Mulai

- [ ] Service ini **dibutuhkan lintas modul** (bukan spesifik satu modul)
- [ ] Service ini **tidak** menyentuh tabel milik modul fitur
- [ ] Sudah didiskusikan dengan Tech Lead

## Implementasi

### 1. Kontrak (Interface) di `app/Contracts/`

- [ ] Nama: `{Nama}RepositoryInterface` atau `{Nama}ServiceInterface`
- [ ] Namespace: `App\Contracts`
- [ ] Method return type eksplisit
- [ ] Docblock Bahasa Indonesia

### 2. Implementasi di `app/Repositories/` (untuk repository)

- [ ] Nama: `Eloquent{Nama}Repository`
- [ ] Namespace: `App\Repositories`
- [ ] `final class`
- [ ] Implement interface
- [ ] Tidak ada business logic (hanya data access)

### 3. Service di `app/Services/` (untuk service)

- [ ] Nama: `{Nama}Service`
- [ ] Namespace: `App\Services`
- [ ] `final class`
- [ ] Menggunakan kontrak, bukan implementasi langsung
- [ ] Transaksi via `DB::transaction()`
- [ ] Event dipicu setelah operasi sukses

### 4. Binding di `CoreServiceProvider`

- [ ] `$this->app->bind(Contract::class, Implementation::class)`
- [ ] `$this->app->singleton(Service::class)` (bila stateful)

### 5. Model di `app/Models/` (bila perlu)

- [ ] `HasUlids` trait
- [ ] `$fillable` eksplisit
- [ ] Casts didefinisikan

### 6. Migrasi di `database/migrations/`

- [ ] Migrasi baru (tidak mengedit yang lama)
- [ ] Foreign key `school_id` bila entitas tenant

### 7. Test

- [ ] Unit test di `tests/Unit/`
- [ ] Feature test di `tests/Feature/`
- [ ] Happy path + minimal 1 edge case

### 8. Audit (bila mutasi)

- [ ] Panggil `AuditService->log()` pada setiap `create/update/delete`

## Verifikasi

- [ ] Tidak ada `use Modules\*` di file baru
- [ ] `tests/Architecture/CoreBoundaryTest` hijau
- [ ] `./vendor/bin/pint --test` hijau
- [ ] `./vendor/bin/phpstan analyse` hijau
- [ ] `php artisan test` hijau

## Dokumentasi

- [ ] Update `docs/architecture/` bila relevan
- [ ] Update `docs/security/` bila menyentuh keamanan
