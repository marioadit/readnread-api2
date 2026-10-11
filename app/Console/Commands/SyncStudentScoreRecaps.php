<?php

namespace App\Console\Commands;

use App\Repository\Interfaces\IStudentScoreRecapRepository;
use Illuminate\Console\Command;

class SyncStudentScoreRecaps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recap:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sinkronisasi dan hitung ulang rekap nilai siswa dari tabel answers_history ke student_score_recaps';

    /**
     * Execute the console command.
     */
    public function handle(IStudentScoreRecapRepository $recapRepository)
    {
        $this->info('Memulai sinkronisasi rekap nilai siswa...');

        try {
            $totalProcessed = $recapRepository->recalculateAll();
            $this->info("Sinkronisasi berhasil! Sebanyak {$totalProcessed} kombinasi nilai siswa, kelas, dan semester telah diperbarui.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Gagal melakukan sinkronisasi: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
