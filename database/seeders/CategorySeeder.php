<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Categories of a neighborhood pharmacy.
     *
     * @var list<string>
     */
    public const array NAMES = [
        'Genéricos',
        'Dor e Febre',
        'Gripe e Resfriado',
        'Alergias',
        'Saúde Digestiva',
        'Antibióticos',
        'Hipertensão e Diabetes',
        'Vitaminas e Suplementos',
        'Dermocosméticos',
        'Higiene e Cuidados Pessoais',
        'Mamãe e Bebê',
        'Primeiros Socorros',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::NAMES as $name) {
            Category::query()->firstOrCreate(['name' => $name]);
        }
    }
}
