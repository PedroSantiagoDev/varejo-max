<?php

namespace App\Console\Commands;

use App\Models\Sale;
use App\Models\Client;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GenerateSalesFile extends Command
{
    protected $signature = 'app:generate-sales-file';
    protected $description = 'Generate a sample sales file with 100 records.';

    public function handle()
    {
        $this->info('Generating sample sales file with at least 100 records...');

        $salesCount = Sale::count();
        $recordsToCreate = 100 - $salesCount;

        if ($recordsToCreate > 0) {
            $this->info("Database has {$salesCount} sales. Creating {$recordsToCreate} more records using factories...");
            
            // Ensure there are products and clients to associate with
            if (Product::count() == 0) {
                Product::factory()->count(10)->create();
                $this->info('Created 10 sample products.');
            }
            if (Client::count() == 0) {
                Client::factory()->count(10)->create();
                $this->info('Created 10 sample clients.');
            }

            Sale::factory()->count($recordsToCreate)->create();
            $this->info("Successfully created {$recordsToCreate} new sale records.");
        }

        $sales = Sale::with(['product', 'client'])->take(100)->get();
        
        $fileContent = '';
        $counter = 0;

        foreach ($sales as $sale) {
            if (!$sale->product || !$sale->client) {
                $this->warn("Skipping sale ID {$sale->id} due to missing product or client relation.");
                continue;
            }

            $productId = str_pad(substr($sale->product->id, 0, 4), 4, '0', STR_PAD_LEFT);
            $productName = $sale->product->name;
            $clientId = str_pad(substr($sale->client->id, 0, 4), 4, '0', STR_PAD_LEFT);
            $clientName = $sale->client->name;
            $quantity = str_pad(substr($sale->quantity, 0, 3), 3, '0', STR_PAD_LEFT);
            $unitPrice = number_format($sale->product->unit_price, 2, '.', '');
            $saleDate = Carbon::parse($sale->sale_date)->subDays(rand(0, 365))->format('Y-m-d');

            $line = $productId . $productName . $clientId . $clientName . $quantity . $unitPrice . $saleDate;
            $fileContent .= $line . PHP_EOL;
            $counter++;
        }

        Storage::disk('local')->put('exemplo_vendas.dat', trim($fileContent));

        $this->info("Successfully generated 'exemplo_vendas.dat' with {$counter} records in storage/app/.");

        return 0;
    }
}