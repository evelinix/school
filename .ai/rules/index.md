# Project Rules Index

Before planning or editing, find the row whose globs match the file's path and read that rule file.

## Aturan Ter-record (dari tool Boost `record-rule`)

| Applies to | Rule file |
| --- | --- |
| docs/api/** | .ai/rules/api.md |
| database/migrations/** | .ai/rules/migrations.md |
| tests/** | .ai/rules/tests.md |

## Peta Konteks Area (fallback: `.ai/*.md`)

Untuk path di luar tabel di atas, baca file konteks pada baris yang cocok dengan path target (paling spesifik menang):

| Applies to | Context file |
| --- | --- |
| `bootstrap/**`, `config/**` | .ai/context.md |
| `routes/**` | .ai/api-contract.md |
| `Modules/**`, `.ai/module-template.md` | .ai/module-template.md |
| `database/migrations/**`, `database/factories/**`, model `$table->*` | .ai/database.md |
| `resources/js/pages/**`, `components/**`, `app.tsx` | .ai/coding-standards.md |
| `tests/**`, `vitest` | .ai/testing.md |
| `app/**` (Controller, Model, Service) | .ai/architecture.md, .ai/patterns.md, .ai/anti-patterns.md |
| `docs/api/**`, route API | .ai/api-contract.md |
| (semua) | .ai/context.md, .ai/decisions.md |

## Cara Pakai

1. Baca file aturan pertama yang glob-nya cocok dengan path yang disentuh.
2. Jalankan `grep -rin '<keyword>' .ai/rules` (+ `.ai/`) untuk aturan berbasis kata yang tidak tertangkap pemetaan path.
3. Ketidakcocokan teks `.ai/` vs repo → anggap teks usang, ralat teksnya; jangan mengikuti teks lama yang bertentangan (lihat `.ai/workflow.md`).