# 📁 AGENTS.md dan Folder `.ai/*`

Berikut adalah struktur file yang saya rancang untuk memandu **AI coding agent** (Claude Code, Cursor, Windsurf, Aider, dsb) saat bekerja pada proyek **School Platform Enterprise**. Semua konten dalam **Bahasa Indonesia** sesuai standar proyek.

---

## 🗂️ Struktur File yang Dibuat

```text
school-platform/
├── AGENTS.md                         # Entry point utama untuk AI agent
└── .ai/
    ├── README.md                     # Panduan penggunaan folder .ai
    ├── context.md                    # Konteks proyek & stack
    ├── architecture.md               # Detail arsitektur
    ├── coding-standards.md           # Standar penulisan kode
    ├── module-template.md            # Template pembuatan modul
    ├── patterns.md                   # Pattern yang direkomendasikan
    ├── anti-patterns.md              # Yang harus dihindari
    ├── testing.md                    # Strategi testing
    ├── workflow.md                   # Alur kerja development
    ├── commands.md                   # Cheatsheet CLI
    ├── api-contract.md               # Kontrak API & DTO
    ├── security.md                   # Aturan keamanan wajib
    ├── database.md                   # Konvensi database
    ├── glossary.md                   # Daftar istilah domain
    └── checklist/
        ├── new-module.md             # Checklist buat modul baru
        ├── new-endpoint.md           # Checklist buat endpoint baru
        ├── new-migration.md          # Checklist buat migrasi
        └── pull-request.md           # Checklist sebelum PR
```

---

## 📄 `AGENTS.md` (Root)

```markdown
# AGENTS.md — Panduan untuk AI Coding Agent

> **Selamat datang, AI Agent.**  
> File ini adalah **sumber kebenaran utama** saat Anda bekerja pada repositori
> **School Platform Enterprise**. Baca file ini sampai selesai sebelum
> menghasilkan kode, membuat file baru, atau mengubah konfigurasi.

---

## 1. Ringkasan Proyek

**School Platform Enterprise** adalah sistem manajemen sekolah modular
(Modular Monolith) untuk single-school dan multi-school.

| Aspek | Nilai |
|-------|-------|
| Backend | Laravel 13, PHP 8.4 |
| Database | PostgreSQL 18 |
| Cache / Queue | Redis 7+ |
| Realtime | Laravel Reverb |
| Admin Web | Inertia.js 3 + React 19 + TypeScript + Vite |
| Mobile | REST API `/api/v1` untuk Flutter 3.45 |
| CI/CD | Jenkins + GitLab Webhook |
| Deployment | Fedora 44 + Nginx + PHP-FPM |
| Arsitektur | Modular Monolith (`nwidart/laravel-modules`) |
| Bahasa Dokumentasi | **Bahasa Indonesia (WAJIB)** |

---

## 2. Aturan Emas (Golden Rules)

Agent **WAJIB** mematuhi aturan berikut pada setiap perubahan:

1. **Bahasa Indonesia** untuk semua komentar, docblock, commit message,
   nama variabel bisnis (bukan teknis), README, dan dokumentasi.
2. **Explicit over Magic** — hindari facade tersembunyi, reflection,
   atau konvensi implisit yang tidak jelas.
3. **Security First** — validasi input, otorisasi eksplisit, hindari
   `request()->all()` tanpa validasi.
4. **Maintainability First** — kode harus mudah dibaca ulang 6 bulan
   kemudian oleh developer lain.
5. **Production Ready** — kode tidak boleh mengandung `dd()`, `dump()`,
   `var_dump()`, `console.log()` (kecuali di logger terstruktur), atau
   TODO tanpa tiket.
6. **Testable** — setiap service/action wajib punya test minimal
   happy path + 1 edge case.
7. **Jangan ubah file di luar scope** yang diminta.
8. **Jangan hapus file** tanpa instruksi eksplisit; gunakan
   `deprecated` atau ajukan konfirmasi.
9. **Migration tidak boleh diubah setelah di-commit** ke `main` —
   buat migrasi baru.
10. **Satu tanggung jawab per class** — class yang menangani HTTP tidak
    boleh menangani query database langsung.

---

## 3. Batas Perubahan (Scope Boundary)

| ✅ Boleh | ❌ Tidak Boleh |
|---------|----------------|
| Menambah file baru sesuai template di `.ai/module-template.md` | Mengubah `bootstrap/app.php` tanpa instruksi |
| Memodifikasi file dalam modul target | Menyentuh modul lain tanpa alasan |
| Menambah test | Mengubah konfigurasi Jenkins tanpa permintaan |
| Menambah migrasi baru | Mengedit migrasi yang sudah ada |
| Menambah permission di seeder | Mengubah struktur `.env.example` |

Jika ragu, **berhenti dan tanyakan** kepada user.

---

## 4. Alur Kerja Standar

1. Baca `AGENTS.md` (file ini).
2. Baca `.ai/context.md` dan `.ai/architecture.md`.
3. Tentukan modul target (mis. `Modules/Siswa`).
4. Baca `.ai/module-template.md` dan `.ai/patterns.md`.
5. Implementasikan sesuai layer:
   `Domain → Application → Infrastructure → Http`.
6. Tambah test di `.ai/testing.md`.
7. Update dokumentasi terkait.
8. Jalankan checklist `.ai/checklist/pull-request.md`.

---

## 5. Folder Referensi AI

Semua konteks tambahan ada di folder `.ai/`:

- `.ai/context.md` — konteks proyek lengkap
- `.ai/architecture.md` — arsitektur & layer
- `.ai/coding-standards.md` — standar kode
- `.ai/module-template.md` — template modul
- `.ai/patterns.md` — pattern direkomendasikan
- `.ai/anti-patterns.md` — yang harus dihindari
- `.ai/testing.md` — strategi testing
- `.ai/workflow.md` — alur kerja
- `.ai/commands.md` — cheatsheet CLI
- `.ai/api-contract.md` — kontrak API
- `.ai/security.md` — aturan keamanan
- `.ai/database.md` — konvensi database
- `.ai/glossary.md` — istilah domain
- `.ai/checklist/*` — checklist operasional

---

## 6. Kontak & Eskalasi

- **Perubahan arsitektur** → ajukan ADR baru di `docs/architecture/adr/`.
- **Perubahan schema** → minta konfirmasi user sebelum migrasi.
- **Perubahan dependency** → minta konfirmasi (dampak supply chain).
- **Kebingungan requirement** → **berhenti dan tanyakan**, jangan berasumsi.

---

## 7. Definisi "Selesai" (Definition of Done)

Sebuah tugas dianggap selesai jika:

- [ ] Kode lolos `./vendor/bin/pint --test`
- [ ] Kode lolos `./vendor/bin/phpstan analyse`
- [ ] Kode lolos `npx tsc --noEmit`
- [ ] Test baru & lama hijau (`php artisan test`)
- [ ] Tidak ada `TODO` tanpa tiket
- [ ] Komentar & docblock dalam Bahasa Indonesia
- [ ] Dokumentasi terkait diperbarui
- [ ] Tidak ada secret/credential di kode

---

**Versi:** 1.0  
**Terakhir diperbarui:** 2026-01-01  
**Pemilik:** Tech Lead
```

---

## 📄 `.ai/README.md`

```markdown
# Folder `.ai/` — Konteks untuk AI Coding Agent

Folder ini berisi konteks granular yang dibaca oleh AI agent
(Claude Code, Cursor, Windsurf, Aider, dsb) saat bekerja pada proyek
**School Platform Enterprise**.

## Cara Pakai

AI agent membaca `AGENTS.md` di root terlebih dahulu, lalu masuk ke
folder ini untuk konteks spesifik. Urutan pembacaan yang dianjurkan:

1. `context.md`
2. `architecture.md`
3. `coding-standards.md`
4. File spesifik sesuai tugas (mis. `testing.md` bila menulis test)

## Konvensi Penulisan

- **Bahasa Indonesia** untuk seluruh konten.
- Format Markdown dengan heading `##` dan `###`.
- Contoh kode wajib disertai komentar penjelas.
- Setiap file diawali ringkasan 1 paragraf.

## Batasan

- Folder ini **tidak boleh** dipakai untuk menyimpan secret.
- Jangan menaruh kode yang dieksekusi (`*.php`, `*.ts`) di sini.
- Perubahan pada folder ini harus disetujui Tech Lead.
```

---

## 📄 `.ai/context.md`

```markdown
# Konteks Proyek

## Identitas

- **Nama:** School Platform Enterprise
- **Versi Target:** 1.0.0
- **Tipe:** Modular Monolith (package-ready)
- **Model:** Single-school sekarang, multi-school ready

## Tujuan Bisnis

Menyediakan platform terpadu untuk manajemen sekolah menengah di
Indonesia, mencakup:

1. Website publik & CMS
2. Akademik (kelas, jadwal, kurikulum)
3. Siswa & Guru
4. Raport & penilaian
5. Perpustakaan
6. Bank Soal & CAT (Computer Assisted Test)

## Pengguna

| Role | Deskripsi |
|------|-----------|
| `super-admin` | Pemilik platform, akses lintas sekolah |
| `school-admin` | Admin sekolah |
| `principal` | Kepala sekolah |
| `vice-principal` | Wakil kepala sekolah |
| `teacher` | Guru |
| `homeroom-teacher` | Wali kelas |
| `student` | Siswa |
| `parent` | Orang tua |
| `librarian` | Pustakawan |
| `exam-admin` | Admin ujian |
| `content-editor` | Editor konten website |

## Stack Ringkas

Lihat `AGENTS.md` bagian "Ringkasan Proyek" untuk tabel lengkap.

## Modul yang Sudah Dirancang

1. `Core` — Sekolah, User, Role, Permission, Settings
2. `Website` — CMS, halaman publik
3. `Kelas` — Akademik, jadwal
4. `Siswa` — Data siswa, enrollment
5. `Guru` — Data guru, penugasan
6. `Raport` — Penilaian, raport
7. `Perpustakaan` — Katalog, peminjaman
8. `BankSoal` — Bank soal
9. `Cat` — Computer Assisted Test

## Dependensi Antar Modul

```
Core ← semua modul lain
Kelas ← Siswa, Guru, Raport
BankSoal ← Cat
Siswa ← Raport, Perpustakaan
```

## Sumber Kebenaran

- **Master plan:** `master_dev_plan.md`
- **ADR:** `docs/architecture/adr/`
- **Kontrak API:** `docs/api/`
```

---

## 📄 `.ai/architecture.md`

```markdown
# Arsitektur

## Peta Layer

Setiap modul memiliki **4 layer** yang wajib dipatuhi:

```
┌─────────────────────────────────────────────┐
│  Http (Presentation)                        │
│  Controller Web (Inertia) & API (JSON)      │
│  Request, Resource, Middleware              │
├─────────────────────────────────────────────┤
│  Application                                │
│  Service, Action, DTO                       │
│  Orkestrasi use-case                        │
├─────────────────────────────────────────────┤
│  Domain                                     │
│  Model, Enum, Event, Exception, Contract    │
│  Aturan bisnis murni                        │
├─────────────────────────────────────────────┤
│  Infrastructure                             │
│  Repository, Provider, Storage              │
│  Detail implementasi teknis                 │
└─────────────────────────────────────────────┘
```

## Aturan Arah Dependensi

```
Http → Application → Domain ← Infrastructure
```

- **Domain** tidak boleh tahu tentang Http/Infrastructure.
- **Application** hanya boleh bergantung pada Domain.
- **Infrastructure** mengimplementasikan kontrak Domain.
- **Http** memanggil Application, tidak langsung ke Domain/Repository.

## Struktur Direktori Modul

```
Modules/NamaModul/
├── app/
│   ├── Domain/
│   │   ├── Models/          # Eloquent Model
│   │   ├── Enums/           # Enum PHP 8.1+
│   │   ├── Events/          # Domain Event
│   │   ├── Exceptions/      # Custom Exception
│   │   └── Contracts/       # Interface
│   ├── Application/
│   │   ├── Actions/         # Single use-case (invokable)
│   │   ├── DTO/             # Spatie Data / readonly class
│   │   └── Services/        # Orkestrasi multi-action
│   ├── Infrastructure/
│   │   ├── Persistence/     # Repository Eloquent
│   │   └── Providers/       # Service Provider
│   └── Http/
│       ├── Controllers/
│       │   ├── Web/         # Inertia
│       │   └── Api/         # JSON
│       ├── Requests/        # Form Request
│       ├── Resources/       # API Resource
│       └── Middleware/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── routes/
│   ├── web.php
│   ├── api.php
│   └── channels.php
├── tests/
│   ├── Unit/
│   └── Feature/
└── module.json
```

## Pola Komunikasi Antar Modul

**WAJIB** menggunakan **Domain Event**, bukan direct service call.

```
Modul A ── dispatch(EventA) ──▶ Event Bus
                                    │
              ┌─────────────────────┼─────────────────────┐
              ▼                     ▼                     ▼
       Listener Modul B      Listener Modul C      Listener Modul D
```

## Contoh Alur Request

```
HTTP Request
    │
    ▼
Route (web.php / api.php)
    │
    ▼
Middleware (auth, school.context)
    │
    ▼
Controller (Web / Api)
    │
    ▼
Form Request (validasi)
    │
    ▼
DTO (transfer data)
    │
    ▼
Service (orkestrasi + transaction)
    │
    ▼
Action (single use-case)
    │
    ▼
Repository (data access)
    │
    ▼
Eloquent Model
    │
    ▼
PostgreSQL
```

## Tenant Context

Semua query data bisnis **WAJIB** terfilter oleh `school_id`:

```php
// ✅ BENAR
Student::query()->where('school_id', $context->requireId())->get();

// ✅ LEBIH BAIK (pakai global scope)
// → scopeForSchool() di Model

// ❌ SALAH
Student::query()->get(); // bocor lintas sekolah
```

## Realtime (Reverb)

Channel yang disepakati:

- `private-school.{schoolId}` — broadcast sekolah
- `private-class.{classId}` — broadcast kelas
- `private-exam.{examId}` — monitoring CAT
- `private-user.{userId}` — notifikasi pribadi

## Queue Prioritas

```
high          → notifikasi kritis, OTP
default       → job umum
reports       → generate PDF raport
imports       → import Excel/CSV besar
notifications → email, push
cat           → processing CAT
```
```

---

## 📄 `.ai/coding-standards.md`

```markdown
# Standar Penulisan Kode

## PHP

### Wajib
- `declare(strict_types=1);` di **setiap** file PHP.
- Class `final` kecuali memang dirancang untuk inheritance.
- Constructor property promotion untuk dependency injection.
- Type hint eksplisit pada parameter & return.
- `readonly` untuk DTO dan Value Object.
- Docblock Bahasa Indonesia untuk class public & method public.

### Contoh Kelas Standar

```php
<?php

declare(strict_types=1);

namespace Modules\Siswa\Application\Services;

use Modules\Siswa\Domain\Contracts\StudentRepositoryInterface;
use Modules\Siswa\Domain\Models\Student;

/**
 * Service untuk mengelola data siswa.
 *
 * Service ini adalah pintu masuk tunggal logika bisnis siswa,
 * digunakan oleh Web Controller (Inertia) dan API Controller.
 */
final class StudentService
{
    public function __construct(
        private readonly StudentRepositoryInterface $repository,
    ) {}

    /**
     * Mencari siswa berdasarkan ID.
     *
     * @throws \Modules\Siswa\Domain\Exceptions\StudentNotFoundException
     */
    public function findById(string $id): Student
    {
        $student = $this->repository->findById($id);

        if ($student === null) {
            throw new StudentNotFoundException($id);
        }

        return $student;
    }
}
```

### Dilarang
- ❌ `dd()`, `dump()`, `var_dump()`, `print_r()` di kode committed.
- ❌ `->get()` tanpa `->where('school_id', ...)` pada data bisnis.
- ❌ Facade `DB::` di Controller (harus di Service/Repository).
- ❌ Magic string — gunakan Enum atau konstanta.
- ❌ `mixed` tanpa alasan kuat.
- ❌ N+1 query — wajib `with()` atau `load()`.

## TypeScript / React

### Wajib
- `strict: true` di `tsconfig.json`.
- Tidak ada `any` — gunakan `unknown` + type guard.
- Komponen fungsional + hooks (bukan class component).
- Props bertipe eksplisit (`type Props = { ... }`).
- Nama file komponen `PascalCase.tsx`.

### Contoh Komponen Standar

```tsx
import type { FC } from 'react';

type StudentCardProps = {
  name: string;
  nis: string;
  onSelect?: (nis: string) => void;
};

/**
 * Kartu ringkas data siswa untuk ditampilkan di daftar.
 */
export const StudentCard: FC<StudentCardProps> = ({ name, nis, onSelect }) => {
  return (
    <div className="rounded-lg border p-4">
      <h3 className="font-semibold">{name}</h3>
      <p className="text-sm text-gray-500">NIS: {nis}</p>
      {onSelect && (
        <button type="button" onClick={() => onSelect(nis)}>
          Pilih
        </button>
      )}
    </div>
  );
};
```

## Database

- Nama tabel: `snake_case` plural (`students`).
- Nama kolom: `snake_case` (`full_name`).
- Foreign key: `{singular}_id` (`student_id`).
- Index: `idx_{table}_{column}`.
- Primary key: ULID (`$table->ulid('id')->primary()`).

## Naming Convention

| Elemen | Konvensi | Contoh |
|--------|----------|--------|
| Class | PascalCase | `StudentService` |
| Method | camelCase | `findById` |
| Variabel | camelCase | `$studentData` |
| Konstanta | UPPER_SNAKE | `MAX_ATTEMPTS` |
| Tabel | snake_case plural | `students` |
| Kolom | snake_case | `full_name` |
| Route | kebab-case | `/students/{id}/grades` |
| Permission | dot.notation | `student.student.view` |
| Event | PascalCase past tense | `StudentCreated` |
| Listener | PascalCase imperative | `SendWelcomeEmail` |

## Git

### Commit Message (Conventional Commits)

```
feat(siswa): tambah endpoint import siswa massal
fix(auth): perbaiki token tidak ter-revoke saat logout
docs(adr): tambahkan ADR-0009 tentang queue
chore(deps): update laravel ke 13.1
test(raport): tambah test kalkulasi nilai akhir
refactor(core): ekstrak service module ke kontrak
```

### Branch

- `feature/{ticket}-{short-desc}` — fitur baru
- `fix/{ticket}-{short-desc}` — perbaikan bug
- `hotfix/{ticket}` — perbaikan darurat production
- `chore/{desc}` — non-fitur

### Pull Request

- Judul: mengikuti Conventional Commits.
- Deskripsi: apa, mengapa, bagaimana, dan cara test.
- Wajib hijau CI sebelum merge.
- Minimal 1 approval.
```

---

## 📄 `.ai/module-template.md`

```markdown
# Template Modul Baru

Gunakan template ini saat membuat modul baru dengan
`php artisan module:make NamaModul`.

## Langkah

1. `php artisan module:make NamaModul`
2. Rapikan folder sesuai struktur di `.ai/architecture.md`.
3. Isi `module.json` dengan benar (lihat di bawah).
4. Buat migrasi, model, service, controller, route.
5. Tambah test.
6. Daftarkan permission di `PermissionSeeder`.
7. Update dokumentasi modul di `docs/modules/{slug}.md`.

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
│       └── Resources/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── routes/
│   ├── web.php
│   ├── api.php
│   └── channels.php
├── tests/
│   ├── Unit/
│   └── Feature/
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
        $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
    }
}
```

## Checklist

Lihat `.ai/checklist/new-module.md`.
```

---

## 📄 `.ai/patterns.md`

```markdown
# Pattern yang Direkomendasikan

## 1. Action Pattern (Single Use-Case)

Setiap use-case bisnis dibuat sebagai class invokable.

```php
<?php

declare(strict_types=1);

namespace Modules\Siswa\Application\Actions;

use Modules\Siswa\Application\DTO\StudentData;
use Modules\Siswa\Domain\Models\Student;

/**
 * Action untuk membuat siswa baru.
 */
final class CreateStudentAction
{
    public function execute(StudentData $data): Student
    {
        return Student::query()->create([
            'nis' => $data->nis,
            'nisn' => $data->nisn,
            'full_name' => $data->fullName,
            'gender' => $data->gender,
            'birth_date' => $data->birthDate,
        ]);
    }
}
```

## 2. Service Pattern (Orkestrasi)

Service menggabungkan beberapa Action + transaksi.

```php
public function create(StudentData $data): Student
{
    return DB::transaction(function () use ($data): Student {
        $student = $this->createAction->execute($data);
        event(new StudentCreated($student));
        return $student;
    });
}
```

## 3. Repository Pattern

Akses data melalui interface agar mudah di-mock.

```php
interface StudentRepositoryInterface
{
    public function findById(string $id): ?Student;
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;
    public function delete(Student $student): void;
}
```

## 4. DTO dengan Spatie Data

```php
final class StudentData extends Data
{
    public function __construct(
        public readonly string $nis,
        public readonly string $nisn,
        public readonly string $fullName,
        public readonly string $gender,
        public readonly string $birthDate,
    ) {}
}
```

## 5. Form Request untuk Validasi

```php
final class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('student.student.create');
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'nis' => ['required', 'string', 'max:20', 'unique:students,nis'],
            'nisn' => ['required', 'string', 'max:20', 'unique:students,nisn'],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'birth_date' => ['required', 'date', 'before:today'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nis.unique' => 'NIS sudah terdaftar.',
            'nisn.unique' => 'NISN sudah terdaftar.',
        ];
    }
}
```

## 6. API Resource

```php
final class StudentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nis' => $this->nis,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
        ];
    }
}
```

## 7. Domain Event + Listener

```php
// Dispatch
event(new StudentCreated($student));

// Listener (queued)
final class SendWelcomeNotification implements ShouldQueue
{
    public function handle(StudentCreated $event): void
    {
        // ...
    }
}
```

## 8. Exception Khusus Domain

```php
final class StudentNotFoundException extends DomainException
{
    public function __construct(string $id)
    {
        parent::__construct("Siswa dengan ID [{$id}] tidak ditemukan.");
    }
}
```
```

---

## 📄 `.ai/anti-patterns.md`

```markdown
# Anti-Pattern — WAJIB DIHINDARI

## 1. Fat Controller

❌ **SALAH**

```php
public function store(Request $request)
{
    $validated = $request->validate([...]);
    $student = Student::create($validated);
    Mail::to($student->email)->send(new WelcomeMail($student));
    Log::info('Siswa dibuat', ['id' => $student->id]);
    return redirect()->route('students.index');
}
```

✅ **BENAR**

```php
public function store(StoreStudentRequest $request)
{
    $student = $this->service->create(StudentData::from($request->validated()));
    return redirect()->route('students.show', $student->id);
}
```

## 2. Query di View / Blade

❌ **SALAH**

```blade
@foreach(\App\Models\Student::all() as $s) ... @endforeach
```

✅ **BENAR** — kirim dari controller.

## 3. Magic Number / String

❌ **SALAH**

```php
if ($student->status === 1) { ... }
```

✅ **BENAR**

```php
enum StudentStatus: string {
    case Active = 'active';
}

if ($student->status === StudentStatus::Active) { ... }
```

## 4. Query Tanpa Filter `school_id`

❌ **SALAH**

```php
Student::query()->get(); // bocor lintas sekolah
```

✅ **BENAR**

```php
Student::query()->where('school_id', $context->requireId())->get();
```

## 5. Bypass Service dari Modul Lain

❌ **SALAH** — Modul A langsung pakai model modul B.

```php
use Modules\Raport\Domain\Models\Grade;
Grade::query()->where(...)->delete();
```

✅ **BENAR** — pakai event / kontrak.

```php
// Modul A: dispatch event
event(new StudentGraduated($student));

// Modul B: listener
final class ArchiveGradesOnGraduation
{
    public function handle(StudentGraduated $event): void { ... }
}
```

## 6. Validasi di Controller (bukan Form Request)

❌ **SALAH**

```php
public function store(Request $request)
{
    $request->validate([...]); // validasi di controller
}
```

✅ **BENAR** — buat `FormRequest` terpisah.

## 7. Hardcode Konfigurasi

❌ **SALAH**

```php
$apiKey = 'sk_live_abc123';
```

✅ **BENAR**

```php
$apiKey = config('services.payment.key');
```

## 8. `env()` di Luar `config/`

❌ **SALAH**

```php
// di service
$timeout = env('API_TIMEOUT', 30);
```

✅ **BENAR**

```php
// config/services.php
'timeout' => env('API_TIMEOUT', 30),

// service
$timeout = config('services.timeout');
```

## 9. N+1 Query

❌ **SALAH**

```php
foreach (Student::all() as $student) {
    echo $student->class->name; // N+1
}
```

✅ **BENAR**

```php
foreach (Student::with('class')->get() as $student) {
    echo $student->class->name;
}
```

## 10. Try-Catch Menelan Exception

❌ **SALAH**

```php
try {
    $this->service->doSomething();
} catch (\Throwable $e) {
    // diamkan
}
```

✅ **BENAR**

```php
try {
    $this->service->doSomething();
} catch (SpecificException $e) {
    Log::warning('Gagal memproses X', [
        'error' => $e->getMessage(),
        'request_id' => $requestId,
    ]);
    throw $e; // atau tangani secara spesifik
}
```
```

---

## 📄 `.ai/testing.md`

```markdown
# Strategi Testing

## Piramida Test

```
        /\
       /E2E\        (Playwright) - sedikit, mahal
      /------\
     /Feature\      (Laravel Feature) - sedang
    /----------\
   /    Unit    \   (PHPUnit, Vitest) - banyak, murah
  /--------------\
```

## Struktur Test

```
tests/
├── Unit/           # Logic murni, tanpa DB
├── Feature/        # Request → response, pakai DB
├── Integration/    # Modul ↔ modul, queue, event
└── Architecture/   # Batas dependensi antar layer
```

Di dalam setiap modul:

```
Modules/Siswa/tests/
├── Unit/
│   ├── ValueObjects/
│   └── Services/
├── Feature/
│   ├── Web/
│   └── Api/
```

## Aturan Test

1. **Setiap service public method** wajib punya test.
2. **Setiap endpoint** wajib punya test happy path + minimal 1 error.
3. Nama test menggunakan Bahasa Indonesia (snake_case).
4. Gunakan `RefreshDatabase` untuk Feature test.
5. **Faker locale** = `id_ID`.

## Contoh Unit Test

```php
final class StudentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_dapat_membuat_siswa_baru(): void
    {
        $service = app(StudentService::class);

        $student = $service->create(new StudentData(
            nis: '2024001',
            nisn: '1234567890',
            fullName: 'Ahmad Fauzi',
            gender: 'L',
            birthDate: '2010-01-15',
        ));

        $this->assertDatabaseHas('students', [
            'nis' => '2024001',
            'full_name' => 'Ahmad Fauzi',
        ]);
    }

    public function test_tidak_dapat_mencari_siswa_tidak_ada(): void
    {
        $this->expectException(StudentNotFoundException::class);
        app(StudentService::class)->findById('01ABCDEF');
    }
}
```

## Contoh Feature Test API

```php
final class StudentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_melihat_daftar_siswa(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('student.student.view');

        Student::factory()->count(3)->create();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/students')
            ->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'nis', 'full_name']],
            ]);
    }

    public function test_user_tanpa_permission_ditolak(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/students')
            ->assertForbidden();
    }
}
```

## Contoh Architecture Test

```php
final class ArchitectureTest extends TestCase
{
    public function test_core_tidak_bergantung_pada_modul_fitur(): void
    {
        $this->assertClassNotToUseClass(
            \App\Core\Application\Services\SchoolContextService::class,
            \Modules\Siswa\Domain\Models\Student::class,
        );
    }

    public function test_controller_tidak_query_langsung(): void
    {
        // Pastikan tidak ada `::query()` di Controller
        // (gunakan custom assertion / grep)
    }
}
```

## Coverage Target

| Layer | Minimum |
|-------|---------|
| Service | 90% |
| Action | 90% |
| Controller | 80% |
| Repository | 80% |
| **Total** | **≥ 80%** |
```

---

## 📄 `.ai/workflow.md`

```markdown
# Alur Kerja Development

## Siklus Harian

```
1. Pull branch `main` terbaru
2. Buat branch fitur: `feature/{ticket}-{desc}`
3. Tulis kode + test
4. Jalankan quality gate lokal:
   - ./vendor/bin/pint
   - ./vendor/bin/phpstan analyse
   - php artisan test
   - npx tsc --noEmit
   - npm run build
5. Commit (Conventional Commits)
6. Push → buat Merge Request di GitLab
7. Tunggu Jenkins hijau
8. Minta review
9. Merge setelah approve
```

## Quality Gate

**Merge ditolak jika:**

- ❌ Test gagal
- ❌ Lint gagal
- ❌ TypeScript error
- ❌ Build frontend gagal
- ❌ Coverage < 80%
- ❌ Ada `dd()` / `dump()` / `console.log()` di kode produksi
- ❌ Ada `TODO` tanpa nomor tiket
- ❌ Ada migrasi lama diedit
- ❌ Ada secret di commit

## Konvensi Branch

| Prefix | Penggunaan |
|--------|-----------|
| `feature/` | Fitur baru |
| `fix/` | Perbaikan bug |
| `hotfix/` | Darurat di production |
| `chore/` | Perawatan, deps, docs |
| `refactor/` | Refactor tanpa perubahan perilaku |
| `test/` | Tambah/perbaiki test saja |

## Konvensi Commit

Format: `<type>(<scope>): <deskripsi>`

Contoh:

```
feat(siswa): tambah endpoint bulk import
fix(auth): perbaiki revoke token saat logout
docs(adr): tambahkan ADR tentang queue
chore(deps): perbarui laravel ke 13.2
```

## Review Checklist

Lihat `.ai/checklist/pull-request.md`.

## Deployment Flow

```
Push ke GitLab → Webhook → Jenkins
    │
    ├─ Build Stage (parallel):
    │   ├─ composer install
    │   ├─ npm ci
    │   ├─ lint
    │   ├─ static analysis
    │   └─ test
    │
    ├─ Build Frontend
    │   └─ npm run build
    │
    ├─ Security Audit
    │   └─ composer audit + npm audit
    │
    └─ Deploy (hanya `main`)
        ├─ rsync ke Fedora 44
        ├─ composer install --no-dev
        ├─ php artisan migrate --force
        ├─ php artisan config:cache
        ├─ php artisan route:cache
        ├─ php artisan event:cache
        ├─ php artisan queue:restart
        └─ systemctl reload php-fpm nginx
```
```

---

## 📄 `.ai/commands.md`

```markdown
# Cheatsheet CLI

## Setup Awal

```bash
# Clone & setup
git clone <repo> school-platform
cd school-platform/backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
```

## Development Harian

```bash
# Backend + Frontend (Vite)
composer dev

# Atau terpisah:
php artisan serve        # http://localhost:8000
npm run dev              # http://localhost:5173

# Queue worker
php artisan queue:work --queue=high,default,reports,imports,notifications,cat

# Reverb (WebSocket)
php artisan reverb:start
```

## Modul

```bash
php artisan module:make NamaModul
php artisan module:list
php artisan school:module:install siswa
php artisan school:module:enable siswa
php artisan school:module:disable siswa
php artisan school:module:uninstall siswa --purge
```

## Database

```bash
php artisan migrate
php artisan migrate:fresh --seed
php artisan migrate:status
php artisan db:seed --class=RoleSeeder
php artisan db:show
```

## Testing

```bash
php artisan test
php artisan test --parallel
php artisan test --filter=StudentServiceTest
php artisan test --coverage --min=80
```

## Code Quality

```bash
./vendor/bin/pint               # auto-fix
./vendor/bin/pint --test        # check only
./vendor/bin/phpstan analyse    # static analysis
npx tsc --noEmit                # TypeScript check
npx eslint resources/js         # ESLint
```

## Cache & Optimization

```bash
php artisan optimize
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan view:cache
```

## Reverb

```bash
php artisan reverb:start
php artisan reverb:restart
```

## Debug & Insight

```bash
php artisan about
php artisan route:list
php artisan event:list
php artisan queue:failed
php artisan queue:retry all
php artisan pail                  # log streaming
```
```

---

## 📄 `.ai/api-contract.md`

```markdown
# Kontrak API

## Base URL

```
/api/v1
```

## Format Response Sukses

```json
{
  "success": true,
  "message": "Operasi berhasil.",
  "data": { "...": "..." },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100
  }
}
```

## Format Response Error

```json
{
  "success": false,
  "message": "Data yang dikirim tidak valid.",
  "error_code": "VALIDATION_ERROR",
  "errors": {
    "nis": ["NIS sudah terdaftar."]
  }
}
```

## Kode Error Standar

| Code | HTTP | Deskripsi |
|------|------|-----------|
| `VALIDATION_ERROR` | 422 | Input tidak valid |
| `UNAUTHENTICATED` | 401 | Token tidak valid/expired |
| `FORBIDDEN` | 403 | Tidak punya permission |
| `NOT_FOUND` | 404 | Resource tidak ditemukan |
| `CONFLICT` | 409 | Konflik data |
| `RATE_LIMITED` | 429 | Terlalu banyak request |
| `INTERNAL_ERROR` | 500 | Kesalahan server |

## Autentikasi

```
Authorization: Bearer {token}
```

Token diperoleh dari `POST /api/v1/auth/login`.

## Endpoint Standar

### Auth

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| POST | `/auth/login` | Login (throttle 10/menit) |
| POST | `/auth/logout` | Logout |
| GET | `/auth/me` | Info user |

### Resource

Untuk setiap resource (contoh: `students`):

| Method | Endpoint | Deskripsi |
|--------|----------|-----------|
| GET | `/students` | Daftar + pagination |
| POST | `/students` | Buat |
| GET | `/students/{id}` | Detail |
| PATCH | `/students/{id}` | Update sebagian |
| DELETE | `/students/{id}` | Hapus |

## Query Parameter Standar

| Param | Deskripsi | Contoh |
|-------|-----------|--------|
| `page` | Halaman | `?page=2` |
| `per_page` | Item per halaman (max 100) | `?per_page=50` |
| `sort` | Urutkan | `?sort=-created_at` |
| `filter[...]` | Filter | `?filter[status]=active` |
| `search` | Pencarian | `?search=ahmad` |

## Rate Limiting

| Endpoint | Limit |
|----------|-------|
| `/auth/login` | 10/menit per IP |
| API umum | 60/menit per user |
| API write | 30/menit per user |

## Versioning

- Versi saat ini: `v1`
- Breaking change → versi baru (`v2`)
- Versi lama didukung minimal 6 bulan

## DTO ↔ TypeScript

Setiap DTO di backend memiliki tipe TypeScript yang di-generate:

```bash
php artisan typescript:transform
```

Output: `resources/js/types/generated.d.ts`
```

---

## 📄 `.ai/security.md`

```markdown
# Aturan Keamanan

## Wajib pada Setiap Endpoint

1. **Autentikasi** — middleware `auth:sanctum` untuk API,
   `auth` untuk Web.
2. **Otorisasi** — `$this->authorize()` atau `Gate::allows()`
   dengan permission eksplisit.
3. **Validasi** — Form Request, bukan `validate()` di controller.
4. **Rate Limiting** — throttle pada endpoint sensitif.
5. **Output Encoding** — gunakan API Resource, jangan
   `return $model`.

## Checklist Endpoint

```php
Route::middleware(['auth:sanctum', 'throttle:60,1', 'school.context'])
    ->get('/students', [StudentApiController::class, 'index']);
```

## Validasi Input

- **Whitelist**, bukan blacklist.
- Batasi panjang string (`max:255`).
- Validasi format (`email`, `date`, `in:...`).
- File upload: cek MIME + ekstensi + ukuran.

## Otorisasi

Gunakan **permission**, bukan role, untuk pengecekan:

```php
// ✅ BENAR
if (! $user->can('student.student.create')) abort(403);

// ❌ SALAH
if (! $user->hasRole('admin')) abort(403);
```

## Data Sensitif

- Password: `Hash::make()`, cast `hashed`.
- Token: simpan hash, kirim plain sekali saja.
- PII (NISN, alamat): jangan log ke file.
- File dokumen: simpan di disk private, akses via signed URL.

## SQL Injection

Selalu gunakan **query builder / Eloquent**. Jangan pernah
concatenate string ke query mentah.

```php
// ❌ SALAH
DB::select("SELECT * FROM students WHERE nis = '$nis'");

// ✅ BENAR
DB::select('SELECT * FROM students WHERE nis = ?', [$nis]);
```

## Mass Assignment

Model wajib punya `$fillable` eksplisit. **Jangan** pakai `$guarded = []`.

## CSRF

- Web (Inertia): wajib CSRF token (Laravel otomatis).
- API stateless: tidak pakai CSRF, tapi pakai Sanctum token.

## CORS

Konfigurasi di `config/cors.php`. Whitelist origin, bukan `*`.

## Secure Headers

Middleware wajib menambahkan:

- `Strict-Transport-Security`
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY`
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Content-Security-Policy` (minimal)

## Logging Aman

```php
// ✅ BENAR
Log::info('Login gagal', ['email' => $email, 'request_id' => $requestId]);

// ❌ SALAH
Log::info('Login gagal', ['password' => $password]);
```

## Secret Management

- Tidak ada secret di repo.
- `.env` di-gitignore.
- `.env.example` hanya berisi placeholder.
- Production: pakai environment variable dari sistem.

## Audit Log

Semua operasi mutasi (`CREATE`, `UPDATE`, `DELETE`, `APPROVE`,
`PUBLISH`) **wajib** tercatat di `audit_logs` dengan:

- `user_id`
- `action`
- `auditable_type` & `auditable_id`
- `old_values` & `new_values`
- `ip_address`
- `request_id`
```

---

## 📄 `.ai/database.md`

```markdown
# Konvensi Database

## PostgreSQL 18

- Ekstensi wajib: `pgcrypto`, `citext`.
- Collation default: `UTF-8`.
- Timezone: `UTC` (render ke `Asia/Jakarta` di aplikasi).

## Primary Key

Selalu ULID:

```php
$table->ulid('id')->primary();
```

Model: `use HasUlids;`

## Foreign Key

```php
$table->foreignUlid('school_id')
    ->constrained('schools')
    ->cascadeOnDelete();

$table->foreignUlid('student_id')
    ->constrained('students')
    ->restrictOnDelete();
```

**Aturan:**
- `cascadeOnDelete` untuk **milik** (child).
- `restrictOnDelete` untuk **referensi** (parent).
- `nullOnDelete` untuk **referensi opsional**.

## Naming

| Elemen | Konvensi | Contoh |
|--------|----------|--------|
| Tabel | snake_case plural | `students` |
| Kolom | snake_case | `full_name` |
| FK | `{singular}_id` | `student_id` |
| Index | `idx_{table}_{column}` | `idx_students_school_id` |
| Unique | `uniq_{table}_{column}` | `uniq_students_nis` |
| Check | `chk_{table}_{rule}` | `chk_students_gender` |

## Kolom Standar

Setiap tabel bisnis memiliki:

```php
$table->timestamps();       // created_at, updated_at
$table->softDeletes();      // deleted_at (opsional)
```

Untuk audit:

```php
$table->string('created_by', 26)->nullable();
$table->string('updated_by', 26)->nullable();
```

## Enum

Hindari PostgreSQL ENUM. Gunakan string + Check:

```php
$table->string('status', 20)->default('active');
$table->check("status IN ('active', 'inactive', 'graduated')");
```

Di PHP: Enum.

```php
enum StudentStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Graduated = 'graduated';
}
```

## JSONB

Gunakan `jsonb` untuk data fleksibel yang sering di-query:

```php
$table->jsonb('metadata')->nullable();
```

**Jangan** pakai JSONB untuk data yang sering difilter — gunakan kolom
terpisah.

## Index

Buat index **hanya** untuk kolom yang muncul di:

- `WHERE` sering
- `ORDER BY` sering
- `JOIN`

```php
$table->index('school_id');
$table->index('status');
$table->index('created_at');

// Composite (berdasarkan query aktual)
$table->index(['school_id', 'status', 'created_at']);
```

## Migrasi

- **Jangan edit migrasi yang sudah di-merge.**
- Buat migrasi baru untuk perubahan.
- Satu migrasi = satu perubahan logis.
- Beri komentar bila ada alasan khusus.

## Seeder

Seeder **idempotent** — pakai `findOrCreate` / `updateOrCreate`:

```php
Role::findOrCreate('super-admin', 'web');
```

## Query Performance

- Selalu `with()` bila ada relasi di loop.
- Pakai `->paginate()` bukan `->get()` untuk list.
- Gunakan `EXPLAIN ANALYZE` untuk query lambat.
- Aktifkan `DB::listen()` di lokal untuk deteksi N+1.
```

---

## 📄 `.ai/glossary.md`

```markdown
# Glosarium Domain

| Istilah | Definisi |
|---------|----------|
| **School** | Entitas sekolah (unit bisnis utama, untuk multi-tenant) |
| **NPSN** | Nomor Pokok Sekolah Nasional (ID resmi sekolah) |
| **NIS** | Nomor Induk Siswa (ID siswa dalam sekolah) |
| **NISN** | Nomor Induk Siswa Nasional (ID siswa dari pemerintah) |
| **Academic Year** | Tahun ajaran (mis. 2025/2026) |
| **Semester** | Semester (Ganjil / Genap) |
| **Level** | Tingkat (SD kelas 1-6, SMP kelas 7-9, dst) |
| **Major** | Jurusan (IPA, IPS, Bahasa) — untuk SMA/SMK |
| **Class** | Kelas (mis. 10-A, XI IPA 1) |
| **Room** | Ruang fisik |
| **Subject** | Mata pelajaran |
| **Curriculum** | Kurikulum (K13, Merdeka) |
| **Homeroom Teacher** | Wali kelas |
| **Raport** | Laporan hasil belajar siswa |
| **Assessment** | Penilaian (formatif, sumatif) |
| **Grade** | Nilai |
| **Behavior** | Sikap / perilaku siswa |
| **Extracurricular** | Ekstrakurikuler |
| **Loan** | Peminjaman (perpustakaan) |
| **Fine** | Denda |
| **Question** | Butir soal |
| **Bank Soal** | Kumpulan soal |
| **CAT** | Computer Assisted Test |
| **Exam Session** | Sesi ujian |
| **Attempt** | Percobaan mengerjakan ujian |
| **Proctoring** | Pengawasan ujian |
| **Correlation ID** | ID untuk melacak satu request lintas service |
| **Tenant** | Sekolah (dalam konteks multi-school) |
| **Module** | Modul fitur (Siswa, Raport, dll) |
| **Manifest** | File `module.json` yang menjelaskan modul |
| **Registry** | Tabel `modules` yang mencatat status modul |
```

---

## 📄 `.ai/checklist/new-module.md`

```markdown
# Checklist — Modul Baru

Gunakan checklist ini saat membuat modul baru.

## Struktur

- [ ] Folder `Modules/NamaModul/` sudah dibuat via `module:make`
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
- [ ] `requires` mencantumkan `core` minimal
- [ ] `permissions` didaftarkan

## Kode

- [ ] Model pakai `HasUlids`
- [ ] Model pakai `SoftDeletes` bila relevan
- [ ] Migration pakai `foreignUlid` untuk `school_id`
- [ ] Service menggunakan `DB::transaction()` untuk operasi multi-tabel
- [ ] DTO pakai `readonly`
- [ ] FormRequest memvalidasi dengan whitelist
- [ ] Controller Web mengembalikan Inertia Response
- [ ] Controller Api mengembalikan `ApiResponse` atau Resource
- [ ] Route didaftarkan di `ServiceProvider`

## Test

- [ ] Unit test untuk setiap Service method
- [ ] Feature test untuk setiap endpoint (Web & API)
- [ ] Test happy path + minimal 1 edge case
- [ ] Test otorisasi (user tanpa permission → 403)

## Dokumentasi

- [ ] Update `docs/modules/{slug}.md`
- [ ] Daftarkan permission di `PermissionSeeder`
- [ ] Update `AGENTS.md` bila ada perubahan arsitektur

## Final Check

- [ ] `./vendor/bin/pint --test` ✅
- [ ] `./vendor/bin/phpstan analyse` ✅
- [ ] `php artisan test` ✅
- [ ] `npx tsc --noEmit` ✅
```

---

## 📄 `.ai/checklist/new-endpoint.md`

```markdown
# Checklist — Endpoint Baru

## Perencanaan

- [ ] Tentukan apakah endpoint Web (Inertia) atau API (JSON)
- [ ] Tentukan permission yang dibutuhkan
- [ ] Tentukan request & response schema

## Implementasi

### API Endpoint
- [ ] Route di `Modules/.../routes/api.php`
- [ ] Middleware: `auth:sanctum`, `school.context`, `throttle`
- [ ] FormRequest untuk validasi
- [ ] Controller method dengan type hint response
- [ ] API Resource untuk transformasi
- [ ] DTO dari FormRequest
- [ ] Service dipanggil (bukan logic langsung)
- [ ] Response via `ApiResponse::success()` atau Resource

### Web Endpoint
- [ ] Route di `Modules/.../routes/web.php`
- [ ] Middleware: `auth`, `verified`, `school.context`
- [ ] FormRequest untuk validasi
- [ ] Controller method mengembalikan `Inertia::render()` atau `RedirectResponse`
- [ ] Share data via `HandleInertiaRequests` bila perlu

## Test

- [ ] Test happy path
- [ ] Test validasi (422)
- [ ] Test otorisasi (401, 403)
- [ ] Test not found (404)

## Dokumentasi

- [ ] Update `docs/api/{module}.md`
- [ ] Update OpenAPI spec
- [ ] Update `docs/api/CHANGELOG.md`
```

---

## 📄 `.ai/checklist/new-migration.md`

```markdown
# Checklist — Migrasi Baru

- [ ] Nama file: `YYYY_MM_DD_HHMMSS_verb_noun_table.php`
- [ ] `declare(strict_types=1);`
- [ ] Nama class anonymous (`return new class extends Migration`)
- [ ] `up()` dan `down()` keduanya diimplementasikan
- [ ] Ada `timestamps()` (kecuali tabel pivot/relasi)
- [ ] Ada `softDeletes()` bila entitas bisnis
- [ ] `foreignUlid` untuk foreign key ke tabel ULID
- [ ] `onDelete` eksplisit (`restrict` / `cascade` / `null`)
- [ ] Index hanya untuk kolom yang di-query
- [ ] Tidak mengedit migrasi lama
- [ ] Test migrasi: `php artisan migrate` dan `migrate:rollback`

## Verifikasi

```bash
php artisan migrate:fresh --seed
php artisan migrate:rollback --step=1
php artisan migrate
```

## Catatan

- Bila mengubah kolom: buat migrasi baru, bukan edit migrasi lama.
- Bila ada data existing: siapkan migrasi data terpisah.
- Untuk production: `--force` dan backup dulu.
```

---

## 📄 `.ai/checklist/pull-request.md`

```markdown
# Checklist — Pull Request

## Sebelum Push

- [ ] Branch sesuai konvensi (`feature/...`, `fix/...`)
- [ ] Tidak ada file tidak sengaja ter-commit
- [ ] Tidak ada `.env` atau secret
- [ ] `./vendor/bin/pint` dijalankan
- [ ] `./vendor/bin/phpstan analyse` bersih
- [ ] `php artisan test` hijau
- [ ] `npx tsc --noEmit` bersih
- [ ] `npm run build` sukses

## Deskripsi PR

- [ ] Judul mengikuti Conventional Commits
- [ ] Deskripsi menjelaskan **apa** dan **mengapa**
- [ ] Screenshot (bila ada perubahan UI)
- [ ] Cara test manual (langkah-langkah)
- [ ] Link tiket terkait

## Setelah Dibuat

- [ ] CI hijau (Jenkins)
- [ ] Minta review minimal 1 orang
- [ ] Respond semua komentar reviewer
- [ ] Squash commit bila perlu
- [ ] Merge dengan **Squash & Merge** (bukan fast-forward)

## Setelah Merge

- [ ] Hapus branch remote
- [ ] Pantau deployment Jenkins
- [ ] Verifikasi di staging (bila ada)
- [ ] Update changelog bila perlu
```

---

## 📌 Cara Menggunakan

1. **Simpan `AGENTS.md` di root** repositori.
2. **Buat folder `.ai/`** dengan semua file di atas.
3. **Commit** ke repositori:
   ```bash
   git add AGENTS.md .ai/
   git commit -m "docs: tambahkan panduan AI agent (AGENTS.md + .ai/)"
   ```
4. **AI agent** (Claude Code, Cursor, dsb) akan otomatis membaca `AGENTS.md` sebagai entry point, lalu masuk ke `.ai/` untuk konteks granular.
