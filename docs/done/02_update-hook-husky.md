# Plan — Update Hook Husky agar Sesuai Proyek

- Tanggal: 2026-09-14
- Status: **selesai dieksekusi (Opsi A, telah disetujui)**
- Konteks: husky sudah terpasang (`prepare: husky`, hook di `.husky/`) tapi isinya belum menutup kedua sisi stack: siap `pint` hanya PHP; `pre-push` hanya `php artisan test` tanpa gate JS dan tanpa pint/phpstan. CI (Jenkins) memakai `composer test` (PHP) + `bun run types:check` (JS) — husky lokal sebaiknya menyamai.

## Tugas

### Opsi A (disarankan — ringan & selaras CI)
- `.husky/pre-commit`:
  ```sh
  vendor/bin/pint --dirty
  bun run types:check
  ```
- `.husky/pre-push`:
  ```sh
  composer test
  bun run types:check
  ```
- Alasan: `pint --dirty` memformat ulang file PHP yang diubah; `types:check` (`tsc --noEmit`) = gate JS yang dipakai Jenkins (aman, tidak crash headless). `composer test` = gate PHP penuh (config:clear → pint --test → phpstan level 7 → artisan test), lebih kuat dari `php artisan test` saja. Lint/format JS penuh tetap via `bun run check` / `check:fix` oleh developer.

### Opsi B (menyeluruh — lebih lambat)
- `pre-commit`: `vendor/bin/pint --dirty` + `bun run check:fix` (auto-fix lint+format+typecheck seluruh project, AKAN mengubah file apa pun).
- `pre-push`: `composer test` + `bun run types:check`.

**Rekomendasi: Opsi A** — cukup mencegat kesalahan tanpa melambankan setiap commit.

## Target File

| File | Perubahan |
|---|---|
| `.husky/pre-commit` | isi hook sesuai pilihan (default A) |
| `.husky/pre-push` | isi hook sesuai pilihan (default A) |
| `AGENTS.md` | baris "Hook Husky" disesuaikan dengan isi hook baru |
| `.ai/commands.md` / `.ai/workflow.md` | sebutan husky disinkronkan bila ada |

## Cek

- `git status` bersih dari perubahan tak terduga selain target.
- Kapabilitas: `bun`, `composer`, `vendor/bin/pint`, `php` ada di PATH dev (sudah, terbukti oleh hook lama).