<?php

namespace App\Filament\Resources\Sales\Actions;

use App\Jobs\ImportSalesFile;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class ImportSalesAction
{
    public static function make(): Action
    {
        return Action::make('importSales')
            ->label('Importar Vendas')
            ->form([
                FileUpload::make('attachment')
                    ->label('Arquivo de Vendas (.dat)')
                    ->required(),
            ])
            ->action(function (array $data) {
                $temporaryPath = $data['attachment'];
                $permanentPath = 'imports/'.basename($temporaryPath);
                Storage::move($temporaryPath, $permanentPath);

                ImportSalesFile::dispatch($permanentPath, auth()->user());

                Notification::make()
                    ->title('Importação em Andamento')
                    ->body('O arquivo de vendas está sendo processado. Você será notificado ao final.')
                    ->success()
                    ->send();
            });
    }
}
