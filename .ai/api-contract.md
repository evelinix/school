# Kontrak API

> **Status:** target. Repo saat ini **belum punya route `/api/...`** — semua web (Inertia). Dokumen ini jadi aturan saat API mobile (`Flutter`) dibangun.

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

## Autentikasi (target)

```
Authorization: Bearer {token}
```

Token dari `POST /api/v1/auth/login`. (Belum ada route API; rencana memakai Sanctum key. `laravel/sanctum` belum diinstal.)

## Endpoint Resource (contoh `students`)

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
| `sort` | Urutkan (- untuk desc) | `?sort=-created_at` |
| `filter[...]` | Filter | `?filter[status]=active` |
| `search` | Pencarian | `?search=ahmad` |

## Rate Limiting (target)

| Endpoint | Limit |
|----------|-------|
| `/auth/login` | 10/menit per IP |
| API umum | 60/menit per user |
| API write | 30/menit per user |

## Versioning

- Versi saat ini: `v1`.
- Breaking change → versi baru (`v2`); versi lama didukung minimal 6 bulan.

## Dokumentasi API (Swagger)

- Dokumentasi API memakai **Swagger / OpenAPI 3**.
- Spec: `docs/api/swagger.yaml` (atau `openapi.yaml` di file-structure.md) — diperbarui **dalam commit yang sama** dengan perubahan endpoint.
- UI Swagger tersedia di environment dev untuk eksplorasi (menyusul; belum dikonfigurasi).

## DTO ↔ TypeScript

- DTO backend (Spatie Data) menjadi source of truth untuk tipe frontend.
- Wayfinder menghasilkan tipe route/controller (`@/routes`, `@/actions`).
- Build pipeline: ubah DTO → regenerate; jangan hardcode shape di frontend.