<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class VendasPorProdutoChart extends ChartWidget
{
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 1;

    protected ?string $heading = 'Vendas por Produto';

    protected function getData(): array
    {
        $salesData = Sale::query()
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->select(
                'products.name as product_name',
                DB::raw('SUM(sales.quantity * products.unit_price) as total_revenue')
            )
            ->groupBy('products.name')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        $labels = $salesData->map(fn ($item) => $item->product_name);
        $data = $salesData->map(fn ($item) => $item->total_revenue);

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento',
                    'data' => $data,
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)',
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                    ],
                    'borderColor' => [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                    ],
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}