<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Classes;
use Illuminate\Support\Facades\Hash;

class AdditionalGuruSeeder extends Seeder
{
    /**
     * Menambahkan guru baru untuk mengampu kelas-kelas yang sebelumnya
     * diampuh Pak Sumanto (ID 2) secara bersamaan.
     *
     * Kondisi sebelum seed ini:
     *   - Sumanto  → Kelas 1B, 2A, 2B, 3A, 3B, 4A, 4B, 5B, 6A, 6B
     *
     * Setelah seed ini, setiap guru hanya mengampu 1 kelas.
     */
    public function run(): void
    {
        // Daftar guru baru beserta kelas yang akan diampunya
        // Format email: nama_tanpa_spasi@example.com (huruf kecil semua)
        $guruKelas = [
            [
                'name'     => 'Wahyudi',
                'email'    => 'wahyudi@example.com',
                'class_id' => 2,  // Kelas 1 B
            ],
            [
                'name'     => 'Slamet Riyadi',
                'email'    => 'slametriyadi@example.com',
                'class_id' => 3,  // Kelas 2 A
            ],
            [
                'name'     => 'Supriyanto',
                'email'    => 'supriyanto@example.com',
                'class_id' => 4,  // Kelas 2 B
            ],
            [
                'name'     => 'Bambang Sutrisno',
                'email'    => 'bambangsutrisno@example.com',
                'class_id' => 5,  // Kelas 3 A
            ],
            [
                'name'     => 'Triyono',
                'email'    => 'triyono@example.com',
                'class_id' => 6,  // Kelas 3 B
            ],
            [
                'name'     => 'Heru Santoso',
                'email'    => 'herusantoso@example.com',
                'class_id' => 7,  // Kelas 4 A
            ],
            [
                'name'     => 'Agus Widodo',
                'email'    => 'aguswidodo@example.com',
                'class_id' => 8,  // Kelas 4 B
            ],
            [
                'name'     => 'Darmanto',
                'email'    => 'darmanto@example.com',
                'class_id' => 10, // Kelas 5 B
            ],
            [
                'name'     => 'Widyastuti',
                'email'    => 'widyastuti@example.com',
                'class_id' => 11, // Kelas 6 A
            ],
            [
                'name'     => 'Sulistyowati',
                'email'    => 'sulistyowati@example.com',
                'class_id' => 12, // Kelas 6 B
            ],
        ];

        foreach ($guruKelas as $data) {
            // Buat atau update user guru
            $guru = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'     => $data['name'],
                    'role'     => 'guru',
                    'password' => Hash::make('password'),
                ]
            );

            // Assign guru ke kelas yang ditentukan
            Classes::where('id', $data['class_id'])
                ->update(['teacher_id' => $guru->id]);
        }

        // Sumanto sekarang hanya mengampu sisa kelas yang belum diassign ulang.
        // Berdasarkan data di atas, Sumanto (ID 2) semula memegang kelas:
        // 1B(2), 2A(3), 2B(4), 3A(5), 3B(6), 4A(7), 4B(8), 5B(10), 6A(11), 6B(12)
        // Setelah seeder ini, semua sudah dipindah ke guru baru.
        // Jika ingin Sumanto tetap memegang 1 kelas sisa, silakan assign manual.

        $this->command->info('✅ Guru baru berhasil ditambahkan dan kelas berhasil diassign!');
        $this->command->table(
            ['Nama Guru', 'Email', 'ID Kelas'],
            collect($guruKelas)->map(fn($d) => [$d['name'], $d['email'], $d['class_id']])->toArray()
        );
    }
}

