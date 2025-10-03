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
use Exception;

class ImportSalesFile implements ShouldQueue
{
    use Queueable;

    public int $importedRows = 0;
    public int $timeout = 300; // 5 minutos
    public int $tries = 1;

    public function __construct(
        public string $filePath,
        public Authenticatable $user
    ) {
        Log::info("ImportSalesFile JOB CRIADO", [
            'file' => $filePath,
            'user_id' => $user->id
        ]);
    }

    public function handle(): void
    {
        Log::info("========== INICIANDO IMPORTAÇÃO ==========");
        Log::info("Arquivo: {$this->filePath}");
        Log::info("Usuário: {$this->user->email}");

        try {
            // Verifica se o arquivo existe
            if (!Storage::disk('local')->exists($this->filePath)) {
                throw new Exception("Arquivo não encontrado: {$this->filePath}");
            }

            $fileContent = Storage::disk('local')->get($this->filePath);
            Log::info("Arquivo lido com sucesso. Tamanho: " . strlen($fileContent) . " bytes");

            // Corrige encoding
            if (!mb_check_encoding($fileContent, 'UTF-8')) {
                $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'ISO-8859-1');
                Log::info("Encoding convertido para UTF-8");
            }

            $lines = preg_split("/\R/", $fileContent);
            $totalLines = count($lines);
            Log::info("Total de linhas encontradas: {$totalLines}");

            DB::transaction(function () use ($lines) {
                foreach ($lines as $lineNumber => $line) {
                    if (empty(trim($line))) {
                        continue;
                    }

                    try {
                        $this->processLine($line, $lineNumber);
                    } catch (Exception $e) {
                        Log::error("ERRO na linha {$lineNumber}: " . $e->getMessage());
                        // Continua processando as outras linhas
                        continue;
                    }
                }
            });

            // Remove o arquivo
            Storage::disk('local')->delete($this->filePath);
            Log::info("Arquivo removido com sucesso");

            // Notifica o usuário
            Notification::make()
                ->title('Importação Concluída')
                ->body("{$this->importedRows} vendas foram importadas com sucesso.")
                ->success()
                ->sendToDatabase($this->user);

            Log::info("Notificação enviada ao usuário");

            // Dispara evento de broadcasting
            SalesImported::dispatch();
            Log::info("Evento SalesImported disparado");

            Log::info("========== IMPORTAÇÃO FINALIZADA COM SUCESSO ==========");
        } catch (Exception $e) {
            Log::error("========== ERRO CRÍTICO NA IMPORTAÇÃO ==========");
            Log::error("Mensagem: " . $e->getMessage());
            Log::error("Arquivo: " . $e->getFile());
            Log::error("Linha: " . $e->getLine());
            Log::error("Stack trace: " . $e->getTraceAsString());

            // Notifica o usuário sobre o erro
            Notification::make()
                ->title('Erro na Importação')
                ->body('Ocorreu um erro ao processar o arquivo. Verifique os logs.')
                ->danger()
                ->sendToDatabase($this->user);

            throw $e;
        }
    }

    private function processLine(string $line, int $lineNumber): void
    {
        Log::info("Processando linha {$lineNumber}: " . substr($line, 0, 50) . "...");

        $pattern = '/^(\d{4})(.+?)(\d{4})(.+?)(\d{3})(\d+\.\d{2})(\d{4}-\d{2}-\d{2})$/';

        if (!preg_match($pattern, $line, $matches)) {
            throw new Exception("Formato inválido na linha");
        }

        $productId = (int) $matches[1];
        $productName = trim($matches[2]);
        $clientId = (int) $matches[3];
        $clientName = trim($matches[4]);
        $quantitySold = (int) $matches[5];
        $unitPrice = (float) $matches[6];
        $saleDate = $matches[7];

        // Validações
        $this->validateData($productId, $productName, $clientId, $clientName, $quantitySold, $unitPrice, $saleDate);

        // Cria ou atualiza produto
        $product = Product::updateOrCreate(
            ['id' => $productId],
            ['name' => $productName, 'unit_price' => $unitPrice]
        );

        // Cria ou atualiza cliente
        $client = Client::updateOrCreate(
            ['id' => $clientId],
            ['name' => $clientName]
        );

        // Cria a venda
        Sale::create([
            'product_id' => $product->id,
            'client_id' => $client->id,
            'quantity' => $quantitySold,
            'sale_date' => $saleDate,
        ]);

        $this->importedRows++;
        Log::info("Linha {$lineNumber} importada com sucesso");
    }

    private function validateData($productId, $productName, $clientId, $clientName, $quantity, $price, $date): void
    {
        if ($productId === 0) {
            throw new Exception('ID do produto inválido: 0');
        }

        if (empty($productName)) {
            throw new Exception('Nome do produto vazio');
        }

        if ($clientId === 0) {
            throw new Exception('ID do cliente inválido: 0');
        }

        if (empty($clientName)) {
            throw new Exception('Nome do cliente vazio');
        }

        if ($quantity === 0) {
            throw new Exception('Quantidade inválida: 0');
        }

        if ($price <= 0) {
            throw new Exception("Preço inválido: {$price}");
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new Exception("Formato de data inválido: '{$date}'");
        }
    }

    public function failed(Exception $exception): void
    {
        Log::error("========== JOB FALHOU ==========");
        Log::error("Exceção: " . $exception->getMessage());

        Notification::make()
            ->title('Falha na Importação')
            ->body('O job de importação falhou. Erro: ' . $exception->getMessage())
            ->danger()
            ->sendToDatabase($this->user);
    }
}
