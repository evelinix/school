# Arsitektur

## Peta Dua Kategori Kode

```
┌──────────────────────────────────────────────────────────┐
│                  KERNEL CORE (selalu aktif)              │
│                                                          │
│  app/Models/         app/Services/                       │
│  app/Contracts/      app/Repositories/                   │
│  app/Enums/          app/Events/                         │
│  app/Actions/        app/DTO/                            │
│  app/Http/           app/Console/Commands/               │
│  app/Providers/                                          │
│                                                          │
│  database/           routes/         config/             │
│                                                          │
│  → namespace: App\                                       │
│  → TIDAK punya lifecycle                                 │
│  → TIDAK BOLEH impor Modules\*                           │
└────────────────────┬─────────────────────────────────────┘
                     │  Modul bergantung pada Core
                     ▼
┌──────────────────────────────────────────────────────────┐
│            MODUL FITUR (add-on, punya lifecycle)         │
│                                                          │
│  Modules/Website/     Modules/Siswa/                     │
│  Modules/Guru/        Modules/Kelas/                     │
│  Modules/Raport/      Modules/Perpustakaan/              │
│  Modules/BankSoal/    Modules/Cat/                       │
│                                                          │
│  → namespace: Modules\{Nama}\                            │
│  → lifecycle: discover/install/enable/disable/uninstall  │
│  → BOLEH impor App\*                                     │
│  → TIDAK BOLEH impor modul lain langsung                 │
└──────────────────────────────────────────────────────────┘
```

## Arah Dependensi

```
Modul Fitur ──(boleh impor)──▶ Kernel Core
Modul Fitur ──(event bus)──▶  Modul Fitur lain
Kernel Core ──(DILARANG)──▶   Modul Fitur
```

## Layer di Dalam Modul

Setiap modul memiliki 4 layer:

```
┌─────────────────────────────────────┐
│  Http (Presentation)                │
│  Controller Web (Inertia) & API     │
├─────────────────────────────────────┤
│  Application                        │
│  Service, Action, DTO               │
├─────────────────────────────────────┤
│  Domain                             │
│  Model, Enum, Event, Contract       │
├─────────────────────────────────────┤
│  Infrastructure                     │
│  Repository, Provider               │
└─────────────────────────────────────┘
```

**Arah dependensi di dalam modul:** `Http → Application → Domain ← Infrastructure`

## Kernel Core di Struktur Laravel

Kernel Core **tidak** punya folder khusus. Ia memakai konvensi Laravel:

| Layer | Lokasi |
|-------|--------|
| Domain (model) | `app/Models/` |
| Domain (enum) | `app/Enums/` |
| Domain (event) | `app/Events/` |
| Domain (contract) | `app/Contracts/` |
| Domain (exception) | `app/Exceptions/` |
| Application (service) | `app/Services/` |
| Application (action) | `app/Actions/` |
| Application (DTO) | `app/DTO/` |
| Infrastructure (repository) | `app/Repositories/` |
| Infrastructure (provider) | `app/Providers/` |
| Presentation (HTTP) | `app/Http/` |
| Presentation (console) | `app/Console/Commands/` |

## Batas Dijaga oleh Architecture Test

- `tests/Architecture/CoreBoundaryTest.php` — Kernel tidak impor modul
- `tests/Architecture/ModuleBoundaryTest.php` — Modul tidak impor modul lain tanpa izin

## Contoh Alur Request Web (Inertia)

```
HTTP Request
    │
    ▼
routes/web.php
    │
    ▼
Middleware: auth, school.context, module.enabled:siswa
    │
    ▼
Modules\Siswa\Http\Controllers\Web\StudentWebController
    │
    ▼
Modules\Siswa\Application\Services\StudentService
    │  (memakai App\Services\SchoolContextService, App\Services\AuditService)
    ▼
Modules\Siswa\Infrastructure\Persistence\EloquentStudentRepository
    │
    ▼
PostgreSQL
```

## Contoh Alur Request API (Flutter)

```
HTTP Request
    │
    ▼
routes/api.php  (prefix: /api/v1)
    │
    ▼
Middleware: auth:sanctum, school.context, throttle, module.enabled:siswa
    │
    ▼
Modules\Siswa\Http\Controllers\Api\StudentApiController
    │
    ▼
Modules\Siswa\Application\Services\StudentService  (SAMA dengan web)
    │
    ▼
Modules\Siswa\Http\Resources\StudentResource
    │
    ▼
JSON Response
```

## Tenant Context

Semua query data bisnis **WAJIB** terfilter oleh `school_id`:

```php
// ✅ BENAR
Student::query()->where('school_id', $schoolContext->requireId())->get();

// ❌ SALAH
Student::query()->get(); // bocor lintas sekolah
```

Prioritaskan **global scope** (`scopeForSchool()`) di model bila pola sudah mapan.

## Realtime (Reverb)

Channel:
- `private-school.{schoolId}`
- `private-class.{classId}`
- `private-exam.{examId}`
- `private-user.{userId}`

## Queue Prioritas

```
high          → notifikasi kritis, OTP
default       → job umum
reports       → generate PDF raport
imports       → import Excel/CSV besar
notifications → email, push
cat           → processing CAT
```
