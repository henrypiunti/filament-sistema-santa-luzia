<?php

namespace App\Filament\Resources\Customers\Actions;

use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class AnonymizeCustomerAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'anonymize';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Anonimizar dados')
            ->icon(Heroicon::OutlinedEyeSlash)
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading('Anonimizar dados do cliente')
            ->modalDescription('Nome, e-mail, celular e endereço serão substituídos por valores genéricos. Os pedidos são mantidos para fins fiscais. Esta ação não pode ser desfeita.')
            ->modalSubmitActionLabel('Anonimizar')
            ->hidden(fn (Customer $record): bool => $record->isAnonymized())
            ->action(function (Customer $record): void {
                $record->anonymize();

                $this->success();
            })
            ->successNotificationTitle('Dados do cliente anonimizados');
    }
}
