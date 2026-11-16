<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds a realistic demo store. It does not rely on Faker, so it also runs in the production
 * image (installed without dev dependencies). Model events stay enabled on purpose: the
 * order items must go through the observer that keeps the stock in sync.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@santaluzia.test'],
            ['name' => 'Administrador', 'password' => 'password'],
        );

        $admin->roles()->syncWithoutDetaching(
            Role::query()->where('name', 'super-admin')->pluck('id'),
        );

        $this->call([
            CategorySeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            OrderSeeder::class,
        ]);
    }
}
