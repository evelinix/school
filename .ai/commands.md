# Cheatsheet CLI

> Semua command di sini sudah diverifikasi terhadap repo. **JS package manager = bun** (bukan npm), **build/lint = vite-plus** (`vp`), bukan vite mentah.

## Dependency & Boot

```bash
bun install                 # JS deps (lockfile: bun.lock; CI pakai --frozen-lockfile)
composer install            # PHP deps
composer setup              # salin .env + key:generate + migrate + bun install + bun run build
```

## Development Harian

```bash
bun run dev                 # Vite dev server (vite-plus)
php artisan dev             # alias composer dev (serve + vite)
bun run build               # build produksi frontend
docker compose up -d mailpit rustfs   # mail + S3 lokal (RustFS 127.0.0.1:9000) — HANYA dua service ini
```

> **PostgreSQL dev TIDAK tersedia di docker-compose** (hanya mailpit + rustfs). DB dev adalah Postgres eksternal (`DB_CONNECTION=pgsql`, lihat `.env`). Pastikan service PG jalan sebelum `php artisan migrate` / `serve`.

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
```

E2E (Playwright) — belum terpasang; baru aktif setelah `bun add -D @playwright/test`:

```bash
bunx playwright test        # jalankan E2E
bunx playwright install     # install browser
```

## Modul (nwidart/laravel-modules)

```bash
php artisan module:make NamaModul
php artisan module:list
php artisan module:enable NamaModul     # menulis modules_statuses.json
php artisan module:disable NamaModul
php artisan module:delete NamaModul
```

> `Modules/` + `modules_statuses.json` belum ada — modul pertama menyiapkannya.

## Routes & Types (Wayfinder)

```bash
php artisan route:list
php artisan wayfinder:generate          # tulis ulang resources/js/{actions,routes,wayfinder}
```

Hasilnya gitignored; commit HANYA jika ada perubahan source route.

## Database

```bash
php artisan migrate
php artisan migrate:fresh --seed
php artisan migrate:status
php artisan db:show
php artisan db:seed --class=PermissionSeeder
```

- Lokal: PostgreSQL `school_platform` — **eksternal, bukan container** (lihat catatan docker compose di atas). Tests: SQLite `:memory:` (phpunit.xml).

## Cache & Optimization

```bash
php artisan optimize
php artisan optimize:clear
php artisan config:clear     # dijalankan otomatis oleh composer test
```

## Debug & Insight

```bash
php artisan about
php artisan route:list --path=settings
php artisan event:list
php artisan pail                    # log streaming
php artisan tinker --execute 'User::count();'
```

## Frontend

```bash
bun run types:check        # typecheck saja
bun run build              # build + manifest; jalankan bila ViteException di browser
```

## Realtime (belum jalan — scaffolding)

```bash
php artisan reverb:start   # HANYA setelah composer require laravel/reverb
```