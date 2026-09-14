# Checklist — Migrasi Baru

- [ ] Nama file: `YYYY_MM_DD_HHMMSS_verb_noun_table.php`
- [ ] `declare(strict_types=1);`
- [ ] Anonymous class (`return new class extends Migration`)
- [ ] `up()` dan `down()` keduanya diimplementasikan
- [ ] `timestamps()` (kecuali tabel pivot/relasi)
- [ ] `softDeletes()` bila entitas bisnis
- [ ] ULID + `foreignUlid` untuk tabel bisnis baru (existing tetap `bigint`)
- [ ] `onDelete` eksplisit (`restrict` / `cascade` / `null`)
- [ ] Index hanya untuk kolom yang di-query
- [ ] **TIDAK** mengedit migrasi lama yang sudah di-commit

## Verifikasi

```bash
php artisan migrate
php artisan migrate:rollback --step=1
php artisan migrate
```

## Catatan

- Ubah kolom → buat migrasi baru, bukan edit migrasi lama.
- Ada data existing → siapkan migrasi data terpisah.
- Production: `--force` + backup dulu.