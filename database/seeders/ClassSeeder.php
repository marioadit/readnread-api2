<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Classes;
use App\Models\User;

class ClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari user dengan role guru, jika tidak ada maka default ke user ID 1
        $teacher = User::where('role', 'guru')->first();
        $teacherId = $teacher ? $teacher->id : 1;

        $sections = ['A', 'B'];

        for ($i = 1; $i <= 6; $i++) {
            foreach ($sections as $section) {
                Classes::updateOrCreate(
                    [
                        'name' => "Kelas {$i} {$section}",
                        'academic_year' => '2026/2027',
                    ],
                    [
                        'level' => "kelas {$i}",
                        'teacher_id' => $teacherId,
                        'semester' => 'genap',
                    ]
                );
            }
        }
    }
}
