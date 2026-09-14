# Checklist — Pull Request

## Sebelum Push

- [ ] Branch sesuai konvensi (`feature/...`, `fix/...`)
- [ ] Tidak ada file tidak sengaja ter-commit
- [ ] Tidak ada `.env` / secret / credential
- [ ] `vendor/bin/pint --parallel` dijalankan
- [ ] `vendor/bin/phpstan analyse` bersih
- [ ] `bun run types:check` bersih
- [ ] `bun run check` hijau (lokal; CI memakai `types:check` saja karena `check` crash DataCloneError di headless)
- [ ] `bun run build` sukses (bila ada perubahan frontend)
- [ ] `php artisan test --compact` hijau
- [ ] E2E Playwright hijau bila ada perubahan flow penting (saat `@playwright/test` sudah terpasang)
- [ ] Tidak ada `dd()`/`dump()`/`console.log()`/TODO tanpa tiket
- [ ] Komentar & docblock Bahasa Indonesia

## Deskripsi PR

- [ ] Judul mengikuti Conventional Commits
- [ ] Menjelaskan **apa** dan **mengapa**
- [ ] Screenshot bila ada perubahan UI
- [ ] Cara test manual
- [ ] Link tiket terkait

## Setelah Dibuat

- [ ] CI (Jenkins) hijau — catatan: Jenkins menjalankan `types:check`, bukan `bun run check`
- [ ] Minta review minimal 1 orang
- [ ] Respond semua komentar reviewer
- [ ] Merge dengan **squash** (bukan fast-forward)

## Setelah Merge

- [ ] Hapus branch remote
- [ ] Pantau deployment Jenkins
- [ ] Verifikasi di produksi/staging
- [ ] Update `CHANGELOG.md` bila perlu (ikuti `.ai/changelog-rule.md`)