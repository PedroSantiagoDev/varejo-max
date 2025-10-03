<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class CurvaAbcChart extends ChartWidget
{
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 1;

    protected ?string $heading = 'Curva ABC de Produtos';

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
            ->get();

        $labels = $salesData->map(fn ($item) => $item->product_name);
        $data = $salesData->map(fn ($item) => $item->total_revenue);

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento',
                    'data' => $data,
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'borderColor' => 'rgba(75, 192, 192, 1)',
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

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}