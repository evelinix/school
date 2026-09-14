# Plan — Sinkronisasi & Penyempurnaan AI Guidelines

- Tanggal: 2026-09-14
- Status: berjalan (lihat checklist di bawah)
- Konteks: setelah commit `359651f` (panduan AI) dan `ca8c93d` (fondasi spatie + modules), beberapa klaim status di `AGENTS.md` / `.ai/` menjadi basi. Plan ini berisi sinkronisasi + penyempurnaan agar guideline AI selalu akurat saat dibaca agent.

## Tugas

### 1. Sinkronkan klaim status basi
- **Sasaran:** `AGENTS.md:43` dan `.ai/context.md:67-68` masih menyebut "config + migrasi `create_permission_tables` belum di-commit".
- **Realisasi:** keduanya sudah di-commit `ca8c93d`.
- **Perbaikan:** ubah kalimat menjadi "config + migrasi sudah di-commit"; yang belum tinggal `Modules/`, `modules_statuses.json`, dan seeder role/permission.
- **Cek:** grep tidak lagi menemukan "belum di-commit"/"masih uncommitted".

### 2. Amanah `modules_statuses.json`
- File dibuat otomatis saat modul pertama di-enable (`module:enable`).
- **Aturan:** wajib di-commit ke VCS sebagai state bersama — JANGAN masuk `.gitignore`.
- **Target:** `.ai/context.md` (baris status modules) + `.ai/module-template.md` ("Koneksi Vite untuk Modul").

### 3. Seeder + test otorisasi spatie (persiapan modul Core)
- Belum ada `PermissionSeeder` deterministik, assignment Role↔Permission, maupun feature test otorisasi.
- **Aksi (guideline, bukan membangun modul):**
  - `new-module.md`: tambah poin checklist "Seeding & Otorisasi" — seeder deterministik, naming `modul.bagian.aksi`, role↔permission lewat seeder, verifikasi `db:seed`, ralat "belum ada role" di context.md saat seeder rilis.
  - `context.md`: pertegas bahwa spatie terpasang tapi belum ada seeder/role (bukan "masih uncommitted").
- **Catatan:** seeder itu sendiri dibuat saat mulai modul `Core`, bukan sekarang (hindari asumsi nama permission).

### 4. `.kiro/` vs `.agents/skills/`
- `.kiro/skills/` = salinan duplikat skill (gitignored); `.kiro/agents/` kosong; `.kiro/settings/` = konfigurasi agent lokal (lsp.json, mcp.json).
- **Keputusan:** tandai `.agents/skills/` sebagai **sumber kebenaran**; `.kiro/` hanya artefak lokal — jangan diperbarui/dipakai sebagai acuan.
- **Target:** `AGENTS.md` (Repo conventions) — catatan larangan meng-edit `.kiro/`.

### 5. Disiplin sinkron status (pemicu eksplisit)
- Aturan #3 (sinkron di commit yang sama) dipertegas dengan daftar pemicu struktural.
- **Target:** `.ai/workflow.md` → bagian "Menjaga .ai/ Tetap Aktual".
- Pemicu: modul pertama dibuat; Reverb/broadcasting terinstal & jalan; route `/api/*` pertama + Sanctum; PermissionSeeder rilis; perubahan stack/dependency utama.

## Catatan Eksekusi

- Tidak mengubah file di luar daftar Target di atas.
- Tidak menyentuh `.not_commit/`.
- Commit: tidak diinstruksikan; setelah eksekusi valid, siapkan untuk di-review.

## Checklist

- [x] Tugas 1 — klaim basi disinkron (AGENTS.md, context.md)
- [x] Tugas 2 — `modules_statuses.json` aturan commit (context.md, module-template.md)
- [x] Tugas 3 — checklist seeding & otorisasi (new-module.md) + catatan context.md
- [x] Tugas 4 — `.agents/skills/` sumber kebenaran (AGENTS.md)
- [x] Tugas 5 — pemicu sinkron status (workflow.md)