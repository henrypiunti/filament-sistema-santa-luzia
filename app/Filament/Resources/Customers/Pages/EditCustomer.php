<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\Actions\AnonymizeCustomerAction;
use App\Filament\Resources\Customers\CustomerResource;
use App\Models\Customer;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            AnonymizeCustomerAction::make()
                ->after(fn () => $this->refreshFormData(array_keys($this->getRecord()->getAttributes()))),
            DeleteAction::make()
                ->hidden(fn (Customer $record): bool => $record->orders()->exists()),
        ];
    }

    /**
     * Anonymized customers are read-only so personal data cannot be reintroduced.
     */
    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->hidden(fn (): bool => $this->getRecord()->isAnonymized());
    }
}
