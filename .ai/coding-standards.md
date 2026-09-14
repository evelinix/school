# Standar Penulisan Kode

## PHP

### Wajib
- `declare(strict_types=1);` di setiap file PHP baru.
- Constructor property promotion untuk dependency injection.
- Type hint eksplisit pada parameter & return: `function findById(string $id): Student`.
- Docblock **Bahasa Indonesia** untuk class & method public yang non-trivial.
- Model memakai gaya Laravel 13: `#[Fillable([...])]` / `#[Hidden([...])]` (bukan `$fillable`/`$hidden`), lihat `app/Models/User.php`.
- Selalu buka kurung kurawal untuk control structure (`if`/`foreach`), tanpa single-line body.

### Dilarang
- ❌ `dd()`, `dump()`, `var_dump()`, `print_r()` di kode committed.
- ❌ `DB::` facade di Controller — harus di Service/Repository.
- ❌ Magic string — pakai Enum atau konstanta.
- ❌ `mixed` tanpa alasan kuat.
- ❌ N+1 — wajib `with()` / `load()`.
- ❌ `env()` di luar `config/` (pakai `config()`).
- ❌ Merubah migrasi yang sudah di-commit — buat migrasi baru.

## TypeScript / React

> **React Compiler aktif** (babel `reactCompilerPreset`). JANGAN menambahkan `useMemo`/`useCallback`/`memo` secara manual — compiler menanganinya.

### Wajib
- `strict: true` di `tsconfig.json` (sudah aktif).
- Tidak ada `any` — gunakan `unknown` + type guard.
- Komponen fungsional + hooks, bukan class component.
- Props bertipe eksplisit (`type Props = { ... }`).
- Nama file komponen `PascalCase.tsx`; utility `kebab-case.ts`.
- Route & controller dipanggil via **Wayfinder**: import dari `@/actions` / `@/routes` (bukan hardcode URL).
- Import memakai alias `@/`.

### Dilarang
- ❌ `console.log()` di kode produksi.
- ❌ Hardcode URL route.

## Database

- Nama tabel: `snake_case` plural (`students`).
- Nama kolom: `snake_case` (`full_name`).
- Foreign key: `{singular}_id` (`student_id`).
- Index: `idx_{table}_{column}`.
- Primary key **tabel bisnis baru**: ULID `$table->ulid('id')` (+ `use HasUlids;`). Tabel existing tetap `bigint id` — jangan ubah migrasi lama.

## Naming Convention

| Elemen | Konvensi | Contoh |
|--------|----------|--------|
| Class | PascalCase | `StudentService` |
| Method / variabel | camelCase | `findById`, `$studentData` |
| Konstanta | UPPER_SNAKE | `MAX_ATTEMPTS` |
| Enum key | TitleCase | `Active`, `Graduated` |
| Tabel | snake_case plural | `students` |
| Kolom | snake_case | `full_name` |
| Route | kebab-case | `/students/{id}/grades` |
| Permission | dot.notation | `student.student.view` |
| Event | PascalCase past tense | `StudentCreated` |
| Listener | PascalCase imperative | `SendWelcomeEmail` |

## Git

### Commit Message (Conventional Commits + scope)

```
feat(siswa): tambah endpoint import siswa massal
fix(auth): perbaiki token tidak ter-revoke saat logout
docs(adr): tambahkan ADR-0009 tentang queue
chore(deps): update laravel ke 13.1
test(raport): tambah test kalkulasi nilai akhir
refactor(core): ekstrak service module ke kontrak
```

Pesan commit memakai **Bahasa Indonesia** (lihat contoh `chore: tambah LICENSE...` di git log).

### Branch
- `feature/{ticket}-{short-desc}` — fitur baru
- `fix/{ticket}-{short-desc}` — perbaikan bug
- `hotfix/{ticket}` — darurat production
- `chore/{desc}` — non-fitur

### PR
- Judul mengikuti Conventional Commits.
- CI (Jenkins) hijau sebelum merge.
- Minimal 1 approval.
- Merge: squash (bukan fast-forward).