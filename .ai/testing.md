# Strategi Testing

> Framework: **Pest** (`pestphp/pest` ^5) di atas PHPUnit; paralel via paratest. Tes jalan di SQLite `:memory:` tanpa DB/service. Baca skill `testing-best-practices` di `.agents/skills/` sebelum menulis test.

## Piramida Test

```
        /\
       /E2E\        (Playwright) — sedikit, mahal
      /------\
     /Feature\      (Laravel Feature) — sedang
    /----------\
   /    Unit    \   (PHPUnit/Pest, vitest) — banyak, murah
  /--------------\
```

## Playwright (E2E — keputusan)

- Kerangka E2E resmi: **Playwright** (`@playwright/test`).
- **Belum diinstal** — jangan tulis test `*.spec.ts` sebelum `bun add -D @playwright/test` dan konfigurasi selesai disetujui.
- Lingkup: alur lintas halaman yang kritis (login/registrasi, 2FA, passkey, settings).
- Test unit/feature (Pest) tetap jadi lapisan utama; E2E untuk koreografi halaman, bukan pengganti Pest.
- Struktur yang disarankan: `tests/e2e/` (spek Playwright) terpisah dari `tests/Unit` & `tests/Feature`.
- **CI:** jangan gabung Playwright ke `bun run check` (yang sudah crash `DataCloneError` di headless) atau ke suite Pest. Saat masuk Jenkins: **stage Jenkins terpisah** + install browser binary/system deps di agent linux terlebih dahulu (`bunx playwright install --with-deps`).

## Struktur Test

```
tests/
├── Unit/           # Logic murni (bisa tanpa DB)
├── Feature/        # Request → response (pakai DB sqlite :memory:)
├── e2e/            # Playwright (E2E) — alur kritis; menyusul
└── (integration & architecture menyusul per modul)
```

Di dalam modul (target): `Modules/NamaModul/tests/{Unit,Feature}`.

## Aturan Test

1. Setiap method public Service wajib punya test.
2. Setiap endpoint wajib test happy path + minimal 1 error case.
3. Nama test menggunakan Bahasa Indonesia (`test_admin_dapat_melihat_daftar_siswa`).
4. Feature test pakai `RefreshDatabase`.
5. Faker: ikuti konvensi existing (`$this->faker` / `fake()`).
6. Jangan hapus test tanpa persetujuan.

## Perintah yang Benar (bukan `npm test`)

```bash
# Satu file / filter
vendor/bin/pest tests/Feature/Admin/ExampleTest.php
php artisan test --compact --filter=StudentServiceTest

# Semua
php artisan test --compact          # lokal (CI pakai --parallel)
php artisan test --compact --parallel
```

## Contoh Unit Test (Pest)

```php
it('dapat membuat siswa baru', function () {
    $service = app(StudentService::class);

    $student = $service->create(new StudentData(
        nis: '2024001',
        fullName: 'Ahmad Fauzi',
        gender: 'L',
        birthDate: '2010-01-15',
        schoolId: $school->id,
    ));

    $this->assertDatabaseHas('students', [
        'nis' => '2024001',
        'full_name' => 'Ahmad Fauzi',
    ]);
});

it('melempar exception saat siswa tidak ditemukan', function () {
    expect(fn () => app(StudentService::class)->findById('01ABCDEF'))
        ->toThrow(StudentNotFoundException::class);
});
```

## Contoh Feature Test Web (Inertia)

```php
it('admin melihat daftar siswa', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo('student.student.view');

    $this->actingAs($admin)
        ->get('/students')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('students/index'));
});

it('user tanpa permission ditolak', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/students')
        ->assertForbidden();
});
```

## Otorisasi & Permission

- Permission (spatie) diperiksa dengan `$user->can('...')`, bukan `hasRole`.
- Test otorisasi: `actingAs` user + `givePermissionTo(...)`.
- Note: `resources/js/hooks/use-permissions.ts` adalah browser Permissions API — tidak terkait spatie.

## Coverage Target

| Layer | Minimum |
|-------|---------|
| Service / Action | 90% |
| Controller | 80% |
| **Total** | **≥ 80%** (target, bukan gate CI saat ini)