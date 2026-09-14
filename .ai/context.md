# Konteks Proyek

## Identitas

- **Nama:** School Platform Enterprise
- **Tipe:** Modular Monolith (package-ready) — saat ini masih monolit Laravel standar
- **Model:** single-school sekarang, multi-school siap (via `school_id`)

## Tujuan Bisnis

Platform terpadu manajemen sekolah menengah di Indonesia:

1. Website publik & CMS
2. Akademik (kelas, jadwal, kurikulum)
3. Siswa & Guru
4. Raport & penilaian
5. Perpustakaan
6. Bank Soal & CAT (Computer Assisted Test)

## Stack Aktual (terverifikasi dari repo)

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 13.31, PHP 8.4, Fortify (auth), spatie/permission, spatie/data |
| Database | PostgreSQL **eksternal** (di luar docker-compose, `DB_CONNECTION=pgsql`); test pakai SQLite `:memory:` |
| Frontend | Inertia v3 + React 19 + TypeScript + Tailwind v4 |
| Build | **bun** + `vite-plus` (Vite 8), Wayfinder (typed routes) |
| Realtime | Echo + Reverb — **TERKONFIGURASI tapi `laravel/reverb` BELUM diinstal** |
| Storage | disk `s3` → RustFS (S3 lokal di docker-compose, endpoint `127.0.0.1:9000`) |
| CI/CD | Jenkins (`Jenkinsfile`) via **GitHub webhook**; job terpisah `Jenkinsfile.dependabot` untuk update dependency otomatis |
| Deploy | server di belakang Cloudflare Tunnel → Nginx → PHP-FPM |
| Bahasa | **Bahasa Indonesia** untuk dokumentasi & komentar |

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

## Modul yang Dirancang

1. `Core` — Sekolah, User, Role, Permission, Settings
2. `Website` — CMS, halaman publik
3. `Kelas` — Akademik, jadwal
4. `Siswa` — Data siswa, enrollment
5. `Guru` — Data guru, penugasan
6. `Raport` — Penilaian, raport
7. `Perpustakaan` — Katalog, peminjaman
8. `BankSoal` — Bank soal
9. `Cat` — Computer Assisted Test

Dependensi: `Core ← semua`; `Kelas ← Siswa, Guru, Raport`; `BankSoal ← Cat`; `Siswa ← Raport, Perpustakaan`.

## Status Saat Ini (penting, agar tidak salah asumsi)

- **PostgreSQL dev TIDAK ada di docker-compose** — compose hanya `mailpit` + `rustfs`. DB dev adalah Postgres eksternal yang harus dijalankan sendiri sebelum `migrate`/`serve`.
- **Belum ada `Modules/` dan `modules_statuses.json`** — `nwidart/laravel-modules` sudah dikonfigurasi (`config/modules.php`, `stubs/`, `vite-module-loader.js`, semua sudah di-commit), belum ada modul.
- **`modules_statuses.json` = state bersama** — dibuat otomatis saat modul pertama di-enable; WAJIB di-commit ke VCS, JANGAN masuk `.gitignore`.
- **spatie/laravel-permission & laravel-data terpasang** — config + migrasi `create_permission_tables` sudah di-commit; **belum ada `PermissionSeeder`/role yang di-assign**.
- **Reverb belum jalan** — broadcasting adalah scaffolding.
- **Tidak ada route `/api/...`** — masih web-only (Inertia). API `/api/v1` adalah target.
- **Dependabot GitHub di-nonaktifkan** — `.github/dependabot.yml` dihapus (hanya memantau `github-actions`). Penggantinya job Jenkins `Jenkinsfile.dependabot` (dependabot-core via Docker di `github.com`, ekosistem `composer` + `npm`), butuh Docker di agent + credential PAT **`github-pat`** (sudah ada di Jenkins, dipakai job `school`). Remote lokal memakai alias SSH `evelin-github.com` → `github.com`.
- `User` model memakai `PasskeyAuthenticatable`, `TwoFactorAuthenticatable` (Fortify).
- PWA aktif: `public/sw.js` + `manifest.json` + protocol handler `web+school://`.

## Sumber Kebenaran

- **Master plan & desain AI:** `docs/all.md`, `docs/architecture/file-structure.md`, `docs/README.md` (acuan rancangan — ter-track di git; pastikan bebas-secret)
- **Changelog:** `CHANGELOG.md` (CalVer, Bahasa Indonesia, ikuti `.ai/changelog-rule.md`)
- **Aturan ter-record:** `.ai/rules/` bila ada (dikelola Boost `record-rule`)