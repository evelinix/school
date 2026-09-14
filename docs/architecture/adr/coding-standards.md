# Standar Penulisan Kode

## PHP
- Wajib `declare(strict_types=1);`
- Class `final` kecuali memang dirancang untuk inheritance
- Constructor property promotion
- `readonly` untuk DTO dan Value Object
- Return type & parameter type eksplisit
- Formatter: **Laravel Pint** (preset `laravel`)
- Static analysis: **PHPStan level 8**

## TypeScript
- `strict: true`
- Tidak ada `any` (gunakan `unknown` bila perlu)
- Prefer `type` untuk data, `interface` untuk contract
- Naming: `PascalCase` (komponen/tipe), `camelCase` (fungsi/variabel)

## Database
- Tabel: `snake_case`, plural (contoh: `students`)
- Kolom: `snake_case` (contoh: `full_name`)
- Foreign key: `{singular}_id` (contoh: `student_id`)
- Index: `idx_{table}_{column}`

## API
- REST versioned: `/api/v1/...`
- Resource plural: `/students`
- Response envelope konsisten

## Event
- Domain event: `PascalCase` past tense (`StudentCreated`)
- Listener: `PascalCase` imperative (`SendWelcomeNotification`)

## Permission
- Format: `module.resource.action` (`student.student.view`)

## Git
- Branch: `feature/{ticket}-{short-desc}`, `fix/...`, `hotfix/...`
- Commit: **Conventional Commits** (`feat:`, `fix:`, `chore:`, `docs:`)
- PR wajib melalui review + CI hijau