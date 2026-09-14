# Plan — 05_migrasi-dependabot-ke-jenkins.md

- Status: **MENUNGGU PERSETUJUAN** (belum dikerjakan)
- Tanggal: 2026-09-14
- Kategori: infra + CI + docs (tugas besar → approval penuh, `.ai/workflow.md` §0)

## Tujuan
Menggantikan peran `.github/dependabot.yml` (yang hanya memantau `github-actions`, dan di GHES `evelin-github.com` dependabot native terbatas) dengan **job Jenkins periodik bernama dependabot** yang menjalankan dependabot-core via Docker untuk otomatis membuat PR update dependency `composer`/`npm`(bun).

## Prasyarat Infra (BLOCKER — harus dikonfirmasi/disiapkan pemilik)
1. Agent Jenkins yang menjalankan job punya **Docker** (host lokal ADA `29.8.0`, tapi agent `linux-agent` di Jenkins belum tentu sama).
2. **Credential GitHub token** di Jenkins (`credentialsId`, scope `repo` untuk GHES) untuk: clone, push branch, dan buka PR.
3. Agent perlu akses jaringan ke `evelin-github.com` (SSH/HTTPS + API) dan boleh pull image Docker registry.
4. Aplikasi "GitHub App"? Tidak — pakai **PAT** (cukup untuk dependabot-cli).

## Desain
- **File baru `Jenkinsfile.dependabot`** (pipeline, di-root repo) — dibuat tapi TIDAK dipicu oleh `githubPush()`; jalan sebagai **job Jenkins terpisah** (Freestyle pipeline) milik repo, trigger `cron` (mis. mingguan `H 6 * * 1`). Alasan: job beda → tidak ikut pipeline CI tiap push.
- **Hapus `.github/dependabot.yml`** (nonaktif; hanya `github-actions`, workflows kosong).
- Pipeline `Jenkinsfile.dependabot`:
  1. `Checkout`: repo ke workspace.
  2. `Dependabot (composer)`: jalankan `docker run --rm -v $PWD:/home/dependabot/dependabot-core ghcr.io/dependabot/dependabot-core:latest` dengan `--job-file` JSON `package_manager: composed`, `source` (provider ghes, hostname evelin-github.com, repo owner/name, branch main), `credentials` (host-key/token), `allowed_updates` → updates + buka PR.
  3. `Dependabot (npm/bun)`: runs sama, `package_manager: npm` → `package.json` + `bun.lock` (catat: dukungan `bun.lock` di dependabot-core npm updater — cek versi gambar saat implementasi).
  4. `post`: arsip log dependabot; `cleanWs()`.
- **Pin versi image** dependabot-core (mis. `2.2xx`) agar reproducible; update terjadwal.
- Kredensial: gunakan `credentials('github-token')` → env `GITHUB_ACCESS_TOKEN` + di-mount sebagai `--credentials-file`.

## File yang Disentuh
- `Jenkinsfile.dependabot` (baru)
- `.github/dependabot.yml` (hapus)
- `CHANGELOG.md` (baris rilis mendatang/2026.09.3)
- `.ai/context.md` (sinkron status CI: dependabot nonaktif → job Jenkins dependabot)
- `docs/README.md` (bila ada daftar CI → update)

## Langkah Kerja
1. Konfirmasi prasyarat infra (Docker agent + token + jaringan) bersama pemilik Jenkins.
2. Tulis `Jenkinsfile.dependabot` sesuai desain.
3. Hapus `.github/dependabot.yml`.
4. Sinkron `docs`/`.ai` + buat konfigurasi job di Jenkins (manual oleh pemilik, atau sediakan snippet).
5. Uji: jalankan job dependabot sekali manual → verifikasi branch/PR update muncul (dry-run `update_files` dulu, tanpa `create_pr`?).

## Verifikasi
- `pint/phpstan/pest` tidak terpengaruh (tidak menyentuh kode).
- `vim`/`bash -n`? (`bash -n`) sintaks Jenkinsfile ok; job dependabot kali pertama: log dependabot-core tanpa error; file lockfile berubah tanpa seharusnya (mode update mendeteksi "no update" → exit 0).
- Opsional: 1x `docker run` dependabot-core versi pin di host untuk memastikan image pullable.

## Catatan Keputusan Terkait
- Arah alternatif: pakai **Renovate self-hosted** (mendukung bun lockfile lebih baik) — tidak dipilih sekarang; catat untuk dievaluasi bila dependabot-core gagal di `bun.lock`.
## Status Pelaksanaan (2026-09-14)

- Repo-side: SELESAI (Jenkinsfile.dependabot + hapus .github/dependabot.yml + sinkron docs).
- TERSISA (manual, pemilik Jenkins): buat job "school-dependabot", credential PAT `github-token`, pastikan Docker di agent, lalu uji dry-run pertama.
