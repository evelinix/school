---
paths:
  - '.husky/**'
---

# General

## Gate commit/push lokal via hook Husky
Gate commit/push lokal: pre-commit = vendor/bin/pint --dirty, lalu bun run types:check HANYA bila ada file JS/TS di staging (resources/js/, package.json, tsconfig, vite.config). Pre-push = composer test + bun run types:check. Jangan menonaktifkan hook tanpa persetujuan.
