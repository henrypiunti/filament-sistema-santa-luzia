<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\OrderProduct;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $order = $this->getRecord();

        $order->update([
            'total' => $order->orderProducts()->get()
                ->sum(fn (OrderProduct $item): float => $item->quantity * $item->price),
        ]);
    }
}
