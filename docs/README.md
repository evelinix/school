# docs/ — Dokumentasi Proyek

Dokumen rancangan & catatan proyek School Platform Enterprise. Dibagi peran dengan `.ai/` (konteks AI) dan `.not_commit/` (material lokal yang tidak boleh di-commit).

## Isi Folder

| File/Folder | Isi |
|---|---|
| `README.md` | Indeks dokumentasi ini |
| `current.md` | **Plan tugas yang sedang berjalan** — wajib disetujui sebelum dikerjakan (Aturan Emas #9 / `.ai/workflow.md` §0); saat tugas baru dimulai, pindahkan (rename) ke `docs/done/` |
| `done/` | **Arsip plan** tugas yang sudah diselesaikan (`done/{nomor_nama_tugas}.md`) |
| `all.md` | Master plan & desain keseluruhan (acuan rancangan — ter-track di git; jaga bebas-secret) |
| `architecture/file-structure.md` | Struktur folder target modular monolith (acuan rancangan — ter-track di git; jaga bebas-secret) |

Dokumen yang berada di repo namun relevan:

| File | Isi |
|---|---|
| `CHANGELOG.md` (root) | Changelog rilis (CalVer, Bahasa Indonesia) |
| `.ai/changelog-rule.md` | Aturan penulisan CHANGELOG oleh AI |

## Menulis CHANGELOG

Saat merilis (menulis `CHANGELOG.md`), ikuti **`.ai/changelog-rule.md`** — format versi bebas (CalVer `YYYY.MM.PATCH`), Bahasa Indonesia, prefix tipe per entry, diakhiri link PR. Aturan ini dipindah dari `docs/` ke `.ai/` agar selalu terbaca oleh AI yang bekerja di repo.

## Pembagian Peran

| Tempat | Isi |
|---|---|
| `docs/` | Dokumen rancangan & desain (bacaan manusia, tidak ada secret) |
| `.ai/` | Konteks AI: status, aturan, checklist, template (dibaca agent via `AGENTS.md`) |
| `.not_commit/` | Material lokal yang **tidak boleh di-commit**: kredensial, sertifikat, `ssl/`, catatan ops server, `github_jenkins.md`, salinan aturan non-komit — gitignored (`*` di dalamnya) |

## Aturan Penting

- Jangan commit `.env` atau kredensial asli (Jenkins meregenerasi APP_KEY dari `.env.example`).
- Bila sebuah dokumen berisi info server/kredensial, taruh di `.not_commit/`, bukan di `docs/`.
- Materi rancangan (`all.md`, `file-structure.md`) kini **ter-track di git**; verifikasi bebas-secret sebelum menambah konten, dan pindahkan hal yang berisi kredensial ke `.not_commit/`.
- Siklus `current.md`/`done/`: sebelum mengerjakan tugas, cek `docs/current.md`; bila ada arsipkan ke `docs/done/{nomor_nama_tugas}.md`, buat plan baru, dan tunggu persetujuan — detail di `.ai/workflow.md` §0.