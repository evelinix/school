# Glosarium Domain

| Istilah | Definisi |
|---------|----------|
| **School** | Entitas sekolah (unit bisnis utama, untuk multi-tenant) |
| **NPSN** | Nomor Pokok Sekolah Nasional (ID resmi sekolah) |
| **NIS** | Nomor Induk Siswa (ID siswa dalam sekolah) |
| **NISN** | Nomor Induk Siswa Nasional (ID siswa dari pemerintah) |
| **Academic Year** | Tahun ajaran (mis. 2026/2027) |
| **Semester** | Semester (Ganjil / Genap) |
| **Level** | Tingkat (SD kelas 1-6, SMP kelas 7-9, dst) |
| **Major** | Jurusan (IPA, IPS, Bahasa) — untuk SMA/SMK |
| **Class** | Kelas (mis. X-A, XI IPA 1) |
| **Room** | Ruang fisik |
| **Subject** | Mata pelajaran |
| **Curriculum** | Kurikulum (K13, Merdeka) |
| **Homeroom Teacher** | Wali kelas |
| **Raport** | Laporan hasil belajar siswa |
| **Assessment** | Penilaian (formatif, sumatif) |
| **Grade** | Nilai |
| **Behavior** | Sikap / perilaku siswa |
| **Extracurricular** | Ekstrakurikuler |
| **Loan** | Peminjaman (perpustakaan) |
| **Fine** | Denda |
| **Question** | Butir soal |
| **Bank Soal** | Kumpulan soal |
| **CAT** | Computer Assisted Test |
| **Exam Session** | Sesi ujian |
| **Attempt** | Percobaan mengerjakan ujian |
| **Proctoring** | Pengawasan ujian |
| **Correlation ID** | ID pelacak satu request lintas service |
| **Tenant** | Sekolah (konteks multi-school) |
| **Module** | Modul fitur (Siswa, Raport, dll) |
| **Manifest** | File `module.json` yang menjelaskan modul |
| **Registry** | Status modul (`modules_statuses.json`) |
| **Passkey** | Kredensial WebAuthn pengganti password |
| **Two-Factor (2FA)** | Verifikasi dua langkah, TOTP di Fortify |

---

Flutter / admin target memakai istilah di atas konsisten agar API & UI satu bahasa.