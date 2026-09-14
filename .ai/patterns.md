# Pattern yang Direkomendasikan

## 1. Action Pattern (Single Use-Case)

Setiap use-case bisnis sebagai class invokable:

```php
<?php

declare(strict_types=1);

namespace Modules\Siswa\Application\Actions;

use Modules\Siswa\Application\DTO\StudentData;
use Modules\Siswa\Domain\Models\Student;

/**
 * Action untuk membuat siswa baru.
 */
final class CreateStudentAction
{
    public function execute(StudentData $data): Student
    {
        return Student::query()->create([
            'nis' => $data->nis,
            'full_name' => $data->fullName,
            'school_id' => $data->schoolId,
        ]);
    }
}
```

## 2. Service Pattern (Orkestrasi + Transaksi)

```php
public function create(StudentData $data): Student
{
    return DB::transaction(function () use ($data): Student {
        $student = $this->createAction->execute($data);
        event(new StudentCreated($student));

        return $student;
    });
}
```

## 3. Repository Pattern

Akses data lewat interface agar mudah di-mock & bertukar implementasi:

```php
interface StudentRepositoryInterface
{
    public function findById(string $id): ?Student;
    public function paginate(array $filters, int $perPage): LengthAwarePaginator;
    public function delete(Student $student): void;
}
```

Bind interface → implementasi Eloquent di ServiceProvider.

## 4. DTO dengan Spatie Data

```php
final class StudentData extends Data
{
    public function __construct(
        public readonly string $nis,
        public readonly string $fullName,
        public readonly string $gender,
        public readonly string $schoolId,
    ) {}
}
```

Konversi: `StudentData::from($request->validated())`.

## 5. Form Request untuk Validasi

```php
final class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('student.student.create');
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'nis' => ['required', 'string', 'max:20', 'unique:students,nis'],
            'full_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:L,P'],
            'birth_date' => ['required', 'date', 'before:today'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'nis.unique' => 'NIS sudah terdaftar.',
        ];
    }
}
```

## 6. API Resource

```php
final class StudentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nis' => $this->nis,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
        ];
    }
}
```

## 7. Domain Event + Listener

```php
// Dispatcher (dalam Service/Action)
event(new StudentCreated($student));

// Listener
final class SendWelcomeNotification implements ShouldQueue
{
    public function handle(StudentCreated $event): void
    {
        // ...
    }
}
```

## 8. Exception Khusus Domain

```php
final class StudentNotFoundException extends DomainException
{
    public function __construct(string $id)
    {
        parent::__construct("Siswa dengan ID [{$id}] tidak ditemukan.");
    }
}
```

## 9. Laravel 13 Model Attributes

```php
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    // ...
}
```

## 10. Route → Inertia (Web)

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('students', 'students/index')->name('students.index');
});
```

Setelah ubah route jalankan `php artisan wayfinder:generate` agar `@/routes` & `@/actions` di frontend ikut ter-update.