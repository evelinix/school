# Siklus Hidup Modul Fitur

Modul fitur adalah **add-on** yang bisa dipasang/dilepas dari sistem.
Mereka hidup di `Modules/{Nama}/` dan dikelola oleh **ModuleManager**
di Kernel Core.

## 1. Diagram Siklus

```
DISCOVERED
    │  (php artisan school:module:discover)
    ▼
INSTALLABLE
    │  (php artisan school:module:install {slug})
    ▼
INSTALLED ─────────────┐
    │                  │
    │ (enable)         │ (uninstall)
    ▼                  ▼
ENABLED ◄──► DISABLED  UNINSTALLED
    │           │
    │ (disable) │ (enable)
    └───────────┘
```

## 2. Definisi Status

| Status | Arti | Data | Route |
|--------|------|------|-------|
| `discovered` | Ditemukan di filesystem, belum terdaftar | — | Tidak dimuat |
| `installed` | Terdaftar di tabel `modules`, migrasi dijalankan | Ada | Tidak dimuat |
| `enabled` | Aktif, provider & route dimuat | Ada | Dimuat |
| `disabled` | Non-aktif sementara, data tetap | Ada | Tidak dimuat |
| `uninstalled` | Dihapus dari registrasi | Tergantung `--purge` | Tidak dimuat |

## 3. Perintah CLI

```bash
# 1. Deteksi modul baru di filesystem
php artisan school:module:discover

# 2. Lihat daftar modul
php artisan school:module:list

# 3. Install modul (registrasi + migrasi)
php artisan school:module:install siswa

# 4. Aktifkan modul
php artisan school:module:enable siswa

# 5. Nonaktifkan modul (data tetap ada)
php artisan school:module:disable siswa

# 6. Uninstall modul (produksi wajib --purge)
php artisan school:module:uninstall siswa --purge

# 7. Cek kesehatan modul
php artisan school:module:health
```

## 4. Contoh Output `school:module:list`

```
+--------------+-------------+---------+-----------+-------+---------+
| Slug         | Nama        | Versi   | Status    | Aktif | Builtin |
+--------------+-------------+---------+-----------+-------+---------+
| website      | Website     | 1.0.0   | enabled   | ya    | tidak   |
| siswa        | Siswa       | 1.0.0   | enabled   | ya    | tidak   |
| guru         | Guru        | 1.0.0   | installed | tidak | tidak   |
| raport       | Raport      | 1.0.0   | disabled  | tidak | tidak   |
+--------------+-------------+---------+-----------+-------+---------+
```

> **Catatan:** Kernel Core tidak muncul di tabel ini.

## 5. Aturan Dependency

Modul dapat mendeklarasikan dependency pada modul lain:

```json
{
    "name": "Raport",
    "requires": {
        "core": "^1.0",
        "siswa": "^1.0",
        "kelas": "^1.0"
    }
}
```

Aturan:
- `core` = Kernel Core (selalu tersedia)
- Modul lain = harus sudah `installed` minimal
- **Circular dependency dilarang** — dideteksi oleh `ModuleDependencyResolver`
- **Disable** ditolak bila masih ada modul aktif yang bergantung
- **Uninstall** wajib men-disable dependen terlebih dahulu

## 6. Proteksi

### Disable tanpa kehilangan data
`disable` hanya:
- Matikan service provider
- Hapus rute dari registri
- Set status di tabel `modules` ke `disabled`

Data di tabel modul **TIDAK** dihapus.

### Uninstall dengan konfirmasi
`uninstall`:
- Minta konfirmasi interaktif
- Di production wajib `--purge` (mencegah kecelakaan)
- Dengan `--purge`: hapus file modul dari disk
- Tanpa `--purge`: hanya hapus baris di tabel `modules`

### Cek dependency sebelum uninstall
`assertNoActiveDependents()` menolak operasi bila ada modul aktif yang
bergantung pada modul target.

## 7. Middleware `EnsureModuleEnabled`

Route modul WAJIB dilindungi middleware ini:

```php
Route::middleware(['auth', 'school.context', 'module.enabled:siswa'])
    ->prefix('app/siswa')
    ->group(function (): void {
        Route::resource('students', StudentWebController::class);
    });
```

Bila modul `siswa` tidak `enabled`, request langsung di-`abort(404)`.

## 8. Membuat Modul Baru

Lihat `.ai/module-template.md` dan `.ai/checklist/new-module.md`.

## 9. Struktur Modul Wajib

```
Modules/{Nama}/
├── app/
│   ├── Domain/
│   ├── Application/
│   ├── Infrastructure/
│   └── Http/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── routes/
│   ├── web.php
│   └── api.php
├── resources/
├── tests/
├── module.json
└── composer.json
```

## 10. Larangan

- ❌ Modul tidak boleh dimuat tanpa registrasi (`module.json` wajib).
- ❌ Modul tidak boleh langsung menyentuh tabel modul lain.
- ❌ Modul tidak boleh `use Modules\{Lain}\...` selain yang dideklarasikan
  di `requires`.
- ❌ Modul tidak boleh menonaktifkan dirinya sendiri dari dalam.
- ❌ Modul tidak boleh mengubah kernel (folder `app/`).
