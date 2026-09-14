# Cheatsheet CLI

> Semua command di sini sudah diverifikasi terhadap repo. **JS package manager = bun** (bukan npm), **build/lint = vite-plus** (`vp`), bukan vite mentah.

## Setup Awal

```bash
# Clone & setup
git clone <repo> school-platform
cd school-platform
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
bun install
bun run build
```

## Development Harian

```bash
bun run dev                 # Vite dev server (vite-plus)
php artisan dev             # alias composer dev (serve + vite)
bun run build               # build produksi frontend
docker compose up -d mailpit rustfs   # mail + S3 lokal (RustFS 127.0.0.1:9000) — HANYA dua service ini
```

> **PostgreSQL dev TIDAK tersedia di docker-compose** (hanya mailpit + rustfs). DB dev adalah Postgres eksternal (`DB_CONNECTION=pgsql`, lihat `.env`). Pastikan service PG jalan sebelum `php artisan migrate` / `serve`.

## Kernel Core — Perintah Umum

```bash
# Migrasi kernel
php artisan migrate

# Seed data kernel
php artisan db:seed

# Cek kesehatan sistem
php artisan school:module:health

# Cek route
php artisan route:list
```

## Modul Fitur — Lifecycle

```bash
# Deteksi modul baru
php artisan school:module:discover

# Daftar modul
php artisan school:module:list

# Install modul (registrasi + migrasi)
php artisan school:module:install siswa

# Enable
php artisan school:module:enable siswa

# Disable (tanpa hapus data)
php artisan school:module:disable siswa

# Uninstall (production wajib --purge)
php artisan school:module:uninstall siswa --purge

# Buat modul baru
php artisan module:make NamaModul
```

> `Modules/` + `modules_statuses.json` belum ada — modul pertama menyiapkannya.

## Quality Gate

```bash
bun run check               # vp lint + format + typecheck (lokal). ⚠️ crash di headless/CI
bun run check:fix           # auto-fix lint/format
bun run types:check         # tsc --noEmit (yang dipakai Jenkins)
vendor/bin/pint --parallel --test     # cek format PHP
vendor/bin/pint --dirty               # fix format file yang diubah (dipakai pre-commit hook)
vendor/bin/phpstan analyse            # static analysis (level 7)
composer test               # gate lengkap: config:clear → pint --test → phpstan → artisan test
```

> Setelah mengubah file JS/TS apa pun, jalankan `bun run check:fix` sebelum commit (padanan reminder `pint --dirty`; pre-commit hanya menjamin `types:check`).

## Testing

```bash
vendor/bin/pest <path>                       # file spesifik
php artisan test --compact --filter=Name     # filter
php artisan test --compact --parallel        # paralel (paratest, seperti CI)

# Architecture test
php artisan test --filter=CoreBoundaryTest
php artisan test --filter=ModuleBoundaryTest
```

## Database

```bash
php artisan migrate
php artisan migrate:fresh --seed
php artisan migrate:status
php artisan db:show
php artisan db:seed --class=PermissionSeeder
```

- Lokal: PostgreSQL `school_platform` — **eksternal, bukan container** (lihat catatan docker compose di atas). Tests: SQLite `:memory:` (phpunit.xml).

## Routes & Types (Wayfinder)

```bash
php artisan route:list
php artisan wayfinder:generate          # tulis ulang resources/js/{actions,routes,wayfinder}
```

Hasilnya gitignored; commit HANYA jika ada perubahan source route.

## Cache & Optimization

```bash
php artisan optimize
php artisan optimize:clear
php artisan config:clear     # dijalankan otomatis oleh composer test
php artisan route:cache
php artisan event:cache
php artisan view:cache
```

## Reverb

```bash
php artisan reverb:start   # websocket server (Reverb v1.11 terinstal)
php artisan reverb:restart
```

## Debug & Insight

```bash
php artisan about
php artisan route:list --path=settings
php artisan route:list --path=api/v1
php artisan route:list --path=app
php artisan event:list
php artisan queue:failed
php artisan queue:retry all
php artisan pail                    # log streaming
php artisan tinker --execute 'User::count();'
```

## Frontend

```bash
bun run types:check        # typecheck saja
bun run build              # build + manifest; jalankan bila ViteException di browser
```
