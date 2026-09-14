# Keputusan Kunci (Decision Log / ADR)

> Daftar keputusan yang telah diambil tim. Kalimat "keputusan" di file lain meruju ke sini.
> Pola: sebuah keputusan ditulis sebagai satu baris tabel — ringkas, bisa dilacak. Details/skalabilitas dibahas di file masing-masing.

| ID  | Keputusan | Alasan | Berlaku untuk | Tanggal |
|-----|-----------|--------|---------------|---------|
| D-01 | **Primary key tabel bisnis BARU memakai ULID** (`$table->ulid('id')->primary()` + `use HasUlids;`); tabel existing (`users`, `passkeys`, `permission_tables`) tetap `bigint`. Migrasi lama TIDAK diedit. | UUID/ULID aman untuk multi-school & future distributed; tabel legacy tidak diganggu demi stabilitas. | Migrasi + model baru | 2026-09-13 |
| D-02 | **Lapisan E2E memakai Playwright** (`@playwright/test`), menyusul setelah diinstal; test utama tetap Pest (unit + feature). | Playwright paling matang untuk browser dengan solder multi-browser; tidak mengganggu suite Pest. | `tests/e2e/` | 2026-09-13 |
| D-03 | **Dokumentasi API memakai Swagger / OpenAPI 3** (spec `docs/api/swagger.yaml`), diperbarui di commit yang sama dengan endpoint. | Standar de-facto, bisa render UI + generate client. | Route API (`/api/v1` target) | 2026-09-13 |
| D-04 | **JS package manager = bun; build/lint frontend = vite-plus (`vp`)** di atas Vite 8, bukan npm/vite mentah. | Dipilih sejak scaffold; semua command yang ditulis mengikuti ini. | `package.json`, CI | (scaffold) |
| D-05 | **Modular Monolith (package-ready)** sebagai arsitektur target; monolit Laravel standar saat ini. Modul per domain (`Core`, `Kelas`, ...). | Cohesion + batas dependensi jelas tanpa mencekik dengan microservice. | `Modules/` (target) | (rancangan) |
| D-06 | **Otorisasi berbasis permission** (spatie/laravel-permission), bukan berbasis role secara langsung. | Granular & mudah dikontrol; role adalah kumpulan permission. | Controller/policy | (rancangan) |
| D-07 | **Bahasa Indonesia** untuk dokumentasi, komentar, dan commit message. | Konsistensi tim; CalVer changelog juga. | Repo | (scaffold) |
| D-08 | **Gate commit/push lokal (Husky)**: pre-commit = `vendor/bin/pint --dirty` + `bun run types:check`; pre-push = `composer test` + `bun run types:check`. | Menyamai gate CI (`composer test` + `types:check`); lint/format JS via `bun run check:fix` dilakukan dev manual. | `.husky/*`, dev lokal | 2026-09-14 |

## Cara Menambahkan

1. Beri nomor baru (`D-08`, dst) — jangan mengubah ulang nomor yang sudah ada.
2. Isi alasan singkat + ruang lingkup + tanggal.
3. Bila keputusan memengaruhi pekerjaan agent lintas file, mintakan pencatatan ke `.ai/rules/` (tool Boost `record-rule`) agar terdeteksi workflow `grep -rin` di `.ai/rules`.