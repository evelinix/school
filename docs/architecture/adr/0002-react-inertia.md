# ADR-0002: React TSX + Inertia.js

## Status
Accepted

## Konteks
Blueprint awal memakai SvelteKit. Namun tim existing menguasai React
dan membutuhkan integrasi yang lebih langsung dengan Laravel (tanpa
duplikasi routing).

## Keputusan
- **Admin Web:** Inertia.js 3 + React 19 + TypeScript + Vite
- **Mobile:** REST API murni (Flutter 3.45)

## Konsekuensi
- Tidak ada REST terpisah untuk admin (lebih efisien)
- Perlu API terpisah untuk mobile → Controller API + API Resource
- Type safety via Spatie Data + generated TS types