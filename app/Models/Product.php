<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'description', 'price', 'stock', 'minimum_stock', 'expires_at'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    /**
     * How many days before the expiration date a product starts being flagged.
     */
    public const int EXPIRATION_WARNING_DAYS = 30;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'minimum_stock' => 'integer',
            'expires_at' => 'date',
        ];
    }

    /**
     * Products whose stock reached the configured minimum. Products without a minimum are ignored.
     *
     * @param  Builder<Product>  $query
     */
    #[Scope]
    protected function lowStock(Builder $query): void
    {
        $query->where('minimum_stock', '>', 0)
            ->whereColumn('stock', '<=', 'minimum_stock');
    }

    /**
     * Products already expired or expiring within the given number of days.
     *
     * @param  Builder<Product>  $query
     */
    #[Scope]
    protected function expiringWithin(Builder $query, int $days): void
    {
        $query->whereNotNull('expires_at')
            ->whereDate('expires_at', '<=', today()->addDays($days));
    }

    public function isLowStock(): bool
    {
        return $this->minimum_stock > 0 && $this->stock <= $this->minimum_stock;
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @return BelongsToMany<Order, $this>
     */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class)
            ->withPivot(['quantity', 'price'])
            ->withTimestamps();
    }
}
