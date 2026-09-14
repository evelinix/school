# Folder `.ai/` — Konteks untuk AI Coding Agent

Folder ini berisi konteks granular yang dibaca oleh AI agent saat bekerja pada proyek **School Platform Enterprise**.

## Cara Pakai

AI agent membaca `AGENTS.md` di root terlebih dahulu, lalu masuk ke folder ini untuk konteks spesifik. Urutan pembacaan yang dianjurkan:

0. **Plan sebelum berkode** — periksa `docs/current.md`; bila ada, arsipkan ke `docs/done/{nomor_nama_tugas}.md`, buat plan baru, tunggu persetujuan (`.ai/workflow.md` §0).
1. `context.md` — identitas & stack proyek
2. `architecture.md` — arsitektur & aturan layer
3. `coding-standards.md` — standar penulisan kode
4. File spesifik sesuai tugas (mis. `testing.md` bila menulis test, `module-template.md` bila membuat modul)

## Konvensi Penulisan

- **Bahasa Indonesia** untuk seluruh konten, komentar, docblock, dan commit message.
- Format Markdown dengan heading `##` dan `###`.
- Contoh kode wajib disertai komentar penjelas.
- Setiap file diawali ringkasan singkat.
- **Status/stack di `.ai/context.md` wajib disinkronkan di commit yang sama** dengan perubahan yang menyentuhnya (lihat `.ai/workflow.md`).

## Batasan

- Folder ini **tidak boleh** dipakai untuk menyimpan secret / credential.
- Jangan menaruh kode yang dieksekusi (`*.php`, `*.ts`) di sini — hanya Markdown.
- Perubahan pada folder ini harus disetujui Tech Lead.

## Indeks File

| File | Isi |
|------|-----|
| `context.md` | Konteks proyek, stack, role, modul |
| `architecture.md` | Arsitektur modular + aturan dependensi |
| `coding-standards.md` | Standar kode PHP / TS / DB / Git |
| `module-template.md` | Template pembuatan modul baru |
| `patterns.md` | Pattern yang direkomendasikan |
| `anti-patterns.md` | Yang wajib dihindari |
| `testing.md` | Strategi testing |
| `workflow.md` | Alur kerja development + quality gate |
| `commands.md` | Cheatsheet CLI yang benar di repo ini |
| `api-contract.md` | Kontrak API & DTO |
| `security.md` | Aturan keamanan wajib |
| `database.md` | Konvensi database |
| `decisions.md` | Keputusan arsitektur/kebijakan (ADR) |
| `changelog-rule.md` | Aturan penulisan CHANGELOG oleh AI (CalVer) |
| `glossary.md` | Daftar istilah domain |
| `checklist/*` | Checklist operasional |