<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ComprasPorClienteChart;
use App\Filament\Widgets\CurvaAbcChart;
use App\Filament\Widgets\EvolucaoDeVendasChart;
use App\Filament\Widgets\ParticipacaoDosClientesChart;
use App\Filament\Widgets\SalesStatsOverview;
use App\Filament\Widgets\VendasPorProdutoChart;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Dashboard';
    public function getWidgets(): array
    {
        return [
            SalesStatsOverview::class,
            EvolucaoDeVendasChart::class,
            VendasPorProdutoChart::class,
            ComprasPorClienteChart::class,
            ParticipacaoDosClientesChart::class,
            CurvaAbcChart::class,
        ];
    }

    public function getColumns(): int | array
    {
        return 2;
    }
}