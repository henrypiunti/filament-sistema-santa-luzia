<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    private const string PRESCRIPTION = 'Venda sob prescrição médica.';

    private const string ANTIBIOTIC = 'Venda sob prescrição médica, com retenção da receita.';

    /**
     * Catalog of a neighborhood pharmacy. A few items are intentionally low on stock
     * or close to expiration so the alerts and the dashboard have something to show.
     *
     * @var list<array{name: string, description: ?string, price: float, categories: list<string>, stock: int, minimum_stock: int, expires_in_days: ?int}>
     */
    public const array PRODUCTS = [
        // Dor e Febre
        ['name' => 'Dipirona Monoidratada 500 mg – 10 comprimidos', 'description' => 'Medicamento genérico. Analgésico e antitérmico.', 'price' => 6.49, 'categories' => ['Dor e Febre', 'Genéricos'], 'stock' => 220, 'minimum_stock' => 40, 'expires_in_days' => 540],
        ['name' => 'Dipirona Gotas 500 mg/ml – 20 ml', 'description' => 'Medicamento genérico.', 'price' => 9.90, 'categories' => ['Dor e Febre', 'Genéricos'], 'stock' => 120, 'minimum_stock' => 25, 'expires_in_days' => 480],
        ['name' => 'Paracetamol 750 mg – 20 comprimidos', 'description' => 'Medicamento genérico. Analgésico e antitérmico.', 'price' => 11.90, 'categories' => ['Dor e Febre', 'Genéricos'], 'stock' => 180, 'minimum_stock' => 35, 'expires_in_days' => 610],
        ['name' => 'Ibuprofeno 400 mg – 10 cápsulas', 'description' => 'Anti-inflamatório.', 'price' => 14.90, 'categories' => ['Dor e Febre'], 'stock' => 130, 'minimum_stock' => 25, 'expires_in_days' => 420],
        ['name' => 'Dorflex – 36 comprimidos', 'description' => 'Dipirona + orfenadrina + cafeína. Relaxante muscular e analgésico.', 'price' => 27.90, 'categories' => ['Dor e Febre'], 'stock' => 90, 'minimum_stock' => 20, 'expires_in_days' => 700],
        ['name' => 'Neosaldina – 30 drágeas', 'description' => 'Indicado para dores de cabeça.', 'price' => 32.90, 'categories' => ['Dor e Febre'], 'stock' => 70, 'minimum_stock' => 15, 'expires_in_days' => 650],
        ['name' => 'Ácido Acetilsalicílico 500 mg – 20 comprimidos', 'description' => 'Medicamento genérico.', 'price' => 8.90, 'categories' => ['Dor e Febre', 'Genéricos'], 'stock' => 6, 'minimum_stock' => 15, 'expires_in_days' => 390],

        // Gripe e Resfriado
        ['name' => 'Benegrip – 12 comprimidos', 'description' => 'Antigripal.', 'price' => 19.90, 'categories' => ['Gripe e Resfriado'], 'stock' => 85, 'minimum_stock' => 20, 'expires_in_days' => 460],
        ['name' => 'Xarope Expectorante Guaco 120 ml', 'description' => 'Fitoterápico.', 'price' => 16.50, 'categories' => ['Gripe e Resfriado'], 'stock' => 60, 'minimum_stock' => 12, 'expires_in_days' => 22],
        ['name' => 'Soro Fisiológico 0,9% Spray Nasal 100 ml', 'description' => null, 'price' => 12.90, 'categories' => ['Gripe e Resfriado', 'Mamãe e Bebê'], 'stock' => 95, 'minimum_stock' => 20, 'expires_in_days' => 520],
        ['name' => 'Pastilhas para Garganta Mel e Limão – 12 unidades', 'description' => null, 'price' => 13.90, 'categories' => ['Gripe e Resfriado'], 'stock' => 75, 'minimum_stock' => 15, 'expires_in_days' => 300],
        ['name' => 'Acetilcisteína 600 mg – 16 envelopes', 'description' => 'Medicamento genérico. Mucolítico.', 'price' => 29.90, 'categories' => ['Gripe e Resfriado', 'Genéricos'], 'stock' => 45, 'minimum_stock' => 10, 'expires_in_days' => 360],

        // Alergias
        ['name' => 'Loratadina 10 mg – 12 comprimidos', 'description' => 'Medicamento genérico. Antialérgico.', 'price' => 10.90, 'categories' => ['Alergias', 'Genéricos'], 'stock' => 110, 'minimum_stock' => 20, 'expires_in_days' => 580],
        ['name' => 'Desloratadina 5 mg – 10 comprimidos', 'description' => 'Medicamento genérico. Antialérgico.', 'price' => 18.90, 'categories' => ['Alergias', 'Genéricos'], 'stock' => 55, 'minimum_stock' => 12, 'expires_in_days' => 500],
        ['name' => 'Dexclorfeniramina Xarope 0,4 mg/ml – 120 ml', 'description' => self::PRESCRIPTION, 'price' => 15.90, 'categories' => ['Alergias'], 'stock' => 40, 'minimum_stock' => 10, 'expires_in_days' => 330],

        // Saúde Digestiva
        ['name' => 'Omeprazol 20 mg – 28 cápsulas', 'description' => 'Medicamento genérico.', 'price' => 19.90, 'categories' => ['Saúde Digestiva', 'Genéricos'], 'stock' => 140, 'minimum_stock' => 30, 'expires_in_days' => 600],
        ['name' => 'Buscopan Composto – 20 comprimidos', 'description' => 'Escopolamina + dipirona. Cólicas e dores abdominais.', 'price' => 24.90, 'categories' => ['Saúde Digestiva', 'Dor e Febre'], 'stock' => 65, 'minimum_stock' => 15, 'expires_in_days' => 15],
        ['name' => 'Sal de Fruta – 2 envelopes de 5 g', 'description' => 'Antiácido efervescente.', 'price' => 4.50, 'categories' => ['Saúde Digestiva'], 'stock' => 160, 'minimum_stock' => 30, 'expires_in_days' => 450],
        ['name' => 'Simeticona Gotas 75 mg/ml – 15 ml', 'description' => 'Medicamento genérico. Gases.', 'price' => 9.90, 'categories' => ['Saúde Digestiva', 'Genéricos', 'Mamãe e Bebê'], 'stock' => 80, 'minimum_stock' => 15, 'expires_in_days' => 400],
        ['name' => 'Soro de Reidratação Oral – 4 envelopes', 'description' => null, 'price' => 11.50, 'categories' => ['Saúde Digestiva'], 'stock' => 50, 'minimum_stock' => 10, 'expires_in_days' => -4],
        ['name' => 'Probiótico Floratil 200 mg – 6 cápsulas', 'description' => null, 'price' => 34.90, 'categories' => ['Saúde Digestiva'], 'stock' => 35, 'minimum_stock' => 8, 'expires_in_days' => 270],

        // Antibióticos
        ['name' => 'Amoxicilina 500 mg – 21 cápsulas', 'description' => self::ANTIBIOTIC, 'price' => 22.90, 'categories' => ['Antibióticos', 'Genéricos'], 'stock' => 60, 'minimum_stock' => 12, 'expires_in_days' => 420],
        ['name' => 'Azitromicina 500 mg – 3 comprimidos', 'description' => self::ANTIBIOTIC, 'price' => 24.90, 'categories' => ['Antibióticos', 'Genéricos'], 'stock' => 45, 'minimum_stock' => 10, 'expires_in_days' => 380],
        ['name' => 'Cefalexina 500 mg – 8 cápsulas', 'description' => self::ANTIBIOTIC, 'price' => 26.90, 'categories' => ['Antibióticos', 'Genéricos'], 'stock' => 4, 'minimum_stock' => 8, 'expires_in_days' => 350],

        // Hipertensão e Diabetes
        ['name' => 'Losartana Potássica 50 mg – 30 comprimidos', 'description' => self::PRESCRIPTION.' Disponível no programa Farmácia Popular.', 'price' => 14.90, 'categories' => ['Hipertensão e Diabetes', 'Genéricos'], 'stock' => 200, 'minimum_stock' => 40, 'expires_in_days' => 640],
        ['name' => 'Hidroclorotiazida 25 mg – 30 comprimidos', 'description' => self::PRESCRIPTION, 'price' => 7.90, 'categories' => ['Hipertensão e Diabetes', 'Genéricos'], 'stock' => 120, 'minimum_stock' => 25, 'expires_in_days' => 560],
        ['name' => 'Metformina 850 mg – 30 comprimidos', 'description' => self::PRESCRIPTION.' Disponível no programa Farmácia Popular.', 'price' => 11.90, 'categories' => ['Hipertensão e Diabetes', 'Genéricos'], 'stock' => 170, 'minimum_stock' => 35, 'expires_in_days' => 590],
        ['name' => 'Fitas para Glicemia – 50 unidades', 'description' => 'Compatíveis com o medidor da mesma marca.', 'price' => 79.90, 'categories' => ['Hipertensão e Diabetes'], 'stock' => 30, 'minimum_stock' => 6, 'expires_in_days' => 200],
        ['name' => 'Medidor de Pressão Digital de Pulso', 'description' => 'Garantia de 1 ano.', 'price' => 149.90, 'categories' => ['Hipertensão e Diabetes'], 'stock' => 12, 'minimum_stock' => 3, 'expires_in_days' => null],

        // Vitaminas e Suplementos
        ['name' => 'Vitamina C 1 g – 10 comprimidos efervescentes', 'description' => null, 'price' => 15.90, 'categories' => ['Vitaminas e Suplementos'], 'stock' => 100, 'minimum_stock' => 20, 'expires_in_days' => 330],
        ['name' => 'Vitamina D3 2.000 UI – 60 cápsulas', 'description' => null, 'price' => 39.90, 'categories' => ['Vitaminas e Suplementos'], 'stock' => 55, 'minimum_stock' => 12, 'expires_in_days' => 480],
        ['name' => 'Polivitamínico A-Z – 60 comprimidos', 'description' => null, 'price' => 49.90, 'categories' => ['Vitaminas e Suplementos'], 'stock' => 40, 'minimum_stock' => 8, 'expires_in_days' => 420],
        ['name' => 'Ômega 3 1.000 mg – 120 cápsulas', 'description' => null, 'price' => 59.90, 'categories' => ['Vitaminas e Suplementos'], 'stock' => 30, 'minimum_stock' => 6, 'expires_in_days' => 26],

        // Dermocosméticos
        ['name' => 'Protetor Solar Facial FPS 50 – 50 g', 'description' => 'Toque seco, sem cor.', 'price' => 69.90, 'categories' => ['Dermocosméticos'], 'stock' => 35, 'minimum_stock' => 8, 'expires_in_days' => 500],
        ['name' => 'Hidratante Corporal Pele Extrasseca – 400 ml', 'description' => null, 'price' => 44.90, 'categories' => ['Dermocosméticos'], 'stock' => 28, 'minimum_stock' => 6, 'expires_in_days' => 620],
        ['name' => 'Água Micelar – 200 ml', 'description' => null, 'price' => 39.90, 'categories' => ['Dermocosméticos'], 'stock' => 3, 'minimum_stock' => 6, 'expires_in_days' => 540],
        ['name' => 'Pomada para Assaduras – 45 g', 'description' => 'Óxido de zinco.', 'price' => 21.90, 'categories' => ['Dermocosméticos', 'Mamãe e Bebê'], 'stock' => 50, 'minimum_stock' => 10, 'expires_in_days' => 600],

        // Higiene e Cuidados Pessoais
        ['name' => 'Creme Dental com Flúor – 90 g', 'description' => null, 'price' => 6.90, 'categories' => ['Higiene e Cuidados Pessoais'], 'stock' => 140, 'minimum_stock' => 30, 'expires_in_days' => 700],
        ['name' => 'Escova Dental Macia', 'description' => null, 'price' => 9.90, 'categories' => ['Higiene e Cuidados Pessoais'], 'stock' => 90, 'minimum_stock' => 20, 'expires_in_days' => null],
        ['name' => 'Fio Dental – 50 m', 'description' => null, 'price' => 11.90, 'categories' => ['Higiene e Cuidados Pessoais'], 'stock' => 70, 'minimum_stock' => 15, 'expires_in_days' => null],
        ['name' => 'Desodorante Roll-on sem Perfume – 50 ml', 'description' => null, 'price' => 16.90, 'categories' => ['Higiene e Cuidados Pessoais'], 'stock' => 60, 'minimum_stock' => 12, 'expires_in_days' => 650],
        ['name' => 'Absorvente Noturno com Abas – 8 unidades', 'description' => null, 'price' => 12.90, 'categories' => ['Higiene e Cuidados Pessoais'], 'stock' => 80, 'minimum_stock' => 15, 'expires_in_days' => null],

        // Mamãe e Bebê
        ['name' => 'Fralda Infantil Tamanho M – 34 unidades', 'description' => null, 'price' => 54.90, 'categories' => ['Mamãe e Bebê'], 'stock' => 45, 'minimum_stock' => 10, 'expires_in_days' => null],
        ['name' => 'Lenços Umedecidos – 96 unidades', 'description' => 'Sem álcool e sem perfume.', 'price' => 17.90, 'categories' => ['Mamãe e Bebê'], 'stock' => 70, 'minimum_stock' => 15, 'expires_in_days' => 400],
        ['name' => 'Paracetamol Gotas Infantil 200 mg/ml – 15 ml', 'description' => 'Medicamento genérico. Siga a dose por peso indicada na bula.', 'price' => 8.90, 'categories' => ['Mamãe e Bebê', 'Dor e Febre', 'Genéricos'], 'stock' => 65, 'minimum_stock' => 15, 'expires_in_days' => 450],

        // Primeiros Socorros
        ['name' => 'Curativo Adesivo – 40 unidades', 'description' => null, 'price' => 12.90, 'categories' => ['Primeiros Socorros'], 'stock' => 100, 'minimum_stock' => 20, 'expires_in_days' => 900],
        ['name' => 'Álcool 70% Líquido – 1 L', 'description' => null, 'price' => 14.90, 'categories' => ['Primeiros Socorros', 'Higiene e Cuidados Pessoais'], 'stock' => 90, 'minimum_stock' => 20, 'expires_in_days' => 700],
        ['name' => 'Gaze Estéril 7,5 x 7,5 cm – 10 unidades', 'description' => null, 'price' => 5.90, 'categories' => ['Primeiros Socorros'], 'stock' => 110, 'minimum_stock' => 20, 'expires_in_days' => 1000],
        ['name' => 'Termômetro Digital', 'description' => null, 'price' => 24.90, 'categories' => ['Primeiros Socorros'], 'stock' => 25, 'minimum_stock' => 5, 'expires_in_days' => null],
        ['name' => 'Água Oxigenada 10 Volumes – 100 ml', 'description' => null, 'price' => 4.90, 'categories' => ['Primeiros Socorros'], 'stock' => 75, 'minimum_stock' => 15, 'expires_in_days' => 500],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIdsByName = Category::query()->pluck('id', 'name');

        foreach (self::PRODUCTS as $data) {
            $product = Product::query()->firstOrCreate(
                ['name' => $data['name']],
                [
                    'description' => $data['description'],
                    'price' => $data['price'],
                    'stock' => $data['stock'],
                    'minimum_stock' => $data['minimum_stock'],
                    'expires_at' => $data['expires_in_days'] === null ? null : today()->addDays($data['expires_in_days']),
                ],
            );

            $product->categories()->syncWithoutDetaching(
                $categoryIdsByName->only($data['categories'])->values()->all(),
            );
        }
    }
}
