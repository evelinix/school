# Aturan Keamanan

## Wajib pada Setiap Endpoint

1. **Autentikasi** — middleware `auth` (web) / `auth:...` (API bila ada).
2. **Otorisasi** — `$user->can('...')` / `$this->authorize()` dengan **permission**, bukan `hasRole`.
3. **Validasi** — Form Request, bukan `validate()` inline di controller.
4. **Rate limiting** — throttle pada endpoint sensitif (pola existing: `routes/settings.php` pakai `throttle:6,1` untuk change-password dan `RequirePassword` untuk halaman security).
5. **Output transform** — API Resource, jangan `return $model`.

## Validasi Input

- Whitelist, bukan blacklist.
- Batasi panjang string (`max:255`).
- Validasi format (`email`, `date`, `in:...`).
- File upload: cek MIME + ekstensi + ukuran.

## Otorisasi

```php
// ✅ BENAR — permission eksplisit
if (! $user->can('student.student.create')) {
    abort(403);
}

// ❌ SALAH — role saja tidak cukup untuk akses granular
if (! $user->hasRole('admin')) {
    abort(403);
}
```

## Data Sensitif

- Password: cast `hashed` (sudah di `User`).
- PII: jangan log NISN/alamat ke file.
- File dokumen: disk private + signed URL bila perlu.

## SQL Injection

Selalu Eloquent / query builder; jangan concatenate ke SQL mentah:

```php
// ❌ DB::select("SELECT * FROM students WHERE nis = '$nis'")
// ✅
DB::select('SELECT * FROM students WHERE nis = ?', [$nis]);
```

## Mass Assignment

`#[Fillable([...])]` eksplisit di model. Jangan `$guarded = []`.

## CSRF

- Web (Inertia): Laravel menangani otomatis via XSRF token.
- API stateless (target): token, bukan CSRF.

## Secret Management

- Tidak ada secret di repo; `.env` gitignored; `.env.example` hanya placeholder.
- Jangan commit `.env` (berisi credential RustFS/DB asli; Jenkins regenerate APP_KEY dari `.env.example`).
- Jangan log credential/secret.

## Audit Log

Operasi mutasi (CREATE/UPDATE/DELETE/APPROVE/PUBLISH) **wajib** tercatat (target `audit_logs` dengan `user_id`, `action`, `auditable_type/auditable_id`, `old_values`/`new_values`, `ip_address`, `request_id`). Belum diimplementasikan — jangan berasumsi ada.

## Endpoint Web Sensitif — Ikuti Pola Existing

Lihat `routes/settings.php`:

```php
Route::get('settings/security', ...)->middleware(RequirePassword::class);
Route::put('settings/password', ...)->middleware('throttle:6,1');
```

Route security baru yang sensitif harus memakai kombinasi `RequirePassword` / `throttle` yang sama.