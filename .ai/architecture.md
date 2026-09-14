# Arsitektur

> Target arsitektur adalah **Modular Monolith**. Saat ini repo masih monolit Laravel standar tanpa `Modules/` — pakai dokumen ini sebagai arah, bukan sebagai deskripsi kondisi kode saat ini.

## Peta Layer per Modul

Setiap modul memiliki **4 layer** yang wajib dipatuhi:

```
┌─────────────────────────────────────────────┐
│  Http (Presentation)                        │
│  Controller Web (Inertia) & API (JSON)      │
│  Request, Resource, Middleware              │
├─────────────────────────────────────────────┤
│  Application                                │
│  Service, Action, DTO                       │
│  Orkestrasi use-case, transaksi             │
├─────────────────────────────────────────────┤
│  Domain                                     │
│  Model, Enum, Event, Exception, Contract    │
│  Aturan bisnis murni                        │
├─────────────────────────────────────────────┤
│  Infrastructure                             │
│  Repository, Provider, Storage              │
│  Detail implementasi teknis                 │
└─────────────────────────────────────────────┘
```

## Arah Dependensi

```
Http → Application → Domain ← Infrastructure
```

- **Domain** tidak boleh tahu tentang Http/Infrastructure.
- **Application** hanya bergantung pada Domain.
- **Infrastructure** mengimplementasikan kontrak (interface) Domain.
- **Http** memanggil Application, tidak langsung ke Domain/Repository.

## Struktur Direktori Modul

```
Modules/NamaModul/
├── app/
│   ├── Domain/          # Models, Enums, Events, Exceptions, Contracts
│   ├── Application/     # Actions (invokable), DTO (Spatie Data), Services
│   ├── Infrastructure/  # Persistence/ (Eloquent repository), Providers/
│   └── Http/            # Controllers/Web, Controllers/Api, Requests, Resources
├── database/            # migrations/, factories/, seeders/
├── routes/              # web.php, api.php, channels.php
├── tests/               # Unit/, Feature/
└── module.json
```

## Komunikasi Antar Modul

**WAJIB** via **Domain Event**, bukan panggil service langsung antar modul:

```
Modul A ── dispatch(EventA) ──▶ Event Bus
                                    │
              ┌─────────────────────┼─────────────────────┐
              ▼                     ▼                     ▼
       Listener Modul B      Listener Modul C      Listener Modul D
```

Debug with `php artisan event:list`.

## Alur Request (target)

```
HTTP → Route → Middleware (auth, school.context) → Controller → Form Request → DTO → Service (transaksi) → Action → Repository → Model → PostgreSQL
```

## Tenant Context (multi-school)

Semua query data bisnis **WAJIB** terfilter `school_id`:

```php
// ✅ BENAR
Student::query()->where('school_id', $context->requireId())->get();

// ❌ SALAH — bocor lintas sekolah
Student::query()->get();
```

Prioritaskan **global scope** (`scopeForSchool()`) di model bila pola sudah mapan.

## Arsitektur Saat Ini (fakta repo)

- `app/` masih struktur default: `Models/`, `Http/Controllers/Settings/`, `Actions/Fortify/`, `Concerns/`, `Providers/`.
- Konfigurasi middleware & exception di `bootstrap/app.php` — jangan ubah tanpa instruksi.
- `trustProxies(at: '*')` **disengaja** (Cloudflare Tunnel → Nginx); jangan "perbaiki".
- Realtime: channel rencana `private-school.{id}`, `private-class.{id}`, `private-exam.{id}`, `private-user.{id}` (belum aktif).

## Queue (target)

```
high          → notifikasi kritis, OTP
default       → job umum
reports       → generate PDF raport
imports       → import Excel/CSV besar
notifications → email, push
cat           → processing CAT
```