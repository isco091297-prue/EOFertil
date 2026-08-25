<?php

namespace App\Console\Commands;

use App\Models\CashbackCampaign;
use App\Services\Ranking\CalculateAccumulatedRankingService;
use Illuminate\Console\Command;
use Throwable;

class ProcessRankingAccumulatedCommand extends Command
{
    /**
     * Nombre del comando.
     */
    protected $signature = 'ranking:process-accumulated';

    /**
     * Descripción.
     */
    protected $description =
    'Procesa automáticamente los rankings acumulados de campañas finalizadas.';

    public function __construct(
        protected CalculateAccumulatedRankingService $service
    ) {
        parent::__construct();
    }

    /**
     * Ejecutar comando.
     */
    public function handle(): int
    {
        $campaigns = CashbackCampaign::query()

            ->where(
                'campaign_type',
                'ranking_accumulated'
            )

            ->where(
                'activo',
                true
            )

            ->where(
                'ranking_processed',
                false
            )

            ->whereDate(
                'fecha_fin',
                '<',
                now()->toDateString()
            )

            ->get();

        if ($campaigns->isEmpty()) {

            $this->info(
                'No existen campañas de ranking acumulado pendientes por procesar.'
            );

            return self::SUCCESS;
        }

        foreach ($campaigns as $campaign) {

            try {

                $this->info(
                    "Procesando campaña acumulada: {$campaign->nombre}"
                );

                $this->service->execute(
                    $campaign
                );

                $this->info(
                    "✔ Campaña {$campaign->nombre} procesada correctamente."
                );
            } catch (Throwable $e) {

                $this->error(
                    "✘ Error en {$campaign->nombre}: {$e->getMessage()}"
                );
            }
        }

        $this->newLine();

        $this->info(
            'Proceso de rankings acumulados finalizado.'
        );

        return self::SUCCESS;
    }
}
