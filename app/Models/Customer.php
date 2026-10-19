<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'last_name',
    'email',
    'cell_phone',
    'zip_code',
    'street',
    'number',
    'complement',
    'neighborhood',
    'city',
    'state',
])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'anonymized_at' => 'datetime',
        ];
    }

    public function isAnonymized(): bool
    {
        return $this->anonymized_at !== null;
    }

    /**
     * Irreversibly replaces the personal data (LGPD art. 18) while keeping the orders,
     * which must be retained for fiscal purposes. City and state are kept for sales statistics.
     */
    public function anonymize(): void
    {
        $this->forceFill([
            'name' => 'Cliente',
            'last_name' => 'Anonimizado',
            'email' => "anonimizado-{$this->id}@anonimizado.invalid",
            'cell_phone' => '00000000000',
            'zip_code' => '00000000',
            'street' => 'Anonimizado',
            'number' => 'Anonimizado',
            'complement' => null,
            'neighborhood' => 'Anonimizado',
            'anonymized_at' => now(),
        ])->save();
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
