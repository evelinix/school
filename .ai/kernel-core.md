# Kernel Core — Panduan

Kernel Core adalah **fondasi aplikasi**. Ia **melebur ke struktur
default Laravel**, bukan di folder khusus. Kernel selalu aktif dan
tidak memiliki siklus install/enable/disable/uninstall.

## 1. Prinsip

| Prinsip | Penjelasan |
|---------|------------|
| **Selalu aktif** | Tidak ada perintah untuk mematikan Core |
| **Zero feature dependency** | Tidak boleh mengimpor `Modules\*` |
| **Stable contract** | Kontrak di `app/Contracts/` stabil (semver) |
| **Shared kernel** | Menyediakan User, School, Permission, Audit lintas modul |
| **Bukan modul** | Tidak muncul di `school:module:list` |

## 2. Peta Folder Kernel Core

```
app/
├── Models/
│   ├── User.php
│   ├── School.php
│   ├── Module.php
│   ├── Setting.php
│   └── AuditLog.php
├── Enums/
│   ├── SchoolLevel.php
│   ├── UserStatus.php
│   ├── ModuleStatus.php
│   └── AuditAction.php
├── Events/
│   ├── SchoolCreated.php
│   ├── UserCreated.php
│   ├── UserLoggedIn.php
│   ├── ModuleInstalled.php
│   ├── ModuleEnabled.php
│   └── ModuleDisabled.php
├── Exceptions/
│   ├── SchoolNotFoundException.php
│   ├── UserNotFoundException.php
│   └── ModuleNotFoundException.php
├── Contracts/
│   ├── SchoolRepositoryInterface.php
│   ├── UserRepositoryInterface.php
│   ├── ModuleRepositoryInterface.php
│   ├── SettingRepositoryInterface.php
│   └── AuditRepositoryInterface.php
├── Services/
│   ├── SchoolService.php
│   ├── UserService.php
│   ├── SettingService.php
│   ├── AuditService.php
│   ├── SchoolContextService.php
│   ├── ModuleManager.php
│   ├── ModuleDependencyResolver.php
│   ├── FileService.php
│   └── HealthService.php
├── Repositories/
│   ├── EloquentSchoolRepository.php
│   ├── EloquentUserRepository.php
│   ├── EloquentModuleRepository.php
│   ├── EloquentSettingRepository.php
│   └── EloquentAuditRepository.php
├── Actions/
│   ├── CreateSchoolAction.php
│   ├── CreateUserAction.php
│   └── RecordAuditAction.php
├── DTO/
│   ├── SchoolData.php
│   ├── UserData.php
│   └── AuditData.php
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   ├── Requests/
│   ├── Resources/
│   └── Responses/
├── Console/Commands/
│   ├── ModuleListCommand.php
│   ├── ModuleDiscoverCommand.php
│   ├── ModuleInstallCommand.php
│   ├── ModuleEnableCommand.php
│   ├── ModuleDisableCommand.php
│   ├── ModuleUninstallCommand.php
│   └── ModuleHealthCommand.php
└── Providers/
    ├── AppServiceProvider.php
    ├── EventServiceProvider.php
    └── CoreServiceProvider.php
```

Folder pendukung kernel di luar `app/`:

```
database/
├── migrations/           ← migrasi kernel (users, schools, modules, settings, audit_logs, permission)
├── factories/            ← UserFactory, SchoolFactory
└── seeders/              ← SchoolSeeder, RoleSeeder, PermissionSeeder, RolePermissionSeeder, AdminSeeder

config/
├── core.php              ← konfigurasi kernel

routes/
├── web.php               ← route auth, dashboard, school, user, role, setting, module
├── api.php               ← route API auth, user
├── channels.php          ← broadcast channel
└── console.php           ← scheduler
```

## 3. Aturan Wajib

### 3.1 Kernel TIDAK BOLEH impor Modules

```php
// ❌ SALAH — di app/Services/SchoolService.php
use Modules\Siswa\Domain\Models\Student;

// ✅ BENAR — di modul Siswa
use App\Services\SchoolContextService;
use App\Domain\Models\User;
```

Batas ini dijaga oleh `tests/Architecture/CoreBoundaryTest.php`.

### 3.2 Kernel menyediakan kontrak stabil

Setiap service kernel yang dipakai modul wajib didaftarkan di
`app/Contracts/` sebagai interface, dan di-bind di `CoreServiceProvider`.

```php
// app/Contracts/SchoolRepositoryInterface.php
interface SchoolRepositoryInterface { /* ... */ }

// app/Providers/CoreServiceProvider.php
$this->app->bind(
    SchoolRepositoryInterface::class,
    EloquentSchoolRepository::class,
);
```

### 3.3 Kernel tidak menyentuh data modul

Kernel tidak boleh query tabel milik modul fitur
(`students`, `books`, `exams`, dst.).

Jika modul butuh reaksi terhadap perubahan kernel → gunakan Domain Event.

### 3.4 Semua mutasi kernel tercatat di audit

Gunakan `AuditService`:

```php
$this->audit->log(
    action: AuditAction::Update,
    auditableType: School::class,
    auditableId: $school->id,
    oldValues: $old,
    newValues: $new,
);
```

## 4. Menambah Service Kernel Baru

Lihat `.ai/checklist/new-kernel-service.md`.

## 5. Yang TIDAK Ada di Kernel Core

- ❌ Folder `app/Core/` (dihapus dari konsep)
- ❌ Folder `Modules/Core/` (tidak ada)
- ❌ File `module.json` untuk Core
- ❌ Perintah `school:module:enable core` (Core tidak dikelola)
- ❌ Baris `core` di output `school:module:list`

## 6. Ringkasan Perbandingan

| Aspek | Kernel Core | Modul Fitur |
|-------|-------------|-------------|
| Lokasi | `app/`, `database/`, `routes/`, `config/` | `Modules/{Nama}/` |
| Namespace | `App\...` | `Modules\{Nama}\...` |
| Lifecycle | Tidak ada (selalu aktif) | install/enable/disable/uninstall |
| Boleh di-disable? | ❌ | ✅ |
| Manifest | Tidak ada | `module.json` |
| Boleh impor modul? | ❌ | ✅ (hanya Core) |
| Entry point | `bootstrap/providers.php` | Module engine |
