# Anti-Pattern — WAJIB DIHINDARI

## 1. Fat Controller

❌ **SALAH**

```php
public function store(Request $request)
{
    $validated = $request->validate([...]);
    $student = Student::create($validated);
    Mail::to($student->email)->send(new WelcomeMail($student));
    Log::info('Siswa dibuat', ['id' => $student->id]);
    return redirect()->route('students.index');
}
```

✅ **BENAR**

```php
public function store(StoreStudentRequest $request)
{
    $student = $this->service->create(StudentData::from($request->validated()));

    return redirect()->route('students.show', $student->id);
}
```

## 2. Magic Number / String

❌ `if ($student->status === 1) { ... }`
✅ pakai Enum: `if ($student->status === StudentStatus::Active) { ... }`

## 3. Query Tanpa Filter `school_id`

❌ `Student::query()->get();` — bocor lintas sekolah
✅ `Student::query()->where('school_id', $context->requireId())->get();`

## 4. Bypass Service Antar Modul

❌ Modul A langsung pakai model modul B (`Modules\Raport\Domain\Models\Grade::query()->...`).
✅ Dispatch domain event; modul lain mereaksinya via Listener.

## 5. Validasi di Controller

❌ `$request->validate([...])` di method controller.
✅ Buat `FormRequest` terpisah (`php artisan make:request`).

## 6. Hardcode Konfigurasi / Secret

❌ `$apiKey = 'sk_live_abc123';` di service
✅ `config('services.payment.key')` dengan nilai dari `.env`.

## 7. `env()` di Luar `config/`

❌ `env('API_TIMEOUT', 30)` di service
✅ definisikan di `config/`, baca dengan `config('...')`.

## 8. N+1 Query

❌
```php
foreach (Student::all() as $student) {
    echo $student->class->name; // N + 1 query
}
```
✅
```php
foreach (Student::with('class')->get() as $student) {
    echo $student->class->name;
}
```

## 9. Try-Catch Menelan Exception

❌ `catch (\Throwable $e) { /* diamkan */ }`
✅ tangkap exception spesifik, log dengan konteks, lalu `throw` atau tangani eksplisit.

## 10. Merubah Migrasi yang Sudah di-Commit

❌ Mengedit migrasi lama di `main`.
✅ Buat migrasi baru (`make:migration`); jangan merubah migrasi yang sudah di-merge.

## 11. Mencampur HTTP dan Query DB dalam Satu Class

❌ Controller/Action yang menangani request sekaligus menulis query langsung.
✅ Pisahkan: Controller → Service → Repository.

## 12. `dd()`/`dump()`/`console.log()` Tersisa

Kode committed **tidak boleh** mengandung debug dump. Gunakan `Log::info(...)` atau tester.