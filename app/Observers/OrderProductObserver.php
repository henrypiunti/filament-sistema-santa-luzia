<?php

namespace App\Observers;

use App\Models\OrderProduct;
use App\Models\Product;

/**
 * Keeps product stock in sync with the items of an order.
 */
class OrderProductObserver
{
    /**
     * Handle the OrderProduct "created" event.
     */
    public function created(OrderProduct $orderProduct): void
    {
        $this->decreaseStock($orderProduct->product_id, $orderProduct->quantity);
    }

    /**
     * Handle the OrderProduct "updated" event.
     */
    public function updated(OrderProduct $orderProduct): void
    {
        if (! $orderProduct->wasChanged(['product_id', 'quantity'])) {
            return;
        }

        $this->increaseStock($orderProduct->getOriginal('product_id'), $orderProduct->getOriginal('quantity'));
        $this->decreaseStock($orderProduct->product_id, $orderProduct->quantity);
    }

    /**
     * Handle the OrderProduct "deleted" event.
     */
    public function deleted(OrderProduct $orderProduct): void
    {
        $this->increaseStock($orderProduct->product_id, $orderProduct->quantity);
    }

    private function decreaseStock(int $productId, int $quantity): void
    {
        Product::query()->whereKey($productId)->decrement('stock', $quantity);
    }

    private function increaseStock(int $productId, int $quantity): void
    {
        Product::query()->whereKey($productId)->increment('stock', $quantity);
    }
}
