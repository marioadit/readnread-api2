<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // User::create([
        //     'name' => 'Admin User 2',
        //     'email' => 'admin2@example.com',
        //     'password' => Hash::make('password'),
        //     'role' => 'admin',
        // ]);

        User::create([
            'name' => 'Sumanto',
            'email' => 'guru@example.com',
            'password' => Hash::make('password'),
            'role' => 'guru',
        ]);

        User::create([
            'name' => 'Budi galaksi',
            'email' => 'siswa@example.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
        ]);

        User::create([
            'name' => 'Teknik admin',
            'email' => 'teknikadmin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Teknik siswa',
            'email' => 'tekniksiswa@example.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
        ]);

        // // Create 10 additional siswa users
        // for ($i = 1; $i <= 10; $i++) {
        //     User::create([
        //         'name' => 'Siswa SD ' . $i,
        //         'email' => 'siswa' . $i . '@example.com',
        //         'password' => Hash::make('password'),
        //         'role' => 'siswa',
        //     ]);
        // }
    }
}
