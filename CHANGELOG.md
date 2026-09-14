# Changelog

Semua perubahan penting dicatat di file ini.
Format bebas terinspirasi [Keep a Changelog](https://keepachangelog.com/)
dan [Conventional Commits](https://www.conventionalcommits.org/).
Versi pakai CalVer `YYYY.MM.PATCH`.

Legend: ✨ feat · 🐛 fix · ⚡ perf · ♻️ refactor · 📝 docs · 🔧 chore · 📦 deps · 💥 break

---

## [2026.09.4] — 2026-09-14 · "Konsistensi Kode" 🧹

### ♻️ Refactor
- `refactor: sederhanakan referensi class provider di bootstrap` (#—). <!-- TODO: nomor PR -->

### 🐛 Fixed
- `fix: lengkapi tipe return DebugController` — lulus pemeriksaan PHPStan dan mencegah return type implisit (#—). <!-- TODO: nomor PR -->

### 📝 Documentation
- `docs: tambah architectural decision records` — dokumentasikan keputusan modular monolith, React dengan Inertia.js, dan ULID sebagai primary key (#—). <!-- TODO: nomor PR -->

---

## [2026.09.3] — 2026-09-14 · "Dependabot via Jenkins" 🐘

### 🔧 Chore
- `chore: migrasi automasi update dependency ke Jenkins` — tambah `Jenkinsfile.dependabot` (job terpisah, dependabot-core via Docker untuk `composer` & `npm`, dry-run + buka PR di `github.com`), hapus `.github/dependabot.yml` (#—). <!-- TODO: nomor PR -->

---

## [2026.09.2] — 2026-09-14 · "AI Guideline & Dev Gate" 🧭

### 📝 Documentation
- `docs: tambah panduan AI (folder .ai/) dan AGENTS.md berbahasa Indonesia` — status stack, arsitektur, standar kode, testing, workflow, checklist, decision log, rules (#—). <!-- TODO: nomor PR -->
- `docs: pindahkan aturan penulisan CHANGELOG ke .ai/changelog-rule.md` dan tambah dokumentasi folder `docs/` (#—). <!-- TODO: nomor PR -->
- `docs: tambah plan gate dokumen` — `docs/current.md` (plan aktif, wajib disetujui) & `docs/done/` (arsip plan selesai), termasuk mini-plan untuk tugas kecil (#—). <!-- TODO: nomor PR -->
- `docs: sinkronkan klaim status dan catat decision log serta rules` — D-08 (gate husky), `record-rule` untuk husky & plan gate, pertegas keamanan kredensial di respons (#—). <!-- TODO: nomor PR -->

### 🔧 Chore
- `chore: tambah fondasi spatie/laravel-permission & laravel-data dan nwidart/laravel-modules` — config, migrasi `permission_tables`, stubs, loader vite (#—). <!-- TODO: nomor PR -->
- `chore: perbarui hook Husky sesuai gate proyek` — pre-commit `pint --dirty` + `types:check` (scoped ke file JS), pre-push `composer test` + `types:check` (#—). <!-- TODO: nomor PR -->

---

## [2026.09.1] — 2026-09-14 · "CI & PWA" 🚀

### ✨ Added
- `feat: tambah Progressive Web App` — halaman bisa diinstal/offline via `manifest.json` + service worker `public/sw.js` (#—). <!-- TODO: nomor PR -->
- `feat: tambah security headers` untuk respons HTTP (#—). <!-- TODO: nomor PR -->

### 🐛 Fixed
- `fix: perbaiki generasi APP_KEY di Jenkins` — dibuat langsung via PHP dengan nilai yang konsisten, menggantikan `artisan key:generate` yang tumpang tindih dengan pengaturan APP_KEY (#—). <!-- TODO: nomor PR -->

### 🔧 Chore
- `chore: migrasi CI ke Jenkins` (linux-agent, PHP 8.4 + Node 22) dengan trigger via GitHub webhook (#—). <!-- TODO: nomor PR -->
- `chore: jadikan bun run types:check sebagai gate JS di CI` — `bun run check` dilewati karena crash headless (DataCloneError) (#—). <!-- TODO: nomor PR -->
- `chore: tambah hook Husky` — pre-commit Pint, pre-push seluruh suite test (#—). <!-- TODO: nomor PR -->

---

## [2026.09.0] — 2026-09-13 · "Pondasi" 🏗️

### ✨ Highlight
- Rilis perdana platform manajemen sekolah berbasis Laravel 13 + React 19 + Inertia v3 dengan autentikasi lengkap (login, register, 2FA TOTP, passkeys, reset password, verifikasi email). (#—) <!-- TODO: nomor PR -->

### ✨ Added

**Autentikasi & Keamanan**
- `feat: tambah autentikasi lengkap via Laravel Fortify` — login, register, logout, konfirmasi password (#—). <!-- TODO: nomor PR -->
- `feat: tambah Two-Factor Authentication (TOTP)` — setup modal, QR code, recovery codes, challenge page (#—). <!-- TODO: nomor PR -->
- `feat: tambah dukungan Passkeys (WebAuthn)` — register, verifikasi, dan manajemen passkey per user (#—). <!-- TODO: nomor PR -->
- `feat: tambah reset password dan verifikasi email` (#—). <!-- TODO: nomor PR -->

**Pengaturan Pengguna**
- `feat: tambah halaman Settings — Profile` — update nama, email, hapus akun (#—). <!-- TODO: nomor PR -->
- `feat: tambah halaman Settings — Security` — kelola 2FA, passkeys, update password (#—). <!-- TODO: nomor PR -->
- `feat: tambah halaman Settings — Appearance` — toggle tema light/dark/system (#—). <!-- TODO: nomor PR -->

**Frontend & UI**
- `feat: tambah layout aplikasi` — sidebar, header, breadcrumb, nav-user, nav-footer (#—). <!-- TODO: nomor PR -->
- `feat: tambah komponen UI lengkap berbasis Radix UI + Tailwind v4` — Button, Card, Dialog, Dropdown, Input, Select, Sidebar, Sheet, Sonner, Tooltip, dll (#—). <!-- TODO: nomor PR -->
- `feat: tambah layout autentikasi` — card, simple, split layout (#—). <!-- TODO: nomor PR -->
- `feat: tambah hooks` — `use-appearance`, `use-clipboard`, `use-flash-toast`, `use-initials`, `use-two-factor-auth`, `use-mobile` (#—). <!-- TODO: nomor PR -->
- `feat: tambah halaman Welcome dan Dashboard` (#—). <!-- TODO: nomor PR -->

**Backend & Infrastruktur**
- `feat: tambah migrasi database` — users, cache, jobs, passkeys, two_factor_columns (#—). <!-- TODO: nomor PR -->
- `feat: tambah User model` dengan traits `TwoFactorAuthenticatable`, `HasPasskeys` (#—). <!-- TODO: nomor PR -->
- `feat: tambah seeder dan factory pengguna` (#—). <!-- TODO: nomor PR -->
- `feat: tambah route settings` — profile, security, appearance (#—). <!-- TODO: nomor PR -->
- `feat: tambah middleware HandleInertiaRequests dan HandleAppearance` (#—). <!-- TODO: nomor PR -->
- `feat: tambah Form Requests untuk settings` — ProfileUpdateRequest, PasswordUpdateRequest, ProfileDeleteRequest, TwoFactorAuthenticationRequest (#—). <!-- TODO: nomor PR -->

**Tooling & Konfigurasi**
- `feat: tambah konfigurasi Vite` dengan plugin React, Inertia, Wayfinder, Tailwind (#—). <!-- TODO: nomor PR -->
- `feat: tambah GitHub Actions workflow` untuk test suite otomatis (#—). <!-- TODO: nomor PR -->
- `feat: tambah konfigurasi Dependabot` untuk update dependensi otomatis (#—). <!-- TODO: nomor PR -->
- `feat: tambah Docker Compose` untuk environment development (#—). <!-- TODO: nomor PR -->

### 📦 Dependencies

**PHP (Runtime)**
- `deps: laravel/framework 13.31.0` (#—). <!-- TODO: nomor PR -->
- `deps: laravel/fortify 1.39.0` (#—). <!-- TODO: nomor PR -->
- `deps: laravel/passkeys 0.2.1` (#—). <!-- TODO: nomor PR -->
- `deps: inertiajs/inertia-laravel 3.3.4` (#—). <!-- TODO: nomor PR -->
- `deps: laravel/wayfinder 0.1.21` (#—). <!-- TODO: nomor PR -->

**JavaScript (Runtime)**
- `deps: react 19.2.0 + react-dom 19.2.0` (#—). <!-- TODO: nomor PR -->
- `deps: @inertiajs/react 3.0.0` (#—). <!-- TODO: nomor PR -->
- `deps: tailwindcss 4.0.0` (#—). <!-- TODO: nomor PR -->
- `deps: @radix-ui/* (avatar, checkbox, dialog, dropdown-menu, select, sidebar, dll)` (#—). <!-- TODO: nomor PR -->
- `deps: lucide-react 0.475.0` (#—). <!-- TODO: nomor PR -->
- `deps: sonner 2.0.0` (#—). <!-- TODO: nomor PR -->

### 🔧 Chore
- `chore: tambah konfigurasi Pint, PHPStan (Larastan), Pest` (#—). <!-- TODO: nomor PR -->
- `chore: tambah .editorconfig, .gitattributes, .npmrc` (#—). <!-- TODO: nomor PR -->
- `chore: tambah AI agent skills dan rules` — Kiro/Agents skills untuk echo, fortify, inertia, testing, best-practices (#—). <!-- TODO: nomor PR -->
- `chore: tambah diagnostic tool` `public/php_check.php` untuk server health check (#—). <!-- TODO: nomor PR -->

---

<!-- TODO: tambahkan rilis berikutnya di atas baris ini -->
