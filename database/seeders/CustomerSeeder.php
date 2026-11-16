<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Fictional customers with realistic Brazilian names and addresses in Santa Luzia/MG and the
 * surrounding cities. Any resemblance to real people is a coincidence. The output is
 * deterministic, so running it again only fills in what is missing.
 */
class CustomerSeeder extends Seeder
{
    public const int TOTAL = 40;

    /**
     * @var list<string>
     */
    private const array FIRST_NAMES = [
        'Ana', 'Maria Eduarda', 'Juliana', 'Fernanda', 'Camila', 'Larissa', 'Beatriz', 'Patrícia',
        'Aline', 'Gabriela', 'Letícia', 'Vanessa', 'Rafaela', 'Sônia', 'Luciana', 'Tatiane',
        'Lucas', 'Gabriel', 'Mateus', 'Rafael', 'Pedro Henrique', 'João Victor', 'Felipe', 'Gustavo',
        'Thiago', 'Bruno', 'Rodrigo', 'Leonardo', 'Marcelo', 'Diego', 'Vinícius', 'Wellington',
        'Antônio', 'José Carlos', 'Geraldo', 'Sebastião', 'Cláudia', 'Rosângela', 'Kelly', 'Wesley',
    ];

    /**
     * @var list<string>
     */
    private const array LAST_NAMES = [
        'Silva', 'Santos', 'Oliveira', 'Souza', 'Rodrigues', 'Ferreira', 'Alves', 'Pereira',
        'Lima', 'Gomes', 'Costa', 'Ribeiro', 'Martins', 'Carvalho', 'Almeida', 'Lopes',
        'Soares', 'Fernandes', 'Vieira', 'Barbosa', 'Rocha', 'Dias', 'Nascimento', 'Andrade',
        'Moreira', 'Nunes', 'Marques', 'Machado', 'Mendes', 'Freitas', 'Cardoso', 'Teixeira',
    ];

    /**
     * @var list<string>
     */
    private const array EMAIL_DOMAINS = ['gmail.com', 'gmail.com', 'gmail.com', 'hotmail.com', 'outlook.com', 'yahoo.com.br', 'icloud.com'];

    /**
     * @var list<?string>
     */
    private const array COMPLEMENTS = [null, null, null, null, 'Casa', 'Casa 2', 'Fundos', 'Apto 101', 'Apto 203', 'Apto 304', 'Bloco B, Apto 12', 'Loja 1'];

    /**
     * Weighted toward Santa Luzia, where the store is.
     *
     * @var list<array{city: string, state: string, area_code: string, zip_prefix: string, neighborhoods: list<string>, streets: list<string>}>
     */
    private const array LOCATIONS = [
        [
            'city' => 'Santa Luzia', 'state' => 'MG', 'area_code' => '31', 'zip_prefix' => '330',
            'neighborhoods' => ['Centro', 'São Benedito', 'Frimisa', 'Bonanza', 'Palmital', 'Londrina', 'Cristina', 'Bom Destino'],
            'streets' => ['Rua Direita', 'Avenida Brasília', 'Rua Floriano Peixoto', 'Rua Sete de Setembro', 'Avenida das Indústrias', 'Rua São José', 'Rua Padre Lucas', 'Rua das Acácias'],
        ],
        [
            'city' => 'Santa Luzia', 'state' => 'MG', 'area_code' => '31', 'zip_prefix' => '331',
            'neighborhoods' => ['São Benedito', 'Palmital', 'Belo Vale', 'Duquesa', 'Liberdade'],
            'streets' => ['Rua Bahia', 'Rua Espírito Santo', 'Avenida Raul Teixeira da Costa Sobrinho', 'Rua dos Ipês', 'Rua Pernambuco'],
        ],
        [
            'city' => 'Belo Horizonte', 'state' => 'MG', 'area_code' => '31', 'zip_prefix' => '318',
            'neighborhoods' => ['Venda Nova', 'Planalto', 'Jaraguá', 'São Gabriel', 'Ribeiro de Abreu'],
            'streets' => ['Rua Padre Pedro Pinto', 'Avenida Cristiano Machado', 'Rua Itamarandiba', 'Rua Jacuí', 'Rua Conselheiro Lafaiete'],
        ],
        [
            'city' => 'Vespasiano', 'state' => 'MG', 'area_code' => '31', 'zip_prefix' => '332',
            'neighborhoods' => ['Centro', 'Morro Alto', 'Jardim Encantado', 'Nova York'],
            'streets' => ['Rua Rio de Janeiro', 'Avenida Prefeito Sebastião Fernandes', 'Rua Minas Gerais', 'Rua Goiás'],
        ],
        [
            'city' => 'Lagoa Santa', 'state' => 'MG', 'area_code' => '31', 'zip_prefix' => '334',
            'neighborhoods' => ['Centro', 'Lundcéia', 'Joá', 'Vila Asas'],
            'streets' => ['Rua Pinto Alves', 'Avenida Acadêmico Nilo Figueiredo', 'Rua Aleomar Baleeiro', 'Rua Dr. Lund'],
        ],
    ];

    /**
     * Indexes of LOCATIONS; repeated entries make a city more likely.
     *
     * @var list<int>
     */
    private const array LOCATION_WEIGHTS = [0, 0, 0, 0, 1, 1, 1, 2, 2, 3, 4];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        mt_srand(20261110);

        $usedNames = [];
        $created = 0;

        while ($created < self::TOTAL) {
            $firstName = $this->pick(self::FIRST_NAMES);
            $surnames = array_rand(array_flip(self::LAST_NAMES), 2);
            $lastName = implode(' ', mt_rand(0, 1) ? $surnames : array_reverse($surnames));

            if (isset($usedNames["{$firstName} {$lastName}"])) {
                continue;
            }

            $usedNames["{$firstName} {$lastName}"] = true;
            $location = self::LOCATIONS[$this->pick(self::LOCATION_WEIGHTS)];
            $registeredAt = now()->subDays(mt_rand(65, 400));

            $customer = Customer::query()->firstOrCreate(
                ['email' => $this->email($firstName, $lastName)],
                [
                    'name' => $firstName,
                    'last_name' => $lastName,
                    'cell_phone' => $location['area_code'].'9'.mt_rand(7, 9).str_pad((string) mt_rand(0, 9999999), 7, '0', STR_PAD_LEFT),
                    'zip_code' => $location['zip_prefix'].str_pad((string) mt_rand(0, 99999), 5, '0', STR_PAD_LEFT),
                    'street' => $this->pick($location['streets']),
                    'number' => (string) mt_rand(12, 2480),
                    'complement' => $this->pick(self::COMPLEMENTS),
                    'neighborhood' => $this->pick($location['neighborhoods']),
                    'city' => $location['city'],
                    'state' => $location['state'],
                ],
            );

            if ($customer->wasRecentlyCreated) {
                $customer->forceFill(['created_at' => $registeredAt])->save();
            }

            $created++;
        }
    }

    /**
     * @template TValue
     *
     * @param  list<TValue>  $items
     * @return TValue
     */
    private function pick(array $items): mixed
    {
        return $items[mt_rand(0, count($items) - 1)];
    }

    private function email(string $firstName, string $lastName): string
    {
        $first = Str::of($firstName)->ascii()->lower()->explode(' ')->first();
        $last = Str::of($lastName)->ascii()->lower()->explode(' ')->last();

        $localPart = match (mt_rand(0, 3)) {
            0 => "{$first}.{$last}",
            1 => "{$first}{$last}".mt_rand(70, 99),
            2 => "{$first}_{$last}",
            default => $first[0].$last.mt_rand(1, 999),
        };

        return $localPart.'@'.$this->pick(self::EMAIL_DOMAINS);
    }
}
