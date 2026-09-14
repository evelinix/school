# 📂 Dokumen Struktur File — School Platform Enterprise

> Dokumen ini adalah **peta lengkap** struktur direktori dan file untuk seluruh proyek. Setiap folder dijelaskan fungsinya, dan file-file kunci dicantumkan agar developer (manusia maupun AI agent) memiliki acuan tunggal saat navigasi repositori.

---

## 1. Ikhtisar Level Root

```text
school-platform/
├── backend/                      # Laravel 13 (API + Web Inertia)
├── frontend/                     # (Opsional) React standalone / admin terpisah
├── mobile/                       # (Opsional) Flutter 3.45
├── packages/                     # Hasil extraction modul (Composer package)
├── infrastructure/               # Nginx, systemd, Jenkins, scripts ops
├── docs/                         # Dokumentasi proyek (Bahasa Indonesia)
├── scripts/                      # Skrip utilitas (dev, build, deploy)
├── .ai/                          # Konteks untuk AI coding agent
├── .github/                      # (Opsional) integrasi GitHub mirror
├── AGENTS.md                     # Panduan AI coding agent
├── Jenkinsfile                   # Pipeline CI/CD (root, agar mudah dibaca)
├── README.md                     # Entry point developer
├── CHANGELOG.md                  # Riwayat perubahan rilis
├── LICENSE                       # Lisensi proyek
├── .editorconfig                 # Konsistensi editor lintas tim
├── .gitignore                    # Daftar file yang tidak di-commit
└── .gitlab-ci.yml                # (Opsional) trigger webhook ke Jenkins
```

### Penjelasan Singkat Root

| Folder/File | Fungsi |
|-------------|--------|
| `backend/` | Aplikasi Laravel utama (monolith dengan `Modules/`) |
| `frontend/` | Placeholder untuk UI standalone (mis. kiosk, admin terpisah) |
| `mobile/` | Proyek Flutter 3.45 (konsumen REST API `/api/v1`) |
| `packages/` | Composer package hasil ekstraksi modul (Phase 34) |
| `infrastructure/` | Konfigurasi server, Nginx, systemd, Jenkins agent |
| `docs/` | Dokumentasi arsitektur, API, deployment |
| `scripts/` | Skrip utilitas (setup, seed, backup) |
| `.ai/` | Konteks granular untuk AI coding agent |
| `Jenkinsfile` | Definisi pipeline CI/CD |
| `AGENTS.md` | Aturan emas untuk AI coding agent |

---

## 2. Struktur `backend/` (Laravel 13)

```text
backend/
├── app/                                  # Kode aplikasi utama (Laravel default)
│   ├── Core/                             # ★ Kernel inti (cross-module)
│   │   ├── Domain/
│   │   │   ├── Contracts/                # Interface shared (mis. FileContract)
│   │   │   ├── Enums/                    # Enum global (mis. Environment)
│   │   │   ├── Events/                   # Domain event lintas modul
│   │   │   ├── Exceptions/               # Exception dasar aplikasi
│   │   │   └── ValueObjects/             # Value Object (mis. Money)
│   │   ├── Application/
│   │   │   ├── Actions/                  # Action cross-module
│   │   │   ├── DTO/                      # DTO cross-module
│   │   │   └── Services/
│   │   │       ├── ModuleManager.php
│   │   │       ├── ModuleDependencyResolver.php
│   │   │       ├── SchoolContextService.php
│   │   │       ├── AuditService.php
│   │   │       ├── FileService.php
│   │   │       ├── NotificationService.php
│   │   │       ├── SearchService.php
│   │   │       └── HealthService.php
│   │   ├── Infrastructure/
│   │   │   ├── Persistence/
│   │   │   │   └── BaseRepository.php
│   │   │   ├── Providers/
│   │   │   │   └── CoreServiceProvider.php
│   │   │   ├── Logging/
│   │   │   │   ├── CorrelationIdProcessor.php
│   │   │   │   └── StructuredLogger.php
│   │   │   └── Health/
│   │   │       └── HealthCheckRegistry.php
│   │   └── Presentation/
│   │       ├── Console/
│   │       │   ├── ModuleListCommand.php
│   │       │   ├── ModuleInstallCommand.php
│   │       │   ├── ModuleEnableCommand.php
│   │       │   ├── ModuleDisableCommand.php
│   │       │   ├── ModuleUninstallCommand.php
│   │       │   └── ModuleHealthCommand.php
│   │       └── Http/
│   │           ├── Controllers/
│   │           │   ├── HealthController.php
│   │           │   └── DashboardController.php
│   │           ├── Middleware/
│   │           │   ├── EnsureModuleEnabled.php
│   │           │   ├── ResolveSchoolContext.php
│   │           │   ├── AssignCorrelationId.php
│   │           │   └── SecurityHeaders.php
│   │           ├── Requests/
│   │           └── Responses/
│   │               └── ApiResponse.php
│   ├── Http/
│   │   └── Middleware/
│   │       └── HandleInertiaRequests.php
│   ├── Models/
│   │   └── User.php                      # (Delegasi ke Modul Core)
│   ├── Policies/
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── EventServiceProvider.php
├── bootstrap/
│   ├── app.php                           # Konfigurasi middleware & exception
│   ├── providers.php                     # Daftar service provider
│   └── cache/                            # Cache bootstrap (gitignored)
├── config/                               # Konfigurasi Laravel
│   ├── app.php
│   ├── auth.php
│   ├── broadcasting.php                  # Konfigurasi Reverb
│   ├── cache.php
│   ├── core.php                          # ★ Konfigurasi Core
│   ├── cors.php
│   ├── database.php
│   ├── filesystems.php
│   ├── inertia.php
│   ├── logging.php
│   ├── modules.php                       # Konfigurasi nwidart/laravel-modules
│   ├── permission.php                    # Spatie Permission
│   ├── queue.php
│   ├── sanctum.php
│   ├── services.php
│   └── session.php
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   ├── migrations/                       # Migrasi global (users, cache, jobs)
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   └── ...
│   └── seeders/
│       └── DatabaseSeeder.php
├── Modules/                              # ★ Semua modul fitur
│   ├── Core/                             # Modul inti
│   ├── Website/                          # Modul website & CMS
│   ├── Kelas/                            # Modul akademik
│   ├── Siswa/                            # Modul siswa
│   ├── Guru/                             # Modul guru
│   ├── Raport/                           # Modul raport
│   ├── Perpustakaan/                     # Modul perpustakaan
│   ├── BankSoal/                         # Modul bank soal
│   └── Cat/                              # Modul CAT
├── public/
│   ├── index.php
│   ├── .htaccess
│   ├── favicon.ico
│   ├── robots.txt
│   └── build/                            # Asset hasil Vite (gitignored)
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/                               # ★ Frontend React + Inertia
│   │   ├── app.tsx                       # Entry point Inertia
│   │   ├── Components/                   # Komponen reusable
│   │   │   ├── ui/                       # Design system primitives
│   │   │   │   ├── Button.tsx
│   │   │   │   ├── Input.tsx
│   │   │   │   ├── Select.tsx
│   │   │   │   ├── Dialog.tsx
│   │   │   │   ├── DataTable.tsx
│   │   │   │   ├── Pagination.tsx
│   │   │   │   ├── Toast.tsx
│   │   │   │   ├── Badge.tsx
│   │   │   │   ├── Avatar.tsx
│   │   │   │   ├── Tooltip.tsx
│   │   │   │   ├── Tabs.tsx
│   │   │   │   ├── Switch.tsx
│   │   │   │   ├── Checkbox.tsx
│   │   │   │   ├── DatePicker.tsx
│   │   │   │   ├── FileUpload.tsx
│   │   │   │   ├── EmptyState.tsx
│   │   │   │   ├── Skeleton.tsx
│   │   │   │   └── CommandPalette.tsx
│   │   │   └── app/                      # Komponen spesifik aplikasi
│   │   │       ├── AppShell.tsx
│   │   │       ├── Sidebar.tsx
│   │   │       ├── Topbar.tsx
│   │   │       ├── Breadcrumb.tsx
│   │   │       ├── PageHeader.tsx
│   │   │       ├── NotificationBell.tsx
│   │   │       └── NotificationList.tsx
│   │   ├── Layouts/
│   │   │   ├── AppLayout.tsx
│   │   │   ├── AuthLayout.tsx
│   │   │   └── PublicLayout.tsx
│   │   ├── Pages/                        # ★ Halaman Inertia
│   │   │   ├── Auth/
│   │   │   │   ├── Login.tsx
│   │   │   │   ├── ForgotPassword.tsx
│   │   │   │   └── ResetPassword.tsx
│   │   │   ├── Dashboard/
│   │   │   │   └── Index.tsx
│   │   │   ├── Siswa/
│   │   │   │   ├── Index.tsx
│   │   │   │   ├── Create.tsx
│   │   │   │   ├── Edit.tsx
│   │   │   │   └── Show.tsx
│   │   │   ├── Guru/
│   │   │   ├── Kelas/
│   │   │   ├── Raport/
│   │   │   ├── Perpustakaan/
│   │   │   ├── BankSoal/
│   │   │   ├── Cat/
│   │   │   ├── Website/
│   │   │   └── Errors/
│   │   │       ├── 403.tsx
│   │   │       ├── 404.tsx
│   │   │       └── 500.tsx
│   │   ├── lib/
│   │   │   ├── api/
│   │   │   │   ├── client.ts
│   │   │   │   ├── auth.ts
│   │   │   │   ├── errors.ts
│   │   │   │   └── index.ts
│   │   │   ├── auth/
│   │   │   │   └── useAuth.ts
│   │   │   ├── permissions/
│   │   │   │   └── usePermission.ts
│   │   │   ├── realtime/
│   │   │   │   └── useReverb.ts
│   │   │   ├── modules/
│   │   │   │   ├── registry.ts
│   │   │   │   ├── loader.ts
│   │   │   │   └── types.ts
│   │   │   ├── stores/
│   │   │   │   ├── theme.ts
│   │   │   │   └── notifications.ts
│   │   │   ├── utils/
│   │   │   │   ├── format.ts
│   │   │   │   ├── date.ts
│   │   │   │   └── validation.ts
│   │   │   └── types/
│   │   │       ├── generated.d.ts        # Hasil `typescript:transform`
│   │   │       └── index.ts
│   │   └── hooks/
│   │       ├── useForm.ts
│   │       └── useToast.ts
│   └── views/
│       └── app.blade.php                 # Root Blade template Inertia
├── routes/
│   ├── web.php                           # Route web global
│   ├── api.php                           # Route API global
│   ├── channels.php                      # Konfigurasi broadcasting
│   └── console.php                       # Route console (scheduler)
├── storage/
│   ├── app/
│   │   ├── private/                      # File privat (raport PDF, dokumen siswa)
│   │   └── public/                       # File publik (logo, media website)
│   ├── framework/
│   │   ├── cache/
│   │   ├── sessions/
│   │   └── views/
│   └── logs/
│       ├── laravel.log
│       ├── security.log
│       ├── queue.log
│       └── realtime.log
├── tests/
│   ├── Unit/
│   ├── Feature/
│   ├── Integration/
│   ├── Architecture/
│   ├── CreatesApplication.php
│   └── TestCase.php
├── .env.example                          # Template environment
├── .env.testing                          # Environment untuk test
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json
├── phpstan.neon                          # Konfigurasi PHPStan
├── phpunit.xml                           # Konfigurasi PHPUnit
├── pint.json                             # Konfigurasi Laravel Pint
├── tsconfig.json                         # Konfigurasi TypeScript
├── vite.config.ts                        # Konfigurasi Vite
├── tailwind.config.js                    # Konfigurasi Tailwind (bila v3)
└── README.md
```

---

## 3. Struktur Modul (Detail Setiap `Modules/{Nama}`)

Berikut contoh lengkap untuk **Modul Siswa**. Modul lain mengikuti pola yang sama.

```text
Modules/Siswa/
├── app/
│   ├── Domain/
│   │   ├── Models/
│   │   │   ├── Student.php
│   │   │   ├── Parent.php
│   │   │   └── Enrollment.php
│   │   ├── Enums/
│   │   │   ├── StudentStatus.php
│   │   │   └── Gender.php
│   │   ├── Events/
│   │   │   ├── StudentCreated.php
│   │   │   ├── StudentUpdated.php
│   │   │   ├── StudentEnrolled.php
│   │   │   └── StudentArchived.php
│   │   ├── Exceptions/
│   │   │   ├── StudentNotFoundException.php
│   │   │   └── DuplicateStudentException.php
│   │   └── Contracts/
│   │       ├── StudentRepositoryInterface.php
│   │       └── EnrollmentRepositoryInterface.php
│   ├── Application/
│   │   ├── Actions/
│   │   │   ├── CreateStudentAction.php
│   │   │   ├── UpdateStudentAction.php
│   │   │   ├── ArchiveStudentAction.php
│   │   │   ├── EnrollStudentAction.php
│   │   │   └── ImportStudentsAction.php
│   │   ├── DTO/
│   │   │   ├── StudentData.php
│   │   │   ├── EnrollmentData.php
│   │   │   └── StudentFilterData.php
│   │   └── Services/
│   │       ├── StudentService.php
│   │       └── EnrollmentService.php
│   ├── Infrastructure/
│   │   ├── Persistence/
│   │   │   ├── EloquentStudentRepository.php
│   │   │   └── EloquentEnrollmentRepository.php
│   │   ├── Providers/
│   │   │   └── SiswaServiceProvider.php
│   │   └── Imports/
│   │       └── StudentsImport.php         # Maatwebsite/Excel
│   └── Http/
│       ├── Controllers/
│       │   ├── Web/
│       │   │   ├── StudentWebController.php
│       │   │   ├── EnrollmentWebController.php
│       │   │   └── ImportWebController.php
│       │   └── Api/
│       │       ├── StudentApiController.php
│       │       └── EnrollmentApiController.php
│       ├── Requests/
│       │   ├── StoreStudentRequest.php
│       │   ├── UpdateStudentRequest.php
│       │   ├── ImportStudentsRequest.php
│       │   └── StudentFilterRequest.php
│       ├── Resources/
│       │   ├── StudentResource.php
│       │   ├── StudentCollection.php
│       │   └── EnrollmentResource.php
│       └── Policies/
│           └── StudentPolicy.php
├── database/
│   ├── migrations/
│   │   ├── 2025_02_01_000001_create_students_table.php
│   │   ├── 2025_02_01_000002_create_parents_table.php
│   │   ├── 2025_02_01_000003_create_enrollments_table.php
│   │   └── 2025_02_01_000004_create_student_documents_table.php
│   ├── factories/
│   │   ├── StudentFactory.php
│   │   └── EnrollmentFactory.php
│   └── seeders/
│       ├── SiswaDatabaseSeeder.php
│       └── DemoStudentSeeder.php
├── routes/
│   ├── web.php
│   ├── api.php
│   └── channels.php
├── resources/
│   ├── lang/
│   │   ├── id/
│   │   │   └── siswa.php
│   │   └── en/
│   │       └── siswa.php
│   └── js/                                # Komponen spesifik modul (opsional)
│       └── Pages/
│           └── Students/
│               ├── Index.tsx
│               ├── Create.tsx
│               ├── Edit.tsx
│               ├── Show.tsx
│               └── Import.tsx
├── config/
│   └── siswa.php
├── tests/
│   ├── Unit/
│   │   ├── StudentServiceTest.php
│   │   └── CreateStudentActionTest.php
│   ├── Feature/
│   │   ├── Web/
│   │   │   └── StudentWebTest.php
│   │   └── Api/
│   │       └── StudentApiTest.php
│   └── Architecture/
│       └── ModuleBoundaryTest.php
├── module.json
├── composer.json
├── README.md
└── CHANGELOG.md
```

### Fungsi Layer Modul

| Layer | Fungsi | Contoh |
|-------|--------|--------|
| `Domain/` | Aturan bisnis murni, tidak tahu teknis | `Student`, `StudentCreated` |
| `Application/` | Orkestrasi use-case | `CreateStudentAction`, `StudentService` |
| `Infrastructure/` | Implementasi teknis | `EloquentStudentRepository` |
| `Http/` | Kontrak masuk/keluar | `StudentWebController`, `StudentResource` |

---

## 4. Struktur `frontend/` (Opsional)

Disiapkan untuk **React standalone** (mis. kiosk, admin panel terpisah di masa depan). Saat ini admin utama tetap di dalam `backend/resources/js/` karena Inertia.

```text
frontend/
├── src/
│   ├── App.tsx
│   ├── main.tsx
│   ├── api/
│   ├── components/
│   ├── features/
│   ├── hooks/
│   ├── layouts/
│   ├── routes/
│   ├── stores/
│   ├── types/
│   └── utils/
├── public/
├── tests/
│   ├── unit/
│   └── e2e/
├── index.html
├── package.json
├── tsconfig.json
└── vite.config.ts
```

---

## 5. Struktur `mobile/` (Flutter 3.45)

```text
mobile/
├── lib/
│   ├── main.dart
│   ├── app.dart
│   ├── core/
│   │   ├── config/
│   │   │   └── app_config.dart
│   │   ├── constants/
│   │   ├── errors/
│   │   │   ├── failure.dart
│   │   │   └── exception.dart
│   │   ├── network/
│   │   │   ├── api_client.dart
│   │   │   ├── interceptors/
│   │   │   │   ├── auth_interceptor.dart
│   │   │   │   └── logging_interceptor.dart
│   │   │   └── endpoints.dart
│   │   ├── storage/
│   │   │   └── secure_storage.dart
│   │   └── utils/
│   ├── features/
│   │   ├── auth/
│   │   │   ├── data/
│   │   │   │   ├── datasources/
│   │   │   │   ├── models/
│   │   │   │   └── repositories/
│   │   │   ├── domain/
│   │   │   │   ├── entities/
│   │   │   │   ├── repositories/
│   │   │   │   └── usecases/
│   │   │   └── presentation/
│   │   │       ├── blocs/
│   │   │       ├── pages/
│   │   │       └── widgets/
│   │   ├── siswa/
│   │   ├── guru/
│   │   ├── raport/
│   │   └── perpustakaan/
│   └── shared/
│       ├── widgets/
│       └── theme/
├── test/
├── android/
├── ios/
├── pubspec.yaml
└── analysis_options.yaml
```

**Catatan:** Flutter mengonsumsi API `/api/v1` yang disediakan backend. Autentikasi via **Sanctum Bearer Token**.

---

## 6. Struktur `packages/` (Hasil Ekstraksi)

Setelah boundary antar modul stabil (Phase 34), modul diekstraksi menjadi Composer package.

```text
packages/
├── school-core/
│   ├── src/
│   │   ├── Domain/
│   │   ├── Application/
│   │   ├── Infrastructure/
│   │   ├── Http/
│   │   └── CoreServiceProvider.php
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   ├── routes/
│   ├── config/
│   ├── resources/
│   ├── tests/
│   ├── composer.json
│   ├── README.md
│   ├── CHANGELOG.md
│   └── LICENSE
├── school-website/
├── school-kelas/
├── school-siswa/
├── school-guru/
├── school-raport/
├── school-perpustakaan/
├── school-bank-soal/
└── school-cat/
```

---

## 7. Struktur `infrastructure/`

```text
infrastructure/
├── nginx/
│   ├── school-platform.conf               # Server block produksi
│   ├── school-platform-staging.conf
│   └── snippets/
│       ├── security-headers.conf
│       └── ssl.conf
├── systemd/
│   ├── school-queue.service               # Queue worker
│   ├── school-scheduler.service           # Scheduler
│   ├── school-reverb.service              # WebSocket server
│   └── school-backup.service              # Backup otomatis
├── jenkins/
│   ├── Jenkinsfile                        # (Symlink ke root)
│   ├── agent-setup.sh                     # Setup Jenkins agent di Fedora 44
│   └── credentials-template.md
├── database/
│   ├── postgresql.conf.snippet            # Setting tuning PG 18
│   ├── pg_hba.conf.snippet
│   └── init-extensions.sql                # pgcrypto, citext, dll
├── redis/
│   └── redis.conf.snippet
├── scripts/
│   ├── provision-fedora44.sh              # Setup server baru
│   ├── deploy.sh                          # Skrip deploy manual (fallback)
│   ├── rollback.sh                        # Rollback ke rilis sebelumnya
│   └── health-check.sh                    # Cek kesehatan server
└── README.md
```

---

## 8. Struktur `docs/`

```text
docs/
├── README.md                              # Indeks dokumentasi
├── architecture/
│   ├── overview.md                        # Gambaran arsitektur
│   ├── modular-monolith.md
│   ├── layer-rules.md
│   ├── multi-tenant.md
│   ├── event-driven.md
│   ├── realtime-reverb.md
│   ├── queue-strategy.md
│   ├── file-storage.md
│   └── adr/
│       ├── 0001-modular-monolith.md
│       ├── 0002-postgresql-18.md
│       ├── 0003-react-inertia.md
│       ├── 0004-redis.md
│       ├── 0005-reverb.md
│       ├── 0006-sanctum.md
│       ├── 0007-ulid.md
│       ├── 0008-module-system.md
│       ├── 0009-jenkins-cicd.md
│       ├── 0010-fedora-44-deployment.md
│       └── 0011-flutter-api-contract.md
├── api/
│   ├── README.md
│   ├── authentication.md
│   ├── conventions.md
│   ├── errors.md
│   ├── pagination.md
│   ├── versioning.md
│   ├── openapi.yaml                       # Kontrak OpenAPI 3.1
│   ├── CHANGELOG.md
│   └── modules/
│       ├── core.md
│       ├── siswa.md
│       ├── guru.md
│       ├── kelas.md
│       ├── raport.md
│       ├── perpustakaan.md
│       ├── bank-soal.md
│       └── cat.md
├── database/
│   ├── conventions.md
│   ├── erd.md                             # Entity Relationship Diagram
│   ├── indexing-strategy.md
│   ├── migration-policy.md
│   └── extensions.md
├── modules/
│   ├── README.md
│   ├── lifecycle.md
│   ├── manifest-spec.md
│   ├── dependency-rules.md
│   ├── core.md
│   ├── website.md
│   ├── kelas.md
│   ├── siswa.md
│   ├── guru.md
│   ├── raport.md
│   ├── perpustakaan.md
│   ├── bank-soal.md
│   └── cat.md
├── security/
│   ├── baseline.md
│   ├── rbac.md
│   ├── authorization.md
│   ├── audit-logging.md
│   ├── file-upload.md
│   ├── sensitive-data.md
│   └── incident-response.md
├── deployment/
│   ├── overview.md
│   ├── fedora-44-setup.md
│   ├── nginx.md
│   ├── php-fpm.md
│   ├── postgresql-18.md
│   ├── redis.md
│   ├── reverb.md
│   ├── queue-workers.md
│   ├── ssl-tls.md
│   └── zero-downtime.md
├── operations/
│   ├── monitoring.md
│   ├── logging.md
│   ├── backup-restore.md
│   ├── disaster-recovery.md
│   ├── runbook.md
│   └── troubleshooting.md
├── development/
│   ├── getting-started.md
│   ├── coding-standards.md
│   ├── git-workflow.md
│   ├── testing.md
│   ├── debugging.md
│   ├── frontend.md
│   ├── backend.md
│   └── mobile.md
├── ci-cd/
│   ├── jenkins-setup.md
│   ├── gitlab-webhook.md
│   ├── pipeline-stages.md
│   ├── quality-gates.md
│   └── secrets-management.md
└── roadmap/
    ├── phase-00-06.md
    ├── phase-07-14.md
    ├── phase-15-22.md
    ├── phase-23-32.md
    └── phase-33-38.md
```

---

## 9. Struktur `.ai/`

```text
.ai/
├── README.md
├── context.md
├── architecture.md
├── coding-standards.md
├── module-template.md
├── patterns.md
├── anti-patterns.md
├── testing.md
├── workflow.md
├── commands.md
├── api-contract.md
├── security.md
├── database.md
├── glossary.md
└── checklist/
    ├── new-module.md
    ├── new-endpoint.md
    ├── new-migration.md
    └── pull-request.md
```

---

## 10. Struktur `scripts/`

```text
scripts/
├── setup.sh                               # Setup development awal
├── dev.sh                                 # Jalankan dev (serve + vite + queue + reverb)
├── test.sh                                # Jalankan semua test
├── lint.sh                                # Jalankan pint + phpstan + tsc + eslint
├── format.sh                              # Auto-format semua kode
├── migrate-fresh.sh                       # Reset DB + seed
├── seed-demo.sh                           # Isi data demo
├── generate-types.sh                      # Generate TS dari OpenAPI/DTO
├── module-new.sh                          # Wrapper pembuatan modul
├── module-install.sh                      # Install + enable modul
├── backup.sh                              # Backup DB + storage
├── restore.sh                             # Restore dari backup
└── README.md
```

---

## 11. Struktur `.github/` (Opsional — Mirror)

Hanya disiapkan bila ingin mencerminkan repo ke GitHub (mis. untuk open source mirror).

```text
.github/
├── workflows/
│   └── mirror-to-gitlab.yml               # Sinkronisasi (opsional)
├── ISSUE_TEMPLATE/
│   ├── bug_report.md
│   └── feature_request.md
├── PULL_REQUEST_TEMPLATE.md
└── CODEOWNERS
```

---

## 12. File Konfigurasi Root

| File | Fungsi |
|------|--------|
| `AGENTS.md` | Panduan AI coding agent (Bahasa Indonesia) |
| `Jenkinsfile` | Pipeline CI/CD dengan GitLab webhook |
| `README.md` | Onboarding developer baru |
| `CHANGELOG.md` | Riwayat rilis (Keep a Changelog) |
| `LICENSE` | Lisensi (proprietary / MIT sesuai kebijakan) |
| `.editorconfig` | Konsistensi indentasi |
| `.gitignore` | File yang tidak di-commit |
| `.gitattributes` | Normalisasi line ending, ekspor arsip |
| `.gitlab-ci.yml` | (Opsional) trigger webhook ke Jenkins |
| `.dockerignore` | (Opsional) bila memakai Docker untuk dev |
| `docker-compose.yml` | (Opsional) environment dev |
| `Makefile` | (Opsional) shortcut perintah |

---

## 13. Peta Ringkas Level Direktori

Berikut ringkasan 3 level pertama untuk memudahkan navigasi cepat:

```text
school-platform/
├── AGENTS.md
├── Jenkinsfile
├── README.md
├── CHANGELOG.md
├── LICENSE
├── .editorconfig
├── .gitignore
├── .ai/                                    # 15 file konteks AI
├── backend/                                # Laravel 13 + Modules
│   ├── app/Core/                           # Kernel inti
│   ├── Modules/                            # 9 modul fitur
│   ├── config/                             # 20 file konfigurasi
│   ├── database/                           # Migrasi global + seeder
│   ├── resources/js/                       # React + Inertia
│   ├── routes/                             # web, api, channels
│   ├── tests/                              # Unit, Feature, Integration, Architecture
│   └── ...
├── frontend/                               # (Opsional) React standalone
├── mobile/                                 # (Opsional) Flutter 3.45
├── packages/                               # Composer package hasil ekstraksi
├── infrastructure/                         # Nginx, systemd, Jenkins, scripts
├── docs/                                   # 13 kategori dokumentasi
└── scripts/                                # Utilitas developer & ops
```

---

## 14. Aturan Penamaan Folder & File

| Konteks | Konvensi | Contoh |
|---------|----------|--------|
| Modul | PascalCase | `Siswa`, `BankSoal`, `Perpustakaan` |
| Sub-folder layer | PascalCase | `Domain`, `Application`, `Http` |
| File PHP Class | PascalCase | `StudentService.php` |
| File PHP Migration | snake_case + timestamp | `2025_02_01_000001_create_students_table.php` |
| File TypeScript Komponen | PascalCase.tsx | `StudentCard.tsx` |
| File TypeScript Utility | kebab-case.ts | `format-date.ts` |
| File Blade | kebab-case.blade.php | `app.blade.php` |
| File Dokumentasi | kebab-case.md | `modular-monolith.md` |
| File Config | kebab-case.php | `school-platform.conf` |

---

## 15. Batasan & Aturan Struktur

1. **`app/Core/`** tidak boleh bergantung pada `Modules/*` (dijaga oleh Architecture Test).
2. **Setiap modul** wajib punya folder `Domain/`, `Application/`, `Infrastructure/`, `Http/`.
3. **`resources/js/`** hanya berisi kode frontend aplikasi utama (Inertia). Kode frontend milik modul disimpan di `Modules/{Nama}/resources/js/`.
4. **`packages/`** kosong sampai Phase 34; setelah itu diisi Composer package.
5. **`storage/app/private/`** untuk file privat (raport PDF, dokumen siswa), **`storage/app/public/`** untuk media publik.
6. **`docs/`** adalah sumber kebenaran dokumentasi. Kode yang tidak ter-dokumentasi dianggap **belum selesai**.
7. **`.ai/`** tidak boleh berisi kode yang dieksekusi, hanya Markdown.

---

## 16. Cara Memverifikasi Struktur

Jalankan skrip berikut untuk memvalidasi bahwa struktur sesuai standar:

```bash
# Cek modul wajib ada
php artisan module:list

# Cek apakah setiap modul punya 4 layer wajib
for module in Modules/*/; do
    for layer in Domain Application Infrastructure Http; do
        [ -d "${module}app/${layer}" ] || echo "❌ ${module} kehilangan layer ${layer}"
    done
done

# Cek file wajib di root
for file in AGENTS.md Jenkinsfile README.md .gitignore .editorconfig; do
    [ -f "$file" ] || echo "❌ File wajib hilang: $file"
done

# Cek folder wajib di docs/
for folder in architecture api database modules security deployment operations development; do
    [ -d "docs/$folder" ] || echo "❌ docs/$folder hilang"
done
```

---

**Versi:** 1.0  
**Terakhir diperbarui:** 2026-01-01  
**Pemilik:** Tech Lead  
**Lokasi simpan:** `docs/architecture/file-structure.md`

> Dokumen ini adalah **peta navigasi tunggal** proyek. Bila terjadi perubahan struktur folder, update dokumen ini **dalam commit yang sama** dengan perubahan tersebut — bukan di commit terpisah.