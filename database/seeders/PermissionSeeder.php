<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder permission & role kernel.
 *
 * Konvensi penamaan: `{domains}.{resource}.{aksi}` — resource domain dan aksi
 * memakai Bahasa Indonesia (contoh: `pengguna.pengguna.lihat`). Role di-assign
 * langsung ke user, otorisasi route memakai permission, bukan role.
 */
class PermissionSeeder extends Seeder
{
    private const SUPER_ADMIN_ROLE = 'super-admin';

    /**
     * Daftar permission kernel core.
     *
     * @var list<string>
     */
    public const KERNEL_PERMISSIONS = [
        'pengguna.pengguna.lihat',
        'pengguna.pengguna.tambah',
        'pengguna.pengguna.ubah',
        'pengguna.pengguna.hapus',
        'peran.peran.lihat',
        'peran.peran.tambah',
        'peran.peran.ubah',
        'peran.peran.hapus',
        'pengaturan.pengaturan.lihat',
        'pengaturan.pengaturan.ubah',
        'sekolah.sekolah.lihat',
        'sekolah.sekolah.tambah',
        'sekolah.sekolah.ubah',
        'sekolah.sekolah.hapus',
        'modul.modul.lihat',
        'modul.modul.pasang',
        'modul.modul.lepas',
        'modul.modul.aktifkan',
        'modul.modul.nonaktifkan',
        'audit.audit.lihat',
        'monitoring.monitoring.lihat',
    ];

    /**
     * Seed permission, role super-admin, dan assignment user evelin (idempotent).
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = collect(self::KERNEL_PERMISSIONS)->mapWithKeys(
            fn (string $name): array => [$name => Permission::findOrCreate($name, 'web')],
        );

        $superAdmin = Role::findOrCreate(self::SUPER_ADMIN_ROLE, 'web');
        $superAdmin->syncPermissions($permissions->values());

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        /** @var User|null $user */
        $user = User::query()->where('email', 'evelin@school.jmediatech.online')->first();

        if ($user !== null) {
            $user->assignRole($superAdmin);
        }
    }
}
