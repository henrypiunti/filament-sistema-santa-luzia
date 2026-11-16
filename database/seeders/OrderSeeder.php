<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\Product;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Orders spread over the last days so the dashboard charts have history. Items are created
 * through OrderProduct so the observer lowers the stock, and a product is only sold while
 * it stays above its minimum stock.
 */
class OrderSeeder extends Seeder
{
    public const int DAYS_OF_HISTORY = 60;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Order::query()->exists()) {
            return;
        }

        mt_srand(20261111);

        $customerIds = Customer::query()->whereNull('anonymized_at')->pluck('id')->all();

        if ($customerIds === []) {
            return;
        }

        DB::transaction(function () use ($customerIds): void {
            for ($daysAgo = self::DAYS_OF_HISTORY - 1; $daysAgo >= 0; $daysAgo--) {
                $day = today()->subDays($daysAgo);
                $ordersOnDay = $day->isSunday() ? mt_rand(2, 4) : mt_rand(3, 7);

                for ($i = 0; $i < $ordersOnDay; $i++) {
                    $placedAt = $day->copy()->setTime(mt_rand(7, 21), mt_rand(0, 59));

                    if ($placedAt->isFuture()) {
                        continue;
                    }

                    $this->createOrder($customerIds[mt_rand(0, count($customerIds) - 1)], $placedAt);
                }
            }
        });
    }

    private function createOrder(int $customerId, CarbonInterface $placedAt): void
    {
        $products = Product::query()
            ->whereRaw('stock > minimum_stock + 4')
            ->inRandomOrder()
            ->limit(mt_rand(1, 4))
            ->get();

        if ($products->isEmpty()) {
            return;
        }

        $order = new Order(['customer_id' => $customerId, 'total' => 0]);
        $order->created_at = $placedAt;
        $order->updated_at = $placedAt;
        $order->save();

        $total = 0.0;

        foreach ($products as $product) {
            $quantity = [1, 1, 1, 1, 1, 1, 2, 2, 2, 3][mt_rand(0, 9)];

            $item = new OrderProduct([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price,
            ]);
            $item->created_at = $placedAt;
            $item->updated_at = $placedAt;
            $item->save();

            $total += $quantity * (float) $product->price;
        }

        $order->total = round($total, 2);
        $order->timestamps = false;
        $order->save();
    }
}
