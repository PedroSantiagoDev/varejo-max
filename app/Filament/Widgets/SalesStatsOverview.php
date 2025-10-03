<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Sale;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class SalesStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Faturamento Total
        $totalRevenue = Sale::query()
            ->join('products', 'products.id', '=', 'sales.product_id')
            ->select(DB::raw('SUM(sales.quantity * products.unit_price) as total_revenue'))
            ->value('total_revenue') ?? 0;

        // Qtd. Total Vendida
        $totalQuantitySold = Sale::sum('quantity');

        // Produto Mais Vendido
        $topSellingProductInfo = Sale::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->first();

        $topSellingProductName = 'N/A';
        if ($topSellingProductInfo) {
            $product = Product::find($topSellingProductInfo->product_id);
            if ($product) {
                $topSellingProductName = $product->name;
            }
        }

        return [
            Stat::make('Faturamento Total', 'R$ ' . number_format($totalRevenue, 2, ',', '.'))
                ->description('Faturamento total de todas as vendas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('Qtd. Total Vendida', $totalQuantitySold)
                ->description('Quantidade total de itens vendidos')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
            Stat::make('Produto Mais Vendido', $topSellingProductName)
                ->description('Produto com a maior quantidade de vendas')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('warning'),
        ];
    }
}
