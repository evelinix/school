<?php

declare(strict_types=1);

namespace App\Health\Checks;

use Laravel\Horizon\Contracts\MasterSupervisorRepository;
use Spatie\Health\Checks\Checks\HorizonCheck as SpatieHorizonCheck;
use Spatie\Health\Checks\Result;

/**
 * Health check Horizon yang aman terhadap repository kosong.
 *
 * Versi Horizon terbaru mengembalikan `false` (bukan `array`) dari
 * `MasterSupervisorRepository::all()` ketika belum ada supervisor yang berjalan.
 * Check bawaan spatie crash saat `count(false)`, jadi di-handle eksplisit di sini.
 */
class HorizonCheck extends SpatieHorizonCheck
{
    public function run(): Result
    {
        try {
            $masterSupervisors = app(MasterSupervisorRepository::class)->all();
        } catch (\TypeError) {
            // Horizon repository mengembalikan `false` (bukan array) ketika
            // belum ada supervisor, lalu get() gagal di type hint `array $names`.
            return $this
                ->handleFailure('Horizon is not running.')
                ->shortSummary('Not running');
        } catch (\Throwable) {
            return Result::make()
                ->failed('Horizon does not seem to be installed correctly.');
        }

        if (count($masterSupervisors) === 0) {
            return $this
                ->handleFailure('Horizon is not running.')
                ->shortSummary('Not running');
        }

        $masterSupervisor = $masterSupervisors[0];

        if ($masterSupervisor->status === 'paused') {
            return Result::make()
                ->warning('Horizon is running, but the status is paused.')
                ->shortSummary('Paused');
        }

        return $this
            ->handleSuccess()
            ->shortSummary('Running');
    }
}
