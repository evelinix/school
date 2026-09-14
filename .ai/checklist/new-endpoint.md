# Checklist — Endpoint Baru

## Perencanaan

- [ ] Tentukan Web (Inertia) atau API (JSON)
- [ ] Tentukan permission yang dibutuhkan
- [ ] Tentukan request & response schema

## Implementasi

### Web Endpoint

- [ ] Route di `routes/web.php` (atau `Modules/.../routes/web.php`)
- [ ] Middleware: `auth` (+ `verified` bila perlu)
- [ ] FormRequest untuk validasi
- [ ] Controller method kembalikan `Inertia::render()` atau `RedirectResponse`
- [ ] Jalankan `php artisan wayfinder:generate` agar route frontend ter-update

### API Endpoint

- [ ] Route di `routes/api.php` (target `/api/v1`) — belum ada saat ini
- [ ] Middleware: `auth:*`, throttle, scoping school
- [ ] FormRequest + API Resource + DTO
- [ ] Service dipanggil (bukan logika langsung di controller)
- [ ] Response via Resource / envelope `{success, message, data}`

## Keamanan

- [ ] Otorisasi permission eksplisit (bukan role saja)
- [ ] Sensitive action pakai `RequirePassword` / `throttle` (pola `routes/settings.php`)
- [ ] Tidak ada `request()->all()` tanpa validasi

## Test

- [ ] Happy path
- [ ] Validasi gagal (422)
- [ ] Otorisasi (401, 403)
- [ ] Not found (404)

## Dokumentasi

- [ ] Update `.ai/api-contract.md` / `docs/api/` bila API
- [ ] Update spec **Swagger/OpenAPI** (`docs/api/swagger.yaml`) dalam commit yang sama
- [ ] Update changelog bila rilis