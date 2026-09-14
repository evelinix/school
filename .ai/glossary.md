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
| **Passkey** | Kredensial WebAuthn pengganti password |
| **Two-Factor (2FA)** | Verifikasi dua langkah, TOTP di Fortify |
| **docs/current.md** | Plan tugas yang sedang berjalan — wajib disetujui sebelum dikerjakan (Aturan Emas #9) |
| **docs/done/** | Arsip plan tugas selesai (`done/{nomor_nama_tugas}.md`) |
| **Mini-plan** | Plan ringkas untuk tugas kecil (tanpa menunggu approval penuh — `.ai/workflow.md` §0) |
| **vp** | Biner `vite-plus` untuk build/lint/check frontend |

## Istilah Arsitektur

| Istilah | Definisi |
|---------|----------|
| **Kernel Core** | Fondasi aplikasi yang melebur ke struktur Laravel (`app/`, `database/`, `routes/`, `config/`). Selalu aktif. |
| **Modul Fitur** | Add-on di `Modules/` dengan siklus hidup install/enable/disable/uninstall. |
| **Boundary** | Batas arsitektur yang dijaga oleh Architecture Test. |
| **Lifecycle** | Siklus hidup modul: discovered → installed → enabled → disabled → uninstalled. |
| **Module Manager** | Service kernel (`App\Services\ModuleManager`) yang mengelola lifecycle modul fitur. |
| **Manifest** | File `module.json` di root setiap modul fitur. |
| **Registry** | Tabel `modules` di database yang mencatat status modul fitur. |
| **Ensure Module Enabled** | Middleware kernel yang memblokir request bila modul non-aktif. |
| **CoreBoundaryTest** | Architecture test yang memastikan Kernel tidak mengimpor modul. |
| **ModuleBoundaryTest** | Architecture test yang mencegah modul saling mengimpor langsung. |
| **Purge** | Flag `--purge` pada uninstall yang menghapus file modul dari disk. |

---

Flutter / admin target memakai istilah di atas konsisten agar API & UI satu bahasa.
