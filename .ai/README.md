# Folder `.ai/` — Konteks untuk AI Coding Agent

Folder ini berisi konteks granular yang dibaca oleh AI agent
(Claude Code, Cursor, Windsurf, Aider, dsb) saat bekerja pada proyek
**School Platform Enterprise**.

## Peta Baca Cepat

| Tugas | File yang dibaca |
|-------|------------------|
| Kerja di Kernel Core | `kernel-core.md`, `architecture.md`, `coding-standards.md` |
| Buat modul baru | `module-template.md`, `module-lifecycle.md`, `checklist/new-module.md` |
| Modifikasi modul fitur | `architecture.md`, `patterns.md`, `module-lifecycle.md` |
| Tambah endpoint | `api-contract.md`, `checklist/new-endpoint.md` |
| Tambah service kernel | `kernel-core.md`, `checklist/new-kernel-service.md` |
| Menulis test | `testing.md` |
| Tambah migrasi | `database.md`, `checklist/new-migration.md` |
| Sebelum PR | `checklist/pull-request.md` |

## Konvensi

- Bahasa Indonesia untuk seluruh konten.
- Format Markdown.
- Setiap file diawali ringkasan 1 paragraf.

## Batasan

- Tidak ada secret di folder ini.
- Tidak ada kode yang dieksekusi.
- Perubahan harus disetujui Tech Lead.

## Indeks File

| File | Isi |
|------|-----|
| `context.md` | Konteks proyek, stack, role, modul |
| `architecture.md` | Arsitektur dua kategori + aturan dependensi |
| `kernel-core.md` | **Panduan Kernel Core** (melebur ke Laravel) |
| `module-lifecycle.md` | **Siklus hidup modul fitur** |
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
| `glossary.md` | Daftar istilah domain + arsitektur |
| `checklist/*` | Checklist operasional |
