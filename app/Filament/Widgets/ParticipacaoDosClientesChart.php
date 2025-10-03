<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ParticipacaoDosClientesChart extends ChartWidget
{
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 1;

    protected ?string $heading = 'Participação dos Clientes';

    protected function getData(): array
    {
        $salesData = Sale::query()
            ->join('clients', 'clients.id', '=', 'sales.client_id')
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->select(
                'clients.name as client_name',
                DB::raw('SUM(sales.quantity * products.unit_price) as total_revenue')
            )
            ->groupBy('clients.name')
            ->orderByDesc('total_revenue')
            ->get();

        $topClients = $salesData->take(5);
        $otherClientsRevenue = $salesData->skip(5)->sum('total_revenue');

        $labels = $topClients->map(fn ($item) => $item->client_name)->toArray();
        $data = $topClients->map(fn ($item) => $item->total_revenue)->toArray();

        if ($otherClientsRevenue > 0) {
            $labels[] = 'Outros';
            $data[] = $otherClientsRevenue;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(201, 203, 207, 0.7)'
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}