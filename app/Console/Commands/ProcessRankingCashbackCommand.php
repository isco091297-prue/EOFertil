<?php

namespace App\Console\Commands;

use App\Models\CashbackCampaign;
use App\Services\Cashback\CalculateRankingCashbackService;
use Illuminate\Console\Command;
use Throwable;

class ProcessRankingCashbackCommand extends Command
{
    /**
     * Nombre del comando.
     */
    protected $signature =
    'cashback:process-ranking';

    /**
     * Descripción.
     */
    protected $description =
    'Procesa automáticamente los rankings de campañas Cashback finalizadas.';

    public function __construct(
        protected CalculateRankingCashbackService $service
    ) {
        parent::__construct();
    }

    /**
     * Ejecutar comando.
     */
    public function handle(): int
    {
        $campaigns = CashbackCampaign::query()

            ->where('campaign_type', 'cashback')

            ->where('activo', true)

            ->where('ranking_enabled', true)

            ->where('ranking_processed', false)

            ->whereDate(
                'fecha_fin',
                '<',
                now()->toDateString()
            )

            ->get();

        if ($campaigns->isEmpty()) {

            $this->info(
                'No existen campañas pendientes por procesar.'
            );

            return self::SUCCESS;
        }

        foreach ($campaigns as $campaign) {

            try {

                $this->info(
                    "Procesando campaña: {$campaign->nombre}"
                );

                $this->service->execute(
                    $campaign
                );

                $this->info(
                    "✔ Campaña {$campaign->nombre} procesada."
                );
            } catch (Throwable $e) {

                $this->error(
                    "✘ Error en {$campaign->nombre}: {$e->getMessage()}"
                );
            }
        }

        $this->newLine();

        $this->info(
            'Proceso finalizado correctamente.'
        );

        return self::SUCCESS;
    }
}
