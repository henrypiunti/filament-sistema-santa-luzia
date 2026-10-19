<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Fillable(['customer_id', 'total'])]
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * Delete the items through Eloquent so their stock is returned to the products,
     * since the database cascade would skip the model events.
     */
    protected static function booted(): void
    {
        static::deleting(function (Order $order): void {
            $order->orderProducts->each->delete();
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['quantity', 'price'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<OrderProduct, $this>
     */
    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    /**
     * Revenue of each of the last given days (oldest first), including days without sales.
     *
     * @return Collection<string, float> keyed by date (Y-m-d)
     */
    public static function dailyRevenue(int $days): Collection
    {
        $revenueByDay = static::query()
            ->where('created_at', '>=', today()->subDays($days - 1))
            ->get(['total', 'created_at'])
            ->groupBy(fn (Order $order): string => $order->created_at->toDateString())
            ->map(fn (Collection $orders): float => (float) $orders->sum('total'));

        return collect(range($days - 1, 0))
            ->mapWithKeys(fn (int $daysAgo): array => [
                today()->subDays($daysAgo)->toDateString() => $revenueByDay->get(today()->subDays($daysAgo)->toDateString(), 0.0),
            ]);
    }
}
