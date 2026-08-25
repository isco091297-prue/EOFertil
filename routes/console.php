<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Comandos Artisan
|--------------------------------------------------------------------------
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/*
|--------------------------------------------------------------------------
| Procesar Ranking Cashback
|--------------------------------------------------------------------------
|
| Mantiene el proceso que ya existe.
|
*/

Schedule::command(
    'cashback:process-ranking'
)->daily();


/*
|--------------------------------------------------------------------------
| Procesar Ranking Acumulado
|--------------------------------------------------------------------------
|
| Busca campañas acumuladas que ya terminaron y determina
| automáticamente el ganador.
|
*/

Schedule::command(
    'ranking:process-accumulated'
)->daily();
