<?php

namespace App\Agents;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Agent Agentic AI untuk monitoring kesehatan database.
 *
 * Agent ini bersifat read-only: hanya menjalankan query SELECT/observasi
 * dan tidak mengubah data. dipakai oleh dashboard dan artisan db:health.
 */
class DatabaseHealthAgent
{
    /**
     * Jalankan analisis kesehatan database dan kembalikan laporan terstruktur.
     *
     * @return array<string, mixed>
     */
    public function analyze(): array
    {
        $startedAt = microtime(true);
        $status = 'healthy';
        $issues = [];
        $connectionOk = false;
        $version = null;

        try {
            $versionRow = DB::selectOne('SELECT VERSION() AS version');
            $version = $versionRow->version ?? null;
            $connectionOk = true;
        } catch (Throwable $e) {
            $status = 'down';
            $issues[] = 'Koneksi database gagal: '.$e->getMessage();

            return $this->report($startedAt, $status, false, $version, 0, 0, 0, [], false, $issues);
        }

        $latencyMs = round((microtime(true) - $startedAt) * 1000, 2);

        $usersTable = Schema::hasTable('users');
        $projectsTable = Schema::hasTable('projects');

        if (! $usersTable || ! $projectsTable) {
            $status = 'degraded';
            $issues[] = 'Tabel users/projects belum lengkap (jalankan php artisan migrate --seed).';
        }

        $userCount = $usersTable ? User::count() : 0;
        $projectCount = $projectsTable ? Project::count() : 0;

        // Integritas relasi: project tanpa user valid (seharusnya 0 berkat FK cascade).
        $orphaned = 0;
        if ($usersTable && $projectsTable) {
            $orphaned = Project::whereNotIn('user_id', User::select('id'))->count();
            if ($orphaned > 0) {
                $status = 'degraded';
                $issues[] = "Ditemukan {$orphaned} project yatim (user_id tidak valid).";
            }
        }

        // Distribusi tema_agent (aman: group by tanpa input pengguna).
        $byTheme = [];
        if ($projectsTable) {
            $byTheme = Project::select('tema_agent', DB::raw('COUNT(*) AS total'))
                ->groupBy('tema_agent')
                ->pluck('total', 'tema_agent')
                ->toArray();
        }

        $demoExists = $usersTable
            ? User::where('email', 'dosenpbkk@its.ac.id')->exists()
            : false;

        if (! $demoExists) {
            $issues[] = 'Akun demo dosenpbkk@its.ac.id belum ada (jalankan seeder).';
            if ($status === 'healthy') {
                $status = 'degraded';
            }
        }

        if ($userCount < 16 || $projectCount < 32) {
            $issues[] = "Data seeding belum lengkap: {$userCount} users / {$projectCount} projects (target 16 users, 32+ projects).";
            if ($status === 'healthy') {
                $status = 'degraded';
            }
        }

        if ($latencyMs > 1000) {
            $issues[] = "Latensi query tinggi ({$latencyMs} ms). Periksa beban MySQL.";
            if ($status === 'healthy') {
                $status = 'degraded';
            }
        }

        if (empty($issues)) {
            $issues[] = 'Semua pemeriksaan lolos.';
        }

        $report = $this->report(
            $startedAt, $status, $connectionOk, $version,
            $userCount, $projectCount, $orphaned, $byTheme,
            $demoExists, $issues
        );
        $report['latency_ms'] = $latencyMs;

        return $report;
    }

    /**
     * Susun struktur laporan kesehatan.
     *
     * @param  array<string, int>  $byTheme
     * @param  list<string>  $issues
     * @return array<string, mixed>
     */
    private function report(
        float $startedAt,
        string $status,
        bool $connectionOk,
        ?string $version,
        int $userCount,
        int $projectCount,
        int $orphaned,
        array $byTheme,
        bool $demoExists,
        array $issues
    ): array {
        return [
            'status' => $status,
            'connection' => $connectionOk ? 'ok' : 'failed',
            'driver' => DB::getDriverName(),
            'database' => DB::getDatabaseName(),
            'version' => $version,
            'latency_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            'tables' => [
                'users' => Schema::hasTable('users'),
                'projects' => Schema::hasTable('projects'),
            ],
            'counts' => [
                'users' => $userCount,
                'projects' => $projectCount,
                'orphaned_projects' => $orphaned,
            ],
            'projects_by_tema_agent' => $byTheme,
            'demo_account_present' => $demoExists,
            'checked_at' => now()->toDateTimeString(),
            'recommendations' => $issues,
        ];
    }
}
