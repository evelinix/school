# Arsitektur Sistem Manajemen Sekolah

## 1. Ikhtisar

Sistem ini dibangun menggunakan **Modular Monolith** dengan Laravel 13,
PHP 8.4, dan PostgreSQL 18. Setiap fitur diisolasi menjadi modul
independen yang dapat dikembangkan, diuji, dan di-deploy secara terpisah.

## 2. Prinsip Desain

### 2.1 Service Layer Pattern
Semua logika bisnis terpusat di Service Layer. Controller (Web & API)
hanya bertugas menerima request dan mengembalikan response.

### 2.2 Repository Pattern
Akses data diisolasi melalui Repository Interface, sehingga
memudahkan penggantian implementasi (misalnya untuk testing).

### 2.3 DTO (Data Transfer Object)
Transfer data antar layer menggunakan DTO yang immutable
dan sudah tervalidasi.

### 2.4 Hybrid Controller
- **Web Controller** → Inertia.js + React TSX (admin panel)
- **API Controller** → REST JSON (mobile app Flutter)

Keduanya menggunakan Service Layer yang sama.

## 3. Struktur Modul

Setiap modul berada di `Modules/NamaModul/` dengan struktur:

- `Domain/` → Model, Enum, Event, Contract
- `Application/` → Action, DTO, Service
- `Infrastructure/` → Persistence, Provider
- `Http/` → Controller, Request, Resource
- `routes/` → Route web & API
- `database/` → Migrasi & Seeder
- `resources/` → Frontend (React) & Lang
- `tests/` → Unit & Feature test

## 4. Keamanan

- **Authentication**: Laravel Sanctum (token-based untuk API)
- **Authorization**: Spatie Permission (RBAC)
- **Input Validation**: Form Request & DTO
- **Output Encoding**: API Resource
- **Database**: Foreign key constraint, index, soft delete
- **Audit**: Log semua aksi kritis

## 5. Deployment

- **Web Server**: Nginx
- **Runtime**: PHP-FPM 8.4
- **Database**: PostgreSQL 18
- **Cache/Queue**: Redis
- **CI/CD**: Jenkins + GitLab Webhook
- **Server**: Fedora 44

## 6. Diagram Alur
```
┌─────────────┐     ┌─────────────┐
│  Flutter    │     │  React      │
│  (Mobile)   │     │  (Inertia)  │
└──────┬──────┘     └──────┬──────┘
       │                   │
       │ REST API          │ Inertia
       ▼                   ▼
┌─────────────────────────────────┐
│         Laravel 13              │
│  ┌───────────┐  ┌────────────┐  │
│  │ API       │  │ Web        │  │
│  │ Controller│  │ Controller │  │
│  └─────┬─────┘  └─────┬──────┘  │
│        │              │         │
│        └──────┬───────┘         │
│               ▼                 │
│        ┌─────────────┐          │
│        │  Service    │          │
│        │  Layer      │          │
│        └──────┬──────┘          │
│               ▼                 │
│        ┌─────────────┐          │
│        │ Repository  │          │
│        └──────┬──────┘          │
└───────────────┼─────────────────┘
                ▼
        ┌─────────────┐
        │ PostgreSQL  │
        │ 18          │
        └─────────────┘
```

---

# Ringkasan Command CLI

```bash
# 1. Inisialisasi proyek
laravel new school --php-version=8.4 --database=pgsql

# 2. Install dependensi
composer require nwidart/laravel-modules inertiajs/inertia-laravel \
    laravel/sanctum spatie/laravel-permission spatie/laravel-data
npm install react@^19.2 react-dom@^19.2 @inertiajs/react@^3

# 3. Buat modul
php artisan module:make Core
php artisan module:make Siswa
php artisan module:make Akademik
php artisan module:make Perpustakaan

# 4. Migrasi database
php artisan migrate

# 5. Jalankan development
php artisan serve
npm run dev
```