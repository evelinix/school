# Konteks Proyek

## Identitas

- **Nama:** School Platform Enterprise
- **Versi Target:** 1.0.0
- **Tipe:** Modular Monolith
- **Model:** Single-school sekarang, multi-school ready

## Tujuan Bisnis

Platform terpadu manajemen sekolah menengah Indonesia:
Website publik, Akademik, Siswa, Guru, Raport, Perpustakaan,
Bank Soal, CAT.

## Arsitektur Tingkat Tinggi

```
┌──────────────────────────────────────────────┐
│  Kernel Core (melebur di struktur Laravel)   │
│  app/, database/, routes/, config/            │
│  School, User, Role, Permission, Module,      │
│  Setting, Audit                               │
│  → SELALU AKTIF, tidak bisa di-disable        │
└──────────────────┬───────────────────────────┘
                   │  Modul bergantung pada Core
                   ▼
┌──────────────────────────────────────────────┐
│  Modul Fitur (Modules/)                      │
│  Website, Siswa, Guru, Kelas, Raport,        │
│  Perpustakaan, BankSoal, Cat                 │
│  → Punya lifecycle install/enable/disable    │
└──────────────────────────────────────────────┘
```

## Stack Aktual (terverifikasi dari repo)

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 13.31, PHP 8.4, Fortify (auth), spatie/permission, spatie/data, spatie/laravel-health, Telescope, Horizon, Scout (+ Meilisearch), Pulse, Reverb |
| Monitoring | Pulse (usage/queues/cache/slow-queries + Reverb connections & messages), Telescope, Horizon, spatie/health (`/health`), scheduler `health:check` tiap menit |
| Database | PostgreSQL **eksternal** (di luar docker-compose, `DB_CONNECTION=pgsql`); test pakai SQLite `:memory:` |
| Frontend | Inertia v3 + React 19 + TypeScript + Tailwind v4 |
| Build | **bun** + `vite-plus` (Vite 8), Wayfinder (typed routes) |
| Realtime | Echo + Reverb — **terinstal v1.11**, Echo terkonfigurasi (`resources/js/app.tsx` → `configureEcho`) |
| Storage | disk `s3` → RustFS (S3 lokal di docker-compose, endpoint `127.0.0.1:9000`) |
| Cache/Queue | Redis (`REDIS_CLIENT=phpredis`); queue default `database`, Horizon terpasang |
| Search | Meilisearch (`docker-compose`, `127.0.0.1:7700`) via Laravel Scout |
| CI/CD | Jenkins (`Jenkinsfile`) via **GitHub webhook**; job terpisah `Jenkinsfile.dependabot` untuk update dependency otomatis |
| Deploy | server di belakang Cloudflare Tunnel → Nginx → PHP-FPM |
| Bahasa | **Bahasa Indonesia** untuk dokumentasi & komentar |

## Pengguna & Role

| Role | Deskripsi |
|------|-----------|
| `super-admin` | Pemilik platform |
| `admin-sekolah` | Admin sekolah |
| `kepala-sekolah` | Kepala sekolah |
| `wakil-kepala-sekolah` | Wakil kepala sekolah |
| `guru` | Guru |
| `wali-kelas` | Wali kelas |
| `siswa` | Siswa |
| `orang-tua` | Orang tua |
| `pustakawan` | Pustakawan |
| `admin-ujian` | Admin ujian |
| `editor-konten` | Editor konten |

## Dependensi Antar Modul

```
Kernel Core ← semua modul
Kelas ← Siswa, Guru, Raport
BankSoal ← Cat
Siswa ← Raport, Perpustakaan
```

## Status Saat Ini (penting, agar tidak salah asumsi)

- **PostgreSQL dev TIDAK ada di docker-compose** — compose hanya `mailpit` + `rustfs`. DB dev adalah Postgres eksternal yang harus dijalankan sendiri sebelum `migrate`/`serve`.
- **Belum ada `Modules/` dan `modules_statuses.json`** — `nwidart/laravel-modules` sudah dikonfigurasi (`config/modules.php`, `stubs/`, `vite-module-loader.js`, semua sudah di-commit), belum ada modul.
- **`modules_statuses.json` = state bersama** — dibuat otomatis saat modul pertama di-enable; WAJIB di-commit ke VCS, JANGAN masuk `.gitignore`.
- **spatie/laravel-permission & laravel-data terpasang** — config + migrasi `create_permission_tables` sudah di-commit; **`PermissionSeeder` (permission kernel + role `super-admin` + assign evelin) sudah ada & ter-seed**.
- **Monitoring aktif** — Pulse (`/pulse`, gate `viewPulse`), Telescope (`/telescope`), Horizon (`/horizon`), spatie/laravel-health (`/health`, scheduler `health:check` per menit). Akses semua via role `super-admin` / permission `monitoring.monitoring.lihat`.
- **Role `super-admin` di-bypass** — `Gate::before` memberi super-admin semua permission (termasuk permission future); route memakai permission (`can:...`), bukan role langsung.
- **Reverb terinstal** — broadcasting sudah bisa dijalankan (`php artisan reverb:start`).
- **Tidak ada route `/api/...`** — masih web-only (Inertia). API `/api/v1` adalah target.
- **Dependabot GitHub di-nonaktifkan** — `.github/dependabot.yml` dihapus (hanya memantau `github-actions`). Penggantinya job Jenkins `Jenkinsfile.dependabot` (dependabot-core via Docker di `github.com`, ekosistem `composer` + `npm`), butuh Docker di agent + credential PAT **`github-pat`** (sudah ada di Jenkins, dipakai job `school`). Remote lokal memakai alias SSH `evelin-github.com` → `github.com`.
- `User` model memakai `PasskeyAuthenticatable`, `TwoFactorAuthenticatable` (Fortify).
- PWA aktif: `public/sw.js` + `manifest.json` + protocol handler `web+school://`.

## Sumber Kebenaran

- **Master plan & desain AI:** `docs/all.md`, `docs/architecture/file-structure.md`, `docs/README.md` (acuan rancangan — ter-track di git; pastikan bebas-secret)
- **Changelog:** `CHANGELOG.md` (CalVer, Bahasa Indonesia, ikuti `.ai/changelog-rule.md`)
- **Aturan ter-record:** `.ai/rules/` bila ada (dikelola Boost `record-rule`)
- **Aturan AI:** `AGENTS.md` + `.ai/`
