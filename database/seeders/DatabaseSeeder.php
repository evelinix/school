<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'evelin@school.jmediatech.online'],
            [
                'name' => 'Eve Lin',
                'password' => 'password',
            ],
        );

        $this->call(PermissionSeeder::class);
    }
}
