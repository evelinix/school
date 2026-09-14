# Alur Kerja Development

## Siklus Harian

1. Pull `main` terbaru.
2. Buat branch fitur: `feature/{ticket}-{desc}`.
3. Tulis kode + test.
4. Jalankan quality gate lokal (lihat di bawah).
5. Commit (Conventional Commits, Bahasa Indonesia).
6. Push → buat PR (GitHub) → Jenkins otomatis dijalankan via webhook.
7. Minta review → merge squash setelah approve.

## Quality Gate Lokal (urutan penting)

```bash
vendor/bin/pint --parallel --test          # format PHP (fix: vendor/bin/pint --parallel)
vendor/bin/phpstan analyse                 # static analysis level 7
bun run types:check                        # TypeScript (tsc --noEmit)
bun run check                              # JS lint+format+typecheck (lokal; di CI/dataheadless CRASH DataCloneError)
php artisan test --compact                 # test suite
```

Ringkasan satu perintah: `composer test` (config:clear → pint --test → phpstan → artisan test).

> **PENTING:** Jenkins hanya menjalankan `bun run types:check` (bukan `bun run check`) karena vite-plus `check` crash di environment headless (DataCloneError). Lokal tetap pakai `bun run check`.

## Menjaga `.ai/` Tetap Aktual

- Bila perubahan menyentuh status di `.ai/context.md` (stack, asset, modul, permission, Reverb, dependency, command), perbarui baris bersangkutan **di commit yang sama** dengan perubahan tersebut — bukan commit/doc terpisah.
- Kalimat "belum/target/aktual" di `.ai/` adalah fakta yang mudah basi; jika AGENTS.md/context.md mulai bertentangan dengan repo, ralat teksnya dibanding mengikuti teks lama.
- **Pemicu wajib sinkron `context.md` di commit yang sama:**
  1. Modul pertama dibuat / status `modules_statuses.json` berubah.
  2. `laravel/reverb` (atau broadcasting lain) benar-benar terinstal & jalan.
  3. Route `/api/*` pertama dibuat + Sanctum/guard API terpasang.
  4. `PermissionSeeder` pertama (role↔permission) di-commit.
  5. Perubahan stack/dependency utama (tambah/hapus package inti, migrasi storage, dll).

## Merge Ditolak Jika

- ❌ Test / lint / phpstan / typescript gagal
- ❌ Build frontend gagal
- ❌ Ada `dd()` / `dump()` / `console.log()` di kode produksi
- ❌ Ada migrasi lama diedit
- ❌ Ada secret di commit
- ❌ Komentar/docblock tidak Bahasa Indonesia

## Konvensi Branch & Commit

| Prefix | Penggunaan |
|--------|-----------|
| `feature/` | Fitur baru |
| `fix/` | Perbaikan bug |
| `hotfix/` | Darurat production |
| `chore/` | Perawatan, deps, docs |
| `refactor/` | Refactor tanpa ubah perilaku |
| `test/` | Tambah/perbaiki test saja |

Format commit: `<type>(<scope>): <deskripsi>` (Bahasa Indonesia).

## Review

Lihat `.ai/checklist/pull-request.md`.

## Deployment Flow

```
Push GitHub → Webhook → Jenkins (linux-agent)
    ├─ Install: composer install + bun install --frozen-lockfile
    ├─ Setup: .env dari .env.example + regenerate APP_KEY + migrate
    ├─ Build: bun run build
    ├─ Checks (parallel): types:check | pint --test | phpstan
    └─ Tests: php artisan test --parallel (sqlite :memory:)
```

- E2E (Playwright, bila terpasang): **stage Jenkins terpisah** setelah variabel di atas — bukan bagian `bun run check` (crash headless) dan bukan bagian suite Pest. Agent perlu `bunx playwright install --with-deps` (browser + system deps).

Produksi di belakang **Cloudflare Tunnel → Nginx → PHP-FPM**; aplikasi di `/app/school/public`. Proses deploy produksi mengikuti operasi manual (nginx config di `docs/tunel-fix.md`).

## Release

- Ikuti `.ai/changelog-rule.md` untuk menulis `CHANGELOG.md` (CalVer, Bahasa Indonesia, prefix type + link PR).