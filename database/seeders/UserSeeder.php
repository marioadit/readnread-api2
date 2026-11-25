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
        $users = [
            [
                'email' => 'admin@example.com',
                'name'  => 'Admin User',
                'role'  => 'admin',
            ],
            [
                'email' => 'guru@example.com',
                'name'  => 'Sumanto',
                'role'  => 'guru',
            ],
            [
                'email' => 'siswa@example.com',
                'name'  => 'Budi galaksi',
                'role'  => 'siswa',
            ],
            [
                'email' => 'teknikadmin@example.com',
                'name'  => 'Teknik admin',
                'role'  => 'admin',
            ],
            [
                'email' => 'tekniksiswa@example.com',
                'name'  => 'Teknik siswa',
                'role'  => 'siswa',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],           // cari berdasarkan email (unik)
                [
                    'name'     => $userData['name'],
                    'role'     => $userData['role'],
                    'password' => Hash::make('password'),  // hanya diset saat CREATE, tidak overwrite saat UPDATE
                ]
            );
        }
    }
}
