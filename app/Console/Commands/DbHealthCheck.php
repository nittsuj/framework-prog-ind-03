<?php

namespace App\Console\Commands;

use App\Agents\DatabaseHealthAgent;
use Illuminate\Console\Command;

/**
 * Perintah artisan untuk memeriksa kesehatan database.
 *
 * Penggunaan: php artisan db:health
 */
class DbHealthCheck extends Command
{
    /**
     * Nama dan signature perintah.
     *
     * @var string
     */
    protected $signature = 'db:health';

    /**
     * Deskripsi perintah.
     *
     * @var string
     */
    protected $description = 'Tampilkan laporan kesehatan database MySQL (Agentic AI monitor)';

    /**
     * Jalankan pemeriksaan kesehatan.
     */
    public function handle(DatabaseHealthAgent $agent): int
    {
        $report = $agent->analyze();

        $this->info('Database Health: '.strtoupper($report['status']));
        $this->table(
            ['Metric', 'Value'],
            [
                ['Connection', $report['connection']],
                ['Driver', $report['driver']],
                ['Database', $report['database']],
                ['Version', $report['version'] ?? '-'],
                ['Latency', $report['latency_ms'].' ms'],
                ['Users', $report['counts']['users']],
                ['Projects', $report['counts']['projects']],
                ['Orphaned', $report['counts']['orphaned_projects']],
                ['Demo account', $report['demo_account_present'] ? 'present' : 'missing'],
                ['Checked at', $report['checked_at']],
            ]
        );

        if (! empty($report['projects_by_tema_agent'])) {
            $this->info('Projects by tema_agent:');
            foreach ($report['projects_by_tema_agent'] as $theme => $total) {
                $this->line("  - {$theme}: {$total}");
            }
        }

        $this->info('Recommendations:');
        foreach ($report['recommendations'] as $item) {
            $this->line("  - {$item}");
        }

        return $report['status'] === 'down' ? self::FAILURE : self::SUCCESS;
    }
}
