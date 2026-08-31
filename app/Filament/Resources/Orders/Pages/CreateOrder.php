<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\OrderProduct;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['total'] = 0;

        return $data;
    }

    protected function afterCreate(): void
    {
        $order = $this->getRecord();

        $order->update([
            'total' => $order->orderProducts()->get()
                ->sum(fn (OrderProduct $item): float => $item->quantity * $item->price),
        ]);
    }
}
