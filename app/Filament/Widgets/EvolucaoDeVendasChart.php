<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EvolucaoDeVendasChart extends ChartWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected ?string $heading = 'Evolução de Vendas';

    protected function getData(): array
    {
        $salesData = Sale::query()
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->select(
                DB::raw('SUM(sales.quantity * products.unit_price) as total_revenue'),
                DB::raw("TO_CHAR(sales.sale_date, 'YYYY-MM') as month")
            )
            ->where('sales.sale_date', '>=', Carbon::now()->subYear())
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->get();

        $labels = $salesData->map(fn ($item) => Carbon::createFromFormat('!Y-m', $item->month)->format('M Y'));
        $data = $salesData->map(fn ($item) => $item->total_revenue);

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento',
                    'data' => $data,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}