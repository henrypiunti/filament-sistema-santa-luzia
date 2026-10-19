<?php

namespace App\Console\Commands;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

#[Signature('products:check-alerts {--days= : Dias de antecedência para o alerta de vencimento}')]
#[Description('Notifica administradores sobre produtos com estoque baixo e próximos do vencimento')]
class CheckProductAlerts extends Command
{
    /**
     * Maximum number of products listed by name in each notification.
     */
    private const int LISTED_PRODUCTS_LIMIT = 5;

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?? Product::EXPIRATION_WARNING_DAYS);

        $recipients = User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['admin', 'super-admin']))
            ->get();

        if ($recipients->isEmpty()) {
            $this->warn('Nenhum administrador encontrado para receber os alertas.');

            return self::SUCCESS;
        }

        $lowStockProducts = Product::query()->lowStock()->orderBy('stock')->get();
        $expiringProducts = Product::query()->expiringWithin($days)->orderBy('expires_at')->get();

        if ($lowStockProducts->isNotEmpty()) {
            $this->notify(
                $recipients,
                title: "{$lowStockProducts->count()} produto(s) com estoque baixo",
                body: $this->describe($lowStockProducts, fn (Product $product): string => "{$product->name} ({$product->stock}/{$product->minimum_stock})"),
                color: 'danger',
                icon: 'heroicon-o-archive-box-x-mark',
                filter: 'low_stock',
            );
        }

        if ($expiringProducts->isNotEmpty()) {
            $this->notify(
                $recipients,
                title: "{$expiringProducts->count()} produto(s) vencidos ou vencendo em até {$days} dias",
                body: $this->describe($expiringProducts, fn (Product $product): string => "{$product->name} ({$product->expires_at->format('d/m/Y')})"),
                color: 'warning',
                icon: 'heroicon-o-calendar-days',
                filter: 'expiring',
            );
        }

        $this->info("Estoque baixo: {$lowStockProducts->count()} | Vencimento: {$expiringProducts->count()} | Destinatários: {$recipients->count()}");

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, User>  $recipients
     */
    private function notify(Collection $recipients, string $title, string $body, string $color, string $icon, string $filter): void
    {
        Notification::make()
            ->title($title)
            ->body($body)
            ->icon($icon)
            ->color($color)
            ->actions([
                Action::make('view')
                    ->label('Ver produtos')
                    ->url(ProductResource::getUrl('index', ['filters' => [$filter => ['isActive' => true]]], panel: 'admin'))
                    ->markAsRead(),
            ])
            ->sendToDatabase($recipients);
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  callable(Product): string  $format
     */
    private function describe(Collection $products, callable $format): string
    {
        $description = $products->take(self::LISTED_PRODUCTS_LIMIT)->map($format)->implode(', ');

        if ($products->count() > self::LISTED_PRODUCTS_LIMIT) {
            $description .= ' e mais '.($products->count() - self::LISTED_PRODUCTS_LIMIT).'.';
        }

        return $description;
    }
}
