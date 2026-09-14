# School Platform — repo-specific

Backend Laravel 13.31 + PHP 8.4, SPA Inertia v3 + React 19 + Tailwind v4. **bun** adalah package manager JS; lapisan build/lint frontend memakai **vite-plus** (biner `vp`) di atas Vite 8, bukan vite mentah. DB dev lokal menggunakan PostgreSQL; test berjalan di SQLite `:memory:` (override di phpunit.xml).

> **Bahasa:** dokumentasi, komentar/docblock kode, dan commit message memakai **Bahasa Indonesia** (CalVer changelog juga). Ikuti `.ai/coding-standards.md`.

## Aturan Emas (ringkas — detail di `.ai/`)
1. **Explicit over Magic** — tidak ada facade/konvensi implisit yang tidak jelas.
2. **Security First** — validasi input, otorisasi eksplisit (permission, bukan role), hindari `request()->all()` tanpa validasi.
3. **Maintainability** — kode mudah dibaca 6 bulan kemudian; satu tanggung jawab per class.
4. **Production Ready** — kode tidak memuat `dd()`, `dump()`, `var_dump()`, `console.log()`, atau TODO tanpa tiket.
5. **Testable** — setiap service/action minimal happy path + 1 edge case.
6. **Jangan ubah file di luar scope**; **jangan hapus file** tanpa instruksi eksplisit.
7. **Migrasi tidak diedit setelah di-commit** — buat migrasi baru.
8. Jika ragu → **berhenti dan tanyakan**, jangan berasumsi.
9. **Plan sebelum eksekusi** — sebelum berkode, periksa `docs/current.md`; bila ada pindahkan ke `docs/done/{nomor_nama_tugas}.md`, buat plan baru di `docs/current.md`, dan tunggu persetujuan (detail: `.ai/workflow.md` §0). Tugas kecil (beberapa baris, kosmetik) boleh pakai **mini-plan** tanpa menunggu approval penuh.

Detail arsitektur, pattern, anti-pattern, testing, dan konvensi DB lengkap ada di `.ai/` (daftar di bawah).

## AI Helpers (folder `.ai/`)

`AGENTS.md` = entry point; `.ai/` = konteks granular. Baca sesuai tugas:
- `.ai/context.md` — stack aktual, status modul/permission/reverb, role
- `.ai/architecture.md` — layer module, arah dependensi, tenant scoping (target modular monolith)
- `.ai/coding-standards.md` — standar PHP/TS/DB/Git + naming + Bahasa Indonesia
- `.ai/module-template.md` — cara buat modul (`module:make` + fondasi `Modules/`)
- `.ai/patterns.md` & `.ai/anti-patterns.md` — pola yang dipakai / dihindari
- `.ai/testing.md` — strategi & contoh Pest
- `.ai/workflow.md` & `.ai/commands.md` — alur kerja + cheatsheet CLI yang sah
- `.ai/api-contract.md`, `.ai/security.md`, `.ai/database.md`, `.ai/decisions.md`, `.ai/changelog-rule.md`, `.ai/glossary.md`
- `.ai/checklist/*` — new-module, new-endpoint, new-migration, pull-request
- `.ai/rules/index.md` — peta glob → file aturan (dipakai workflow Boost saat membuat/mengedit file)

## Commands — yang paling sering dipakai
- Dev server: `bun run dev` · Build: `bun run build` · Typecheck: `bun run types:check` (`tsc --noEmit`)
- `bun run check` / `check:fix` = vp lint + format + typecheck dengan `denyWarnings: true`. **Lewati di headless/CI**: dikenal crash dengan DataCloneError di sana, jadi Jenkins hanya menjalankan `types:check`. Lokal, `bun run check` adalah pintu masuk JS. **Setelah mengubah file JS/TS apa pun, jalankan `bun run check:fix` sebelum commit** (padanan reminder `pint --dirty` untuk PHP; pre-commit hanya menjamin `types:check`).
- Gate PHP/JS lengkap: `composer test` (menjalankan `config:clear` → pint `--test` → phpstan level 7 → `php artisan test`). Run fokus lebih cepat: `composer lint:check` (pint) dan `composer types:check` (phpstan).
- Test: `vendor/bin/pest <path>` atau `php artisan test --compact [--filter=...]`. CI berjalan paralel (`--parallel`, paratest). Test memakai sqlite `:memory:` — tanpa DB, tanpa service.
- Hook Husky: pre-commit menjalankan `vendor/bin/pint --dirty` lalu `bun run types:check`; pre-push menjalankan `composer test` lalu `bun run types:check` (gate PHP penuh + gate JS versi CI). Setelah mengedit PHP apa pun jalankan `vendor/bin/pint --dirty` sebelum commit.
- Jika aset 404 di browser (`ViteException: Unable to locate file`), jalankan `bun run build` (atau `bun run dev`).

## Architecture
- Auth adalah **Fortify**: login/register/password reset/email verification/2FA-TOTP/passkeys (WebAuthn) semua aktif di `config/fortify.php`. Model User memakai `PasskeyAuthenticatable` + `TwoFactorAuthenticatable`; registrasi lewat `app/Actions/Fortify/CreateNewUser`. Route settings sensitif memakai `RequirePassword` dan/atau `throttle:6,1` (lihat `routes/settings.php`) — tiru pola itu untuk route sensitif baru.
- **Sistem modul baru di-setup, belum aktif**: `nwidart/laravel-modules` sudah dikonfigurasi (`config/modules.php`, `stubs/`, `vite-module-loader.js` — sudah di-commit) tapi belum ada direktori `Modules/` dan belum ada `modules_statuses.json`. spatie/laravel-permission dan spatie/laravel-data juga sudah terpasang (config + migrasi `create_permission_tables` sudah di-commit); belum ada `PermissionSeeder`/role yang di-assign. Jangan menganggap baiknya sebagai konvensi yang mapan.
- Storage: disk `s3` menarget **RustFS**, server S3-kompatibel lokal di `docker-compose.yaml` (`127.0.0.1:9000`, path-style, `AWS_ENDPOINT`). `docker compose up -d mailpit rustfs` menyalakan mail + S3 lokal.
- Broadcasting: Echo + Reverb sudah dikonfigurasi (`config/broadcasting.php`, `BROADCAST_CONNECTION=reverb`, `resources/js/app.tsx` → `configureEcho`) tapi **`laravel/reverb` belum diinstal** — realtime masih scaffolding, belum jalan.
- Trust proxies sengaja `at: '*'` di `bootstrap/app.php` karena produksi di belakang Cloudflare Tunnel → nginx. Jangan "perbaiki".

## Frontend conventions
- React Compiler aktif (babel `reactCompilerPreset`) — jangan menambahkan `useMemo`/`useCallback`/`memo` secara manual.
- Output Wayfinder (`resources/js/actions`, `routes`, `wayfinder`) **gitignored dan di-regenerate** — setelah mengubah route jalankan `php artisan wayfinder:generate`, import dari `@/actions` / `@/routes`. `formVariants: true` aktif, sehingga helper `.form()` tersedia.
- `resources/js/components/ui/*` adalah komponen gaya-shadcn hasil generate, dikecualikan dari lint/format — jangan edit manual.
- Layout halaman dipilih di `resources/js/app.tsx` (welcome → none, `auth/*` → AuthLayout, `settings/*` → AppLayout+SettingsLayout, selain itu → AppLayout).
- Perangkap penamaan: `resources/js/hooks/use-permissions.ts` adalah hook **browser** Permissions API (notifikasi/kamera/mikrofon/geolokasi) — tidak terkait spatie/laravel-permission.
- Komponen halaman memakai alias `@/`; pengurutan class tailwind ditangani `vp fmt` (`cn`, `clsx`, `cva`).
- Model memakai gaya atribut Laravel 13: `#[Fillable([...])]` / `#[Hidden([...])]` pengganti `$fillable`/`$hidden` (lihat `app/Models/User.php`).

## Repo conventions / ops
- CI adalah **Jenkins**, bukan GitHub Actions (`Jenkinsfile`: PHP 8.4, Node 22, bun on PATH, linux-agent). Tidak ada workflow GitHub yang menjalankan build.
- `.not_commit/` (material ops: kredensial, sertifikat, catatan server; gitignored lewat `.gitignore` berisi `*`) dan `.kiro/` tidak ikut commit. `.kiro/` = artefak lokal agent (salinan skill lama + settings `lsp.json`/`mcp.json`); **sumber kebenaran skill adalah `.agents/skills/`** — jangan meng-update/menyalin dari `.kiro/`. `docs/` berisi dokumen rancangan (master plan, struktur file) — jangan menaruh secret di sana; pastikan bebas-secret bila di-commit. Catatan rilis mengikuti `.ai/changelog-rule.md` saat menulis `CHANGELOG.md` (CalVer, Bahasa Indonesia). Jangan commit `.env` atau kredensial asli di dalamnya (Jenkins meregenerasi APP_KEY dari `.env.example`).
- PWA: service worker `public/sw.js` + `manifest.json`; app juga mendaftarkan protocol handler `web+school://` — pertahankan di `app.tsx`.

## Skills
File skill ada di `.agents/skills/` (echo-react, fortify, inertia-react, wayfinder, tailwindcss, testing, laravel-best-practices). Aktifkan skill yang cocok sebelum bekerja di domain tersebut (auth → fortify-development, wiring route→frontend → wayfinder-development, halaman React → inertia-react-development).

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application running on PHP 8.4. You are an expert with the Laravel ecosystem. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `bun run build`, `bun run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists (settled decisions, non-obvious traps, standing constraints). Framework and package guidelines that only apply to specific paths (testing, frontend, components) also live there, under `.ai/rules/boost` — this is not just recorded decisions, it is load-bearing guidance you have not seen inline. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.
- Record a rule with `record-rule` only when the user explicitly asks for one. Instructions for the work at hand are not rules, no matter how emphatic: "remove this typo", "use X here" are work to do, not rules to record. Never record a rule on your own initiative, as a byproduct of a change, or to summarize what you just did. When the user does ask, pass a `glob` (e.g. `app/Http/Controllers/**`), a short `title`, and a few-line `note`. Use `record-rule` rather than your native memory or notes tool, because native memory is personal and session-scoped, while only `.ai/rules` is shared with the team and persists in the repo.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `bun run build` or ask the user to run `bun run dev` or `composer run dev`.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.
- After the feature tests pass, ask the user to run the complete suite with `php artisan test --compact`.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

</laravel-boost-guidelines>
