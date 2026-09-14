# Definisi Proyek — Sistem Manajemen Sekolah

## Identitas Proyek
- **Nama:** Sistem Manajemen Sekolah
- **Versi Target:** 1.0.0
- **Tipe:** Modular Monolith (package-ready)
- **Model Bisnis:** Single-school sekarang, multi-school ready

## Scope
Platform terpadu untuk manajemen sekolah mencakup:
Website publik, Akademik, Siswa, Guru, Raport, Perpustakaan, Bank Soal, CAT.

## Target Pengguna
1. Super Admin (platform owner)
2. School Admin
3. Kepala Sekolah & Wakil
4. Guru & Wali Kelas
5. Siswa
6. Orang Tua
7. Pustakawan, Admin Ujian, Editor Konten

## Target Deployment
- **Server:** Fedora 44 (bare metal / VPS)
- **Runtime:** PHP-FPM 8.4 + Nginx
- **Database:** PostgreSQL 18
- **Cache/Queue:** Redis 7+
- **Realtime:** Laravel Reverb
- **CI/CD:** Jenkins + GitLab Webhook

## Strategi Multi-School
Setiap entitas domain memiliki `school_id` (foreign ULID). Resolver
tenant berjalan via middleware `ResolveSchoolContext`. Isolasi data
diuji melalui arsitektur test.

## MVP vs Enterprise V1
- **MVP:** Core + Auth + RBAC + Siswa + Akademik + Website
- **Enterprise V1:** + Raport + Perpustakaan + Bank Soal + CAT + Analytics

## Future Modules
Keuangan, Payroll, Inventaris, Alumni, PPDB, E-Learning.