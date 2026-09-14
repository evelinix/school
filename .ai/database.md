# Konvensi Database

> Aktual: PostgreSQL (lokal) / SQLite `:memory:` (test). Ekstensi PG (`pgcrypto`, `citext`) dan Redis adalah **target** — jangan menganggap sudah ada.

## Primary Key

- **Tabel existing** (`users`, `passkeys`, `permission_tables`, dll): `bigint` increment (`$table->id()`). **Jangan ubah migrasi lama.**
- **Keputusan: tabel bisnis BARU memakai ULID** — `$table->ulid('id')->primary()` + `use HasUlids;` di model.
- FK ke tabel ULID: `$table->foreignUlid('school_id')->constrained('schools')->cascadeOnDelete();`

**Aturan `onDelete`:**
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

## Kolom Standar

Setiap tabel bisnis:

```php
$table->timestamps();        // created_at, updated_at
$table->softDeletes();       // bila entitas bisnis (opsional)
```

Audit (target): `created_by`, `updated_by` (string nullable).

## Enum

Hindari PostgreSQL ENUM — pakai string + Check:

```php
$table->string('status', 20)->default('active');
$table->check("status IN ('active', 'inactive', 'graduated')");
```

Di PHP, pasang Enum yang sama (title-case keys):

```php
enum StudentStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Graduated = 'graduated';
}
```

## JSONB

`jsonb` untuk data fleksibel; **jangan** untuk kolom yang sering difilter — pakai kolom terpisah.

## Index

Buat index hanya untuk kolom yang sering muncul di `WHERE`, `ORDER BY`, `JOIN`. Pertimbangkan composite (`['school_id', 'status', 'created_at']`) berdasar query aktual.

## Migrasi

- **Jangan edit migrasi yang sudah di-commit / di-merge** — buat migrasi baru.
- Satu migrasi = satu perubahan logis.
- Pakai anonymous class `return new class extends Migration { ... }` (gaya existing).
- Implementasikan `up()` DAN `down()`.

## Seeder

Seeder **idempotent** — `findOrCreate` / `updateOrCreate`:

```php
Role::findOrCreate('super-admin', 'web');
```

## Query Performance

- Selalu `with()` bila relasi muncul di loop — hindari N+1.
- `->paginate()` untuk list, bukan `->get()`.
- `EXPLAIN ANALYZE` untuk query lambat.
- `php artisan pail` / log untuk deteksi N+1 saat debug.