<?php

namespace App\Jobs;

use App\Events\SalesImported;
use App\Models\Client;
use App\Models\Product;
use App\Models\Sale;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImportSalesFile implements ShouldQueue
{
    use Queueable;

    public int $importedRows = 0;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $filePath, public Authenticatable $user)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fileContent = Storage::disk('local')->get($this->filePath);

        // Corrige problemas de encoding se necessário
        if (! mb_check_encoding($fileContent, 'UTF-8')) {
            $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'ISO-8859-1');
        }

        $lines = preg_split("/\R/", $fileContent);

        DB::transaction(function () use ($lines) {
            foreach ($lines as $lineNumber => $line) {
                if (empty(trim($line))) {
                    continue;
                }

                try {
                    $lineLength = strlen($line);
                    Log::info('========================================');
                    Log::info("Processing line {$lineNumber}");
                    Log::info("Line length: {$lineLength}");
                    Log::info("Full line: [{$line}]");

                    // Vamos mostrar a linha em pedaços para análise
                    Log::info('Line parts:');
                    Log::info('  [0-10]:   ['.substr($line, 0, 10).']');
                    Log::info('  [50-60]:  ['.substr($line, 50, 10).']');
                    Log::info('  [100-110]: ['.substr($line, 100, 10).']');
                    Log::info('  [110-120]: ['.substr($line, 110, 10).']');
                    Log::info('  [120-130]: ['.substr($line, 120, 10).']');
                    if ($lineLength > 130) {
                        Log::info('  [130-140]: ['.substr($line, 130, 10).']');
                    }

                    // Método 1: Regex mais robusto
                    // Ajustado para ser mais flexível com os espaços
                    $pattern = '/^(\d{4})(.+?)(\d{4})(.+?)(\d{3})(\d{4}\.\d{2}|\d{7}\.\d{2})(\d{4}-\d{2}-\d{2})$/';

                    Log::info('Trying regex pattern...');

                    if (preg_match($pattern, $line, $matches)) {
                        Log::info('✓ Regex matched!');
                        Log::info('Matches: '.json_encode($matches));

                        $productId = (int) $matches[1];
                        $productName = trim($matches[2]);
                        $clientId = (int) $matches[3];
                        $clientName = trim($matches[4]);
                        $quantitySold = (int) $matches[5];
                        $unitPriceStr = $matches[6];
                        $saleDate = $matches[7];

                        $unitPrice = (float) $unitPriceStr;
                    } else {
                        Log::warning('✗ Regex did NOT match, trying manual extraction');

                        // Vamos tentar uma abordagem diferente: buscar os padrões manualmente

                        // 1. Extrai ID do produto (primeiros 4 caracteres)
                        $productId = (int) substr($line, 0, 4);
                        Log::info("Product ID: {$productId}");

                        // 2. Busca o próximo ID de cliente (próximos 4 dígitos após o nome do produto)
                        // Procura por 4 dígitos consecutivos após a posição 4
                        if (preg_match('/^.{4}(.+?)(\d{4})/', $line, $match1)) {
                            $productName = trim($match1[1]);
                            $clientId = (int) $match1[2];
                            $posAfterClientId = strpos($line, $match1[2]) + 4;

                            Log::info("Product Name: [{$productName}]");
                            Log::info("Client ID: {$clientId}");
                            Log::info("Position after client ID: {$posAfterClientId}");

                            // 3. A partir da posição do client ID, busca o nome do cliente
                            $remaining = substr($line, $posAfterClientId);
                            Log::info("Remaining string: [{$remaining}]");

                            // Busca por 3 dígitos (quantidade) + número decimal + data
                            if (preg_match('/^(.+?)(\d{3})(\d+\.\d{2})(\d{4}-\d{2}-\d{2})$/', $remaining, $match2)) {
                                $clientName = trim($match2[1]);
                                $quantitySold = (int) $match2[2];
                                $unitPriceStr = $match2[3];
                                $saleDate = $match2[4];

                                Log::info("Client Name: [{$clientName}]");
                                Log::info("Quantity: {$quantitySold}");
                                Log::info("Unit Price String: [{$unitPriceStr}]");
                                Log::info("Sale Date: [{$saleDate}]");

                                $unitPrice = (float) $unitPriceStr;
                            } else {
                                Log::error('Failed to parse remaining fields');
                                Log::error("Remaining: [{$remaining}]");
                                throw new \Exception('Could not parse quantity, price and date from: '.$remaining);
                            }
                        } else {
                            throw new \Exception('Could not find product name and client ID');
                        }
                    }

                    // Log dos valores finais
                    Log::info('Final parsed values:', [
                        'productId' => $productId,
                        'productName' => $productName,
                        'clientId' => $clientId,
                        'clientName' => $clientName,
                        'quantity' => $quantitySold,
                        'unitPrice' => $unitPrice,
                        'saleDate' => $saleDate,
                    ]);

                    // Validações
                    if ($productId === 0) {
                        throw new \Exception('Invalid product ID: 0');
                    }

                    if (empty($productName)) {
                        throw new \Exception('Product name is empty');
                    }

                    if ($clientId === 0) {
                        throw new \Exception('Invalid client ID: 0');
                    }

                    if (empty($clientName)) {
                        throw new \Exception('Client name is empty');
                    }

                    if ($quantitySold === 0) {
                        throw new \Exception('Invalid quantity: 0');
                    }

                    if ($unitPrice <= 0) {
                        throw new \Exception("Invalid unit price: {$unitPrice}");
                    }

                    if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $saleDate)) {
                        throw new \Exception("Invalid date format: '{$saleDate}'");
                    }

                    // RF-04: "Find or Create" logic
                    $product = Product::updateOrCreate(
                        ['id' => $productId],
                        ['name' => $productName, 'unit_price' => $unitPrice]
                    );

                    $client = Client::updateOrCreate(
                        ['id' => $clientId],
                        ['name' => $clientName]
                    );

                    // RF-05: Register the Sale
                    Sale::create([
                        'product_id' => $product->id,
                        'client_id' => $client->id,
                        'quantity' => $quantitySold,
                        'sale_date' => $saleDate,
                    ]);

                    $this->importedRows++;

                    Log::info("✓✓✓ Line {$lineNumber} imported successfully ✓✓✓");
                } catch (\Exception $e) {
                    Log::error("✗✗✗ Error on line {$lineNumber} ✗✗✗");
                    Log::error('Error message: '.$e->getMessage());
                    Log::error('Stack trace: '.$e->getTraceAsString());
                    throw $e;
                }
            }
        });

        // Clean up the uploaded file
        Storage::disk('local')->delete($this->filePath);

        // RF-06: Notify user on completion
        Notification::make()
            ->title('Importação Concluída')
            ->body("{$this->importedRows} vendas foram importadas com sucesso.")
            ->success()
            ->sendToDatabase($this->user);

        SalesImported::dispatch();
    }
}
