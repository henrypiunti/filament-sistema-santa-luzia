<?php

namespace App\Console\Commands;

use Filament\Actions\Exports\Models\Export;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('exports:prune {--days=7 : Dias que as planilhas exportadas ficam disponíveis}')]
#[Description('Remove planilhas exportadas antigas, que podem conter dados pessoais (LGPD)')]
class PruneOldExports extends Command
{
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $prunedCount = 0;

        Export::query()
            ->where('created_at', '<', now()->subDays($days))
            ->each(function (Export $export) use (&$prunedCount): void {
                $export->deleteFileDirectory();
                $export->delete();

                $prunedCount++;
            });

        $this->info("{$prunedCount} exportação(ões) removida(s).");

        return self::SUCCESS;
    }
}
