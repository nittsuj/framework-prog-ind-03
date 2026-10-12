<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Menyiapkan database otomatis agar cukup jalankan `php artisan serve`.
 *
 * - Membuat database `academic_portal` bila belum ada.
 * - Menjalankan `migrate --seed` bila tabel users/projects belum lengkap.
 * - Tidak pernah menggagalkan boot: semua error hanya dicatat ke log
 *   sehingga server tetap jalan dan widget DB Health menampilkan status down.
 */
class EnsureDatabaseReady
{
    /**
     * Perintah console yang harus dilewati agar tidak rekursif.
     *
     * @var list<string>
     */
    private const SKIPPED_COMMANDS = [
        'migrate',
        'migrate:fresh',
        'migrate:install',
        'migrate:refresh',
        'migrate:reset',
        'migrate:rollback',
        'migrate:status',
        'db:seed',
        'db:wipe',
        'db:health',
        'package:discover',
        'config:clear',
        'config:cache',
        'vendor:publish',
    ];

    /**
     * Pastikan database siap dipakai.
     */
    public static function ensure(): void
    {
        if (! self::shouldRun()) {
            return;
        }

        try {
            self::createDatabaseIfMissing();
        } catch (Throwable $e) {
            // MySQL kemungkinan belum jalan (mis. XAMPP belum start).
            Log::warning('Oneshot: MySQL tidak terjangkau, lewati auto-setup. '.$e->getMessage());

            return;
        }

        try {
            if (! Schema::hasTable('users') || ! Schema::hasTable('projects')) {
                Artisan::call('migrate', ['--seed' => true, '--force' => true]);

                // Bila tabel tetap hilang (mis. DROP manual padahal migrations
                // tercatat sudah jalan), jangan fresh otomatis agar tidak
                // menghapus data; cukup catat saran perbaikan.
                if (! Schema::hasTable('users') || ! Schema::hasTable('projects')) {
                    Log::warning('Oneshot: tabel masih hilang setelah migrate, jalankan php artisan migrate:fresh --seed secara manual.');
                }

                return;
            }

            // Tabel ada tapi kosong (mis. migrate tanpa seed): lengkapi via seeder.
            if (DB::table('users')->count() === 0) {
                Artisan::call('db:seed', ['--force' => true]);
            }
        } catch (Throwable $e) {
            Log::warning('Oneshot: auto-migrate gagal. '.$e->getMessage());
        }
    }

    /**
     * Tentukan apakah auto-setup boleh jalan pada konteks saat ini.
     */
    private static function shouldRun(): bool
    {
        // Jangan jalan saat testing agar tidak mengotori database uji.
        if (app()->runningUnitTests()) {
            return false;
        }

        if (app()->runningInConsole()) {
            $argv = $_SERVER['argv'] ?? [];
            $command = $argv[1] ?? '';
            foreach (self::SKIPPED_COMMANDS as $skipped) {
                if ($command === $skipped || str_starts_with($command, $skipped.':')) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Buat database MySQL bila belum ada (tanpa memilih database dulu).
     */
    private static function createDatabaseIfMissing(): void
    {
        $config = config('database.connections.mysql');

        // Hanya relevan untuk driver mysql.
        if (config('database.default') !== 'mysql' || empty($config)) {
            DB::connection()->getPdo();

            return;
        }

        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            if (! str_contains(strtolower($e->getMessage()), 'unknown database')) {
                throw $e;
            }

            $database = $config['database'];
            $pdo = new \PDO(
                "mysql:host={$config['host']};port={$config['port']}",
                $config['username'],
                $config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            $pdo->exec(
                'CREATE DATABASE IF NOT EXISTS `'.str_replace('`', '', $database).'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );
            DB::purge('mysql');
            DB::connection()->getPdo();
        }
    }
}
