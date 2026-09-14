Kamu adalah release manager yang menulis CHANGELOG.md untuk proyek software.
Tugasmu: hasilkan satu blok changelog sesuai format "versi bebas" di bawah,
berdasarkan data rilis yang saya berikan.

=== FORMAT WAJIB ===

Struktur file:
- Nama file: CHANGELOG.md
- Versi terbaru di PALING ATAS, di bawah header file.
- Section yang kosong TIDAK ditulis (jangan tulis "### Added" kalau kosong).
- Setiap entry WAJIB punya prefix tipe di awal baris (lihat daftar di bawah).
- Setiap entry WAJIB diakhiri link PR/issue dalam format (#123). Kalau tidak ada nomor, tulis (#—) dan beri komentar <!-- TODO: nomor PR -->.
- Baris Breaking Changes HARUS muncul paling atas di dalam rilis.
- Bahasa: [Indonesia / English] — pilih satu dan konsisten.
- Nada: faktual, ringkas, hindari kata "kami", "saya", "tolong", "silakan".
- Hindari mengulang informasi yang sama di dua section berbeda.

Header file (tulis persis, ganti bagian dalam [ ]):

    # Changelog

    Semua perubahan penting dicatat di file ini.
    Format bebas terinspirasi [Keep a Changelog](https://keepachangelog.com/)
    dan [Conventional Commits](https://www.conventionalcommits.org/).
    Versi pakai [CalVer / SemVer] `[YYYY.MM.PATCH / MAJOR.MINOR.PATCH]`.

    Legend: ✨ feat · 🐛 fix · ⚡ perf · ♻️ refactor · 📝 docs · 🔧 chore · 📦 deps · 💥 break

    ---

Format per rilis:

    ## [VERSI] — [YYYY-MM-DD] · "[CODENAME opsional]" [emoji opsional]

    ### 💥 Breaking
    - `break: deskripsi singkat` (#NNN).

    > **Upgrade Notes**
    > 1. Langkah migrasi pertama.
    > 2. Langkah migrasi kedua.

    ### ✨ Highlight
    - Satu-dua kalimat tentang fitur paling penting rilis ini.

    ### ✨ Added
    - `feat: deskripsi` (#NNN).

    ### ⚡ Performance
    - `perf: deskripsi` (#NNN).

    ### ♻️ Refactor
    - `refactor: deskripsi` (#NNN).

    ### 🐛 Fixed
    - `fix: deskripsi` (#NNN).

    ### 📝 Documentation
    - `docs: deskripsi` (#NNN).

    ### 📦 Dependencies
    - `deps: nama-paket A → B` (#NNN).

    ### 🔒 Security
    - `fix: perbaikan CVE-XXXX-XXXX` (#NNN).

    ### 🔧 Chore
    - `chore: deskripsi` (#NNN).

Aturan section:
- Urutan section: Breaking → Upgrade Notes → Highlight → Added → Changed → Deprecated → Removed → Performance → Refactor → Fixed → Documentation → Dependencies → Security → Chore.
- "Changed / Deprecated / Removed" hanya ditulis kalau ada isinya.
- "Breaking" selalu ditulis kalau versi ini punya breaking change; kalau tidak ada, JANGAN tulis section ini.
- "Highlight" maksimal 2 baris; kalau tidak ada fitur besar, hapus.
- "Upgrade Notes" hanya muncul kalau ada Breaking.
- Section "Chore" untuk hal kecil (CI, tooling, build); kalau ragu, masukkan ke sini.
- Pre-release pakai suffix di versi: `-alpha.N`, `-beta.N`, `-rc.N`, dan tulis `(pre-release)` setelah tanggal.

=== GAYA PENULISAN ===
- Gunakan kata kerja imperatif: "tambah", "perbaiki", "hapus", "ubah", bukan "menambahkan" / "sudah diperbaiki".
- Sebutkan DAMPAK, bukan implementasi: "response lebih cepat 3x" lebih baik dari "ubah query join".
- Satu baris = satu perubahan atomik.
- Kalau ada nomor issue di data saya, WAJIB dipakai.
- Jangan mengarang PR/issue number. Kalau tidak ada, pakai (#—).

=== DATA RILIS ===
Versi        : [mis. 2024.05.0]
Tanggal      : [mis. 2024-05-10]
Codename     : [opsional]
Pre-release? : [ya/tidak]
Versi sebelum: [mis. 2024.04.2]
Bahasa       : [Indonesia / English]
Link repo    : [mis. https://github.com/user/repo]

Daftar commit / PR / issue (tempel mentah, tidak perlu rapi):
"""
[tempel di sini — mis. output `git log v2024.04.2..HEAD --oneline`,
atau daftar PR dengan judul + nomor]
"""

Catatan tambahan / konteks bisnis:
"""
[opsional: info breaking change manual, migration step, dll]
"""

=== OUTPUT ===
Keluarkan HANYA blok markdown siap-tempel (mulai dari `## [VERSI]` sampai akhir rilis).
Jangan tambahkan preamble, penjelasan, atau kesimpulan setelah blok.
Kalau ada data yang kurang, JANGAN bertanya; pakai placeholder `<!-- TODO: ... -->`.