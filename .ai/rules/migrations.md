---
paths:
  - 'database/migrations/**'
---

# Migrations

## ULID untuk tabel bisnis baru
Tabel bisnis BARU memakai ULID: $table->ulid('id')->primary() + use HasUlids; di model. Tabel existing (users, passkeys, permission_tables) tetap bigint — jangan mengedit migrasi lama.
