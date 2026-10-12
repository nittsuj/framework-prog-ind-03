<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder utama portal akademik.
 *
 * - Membuat 15 user acak, masing-masing dengan 2 project.
 * - Membuat 1 akun demo dosen yang idempoten (firstOrCreate).
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 15 user acak x 2 project = 30 project dummy.
        User::factory(15)->has(Project::factory(2))->create();

        // Akun demo siap presentasi (idempoten agar re-seed aman).
        $demo = User::firstOrCreate(
            ['email' => 'dosenpbkk@its.ac.id'],
            [
                'name' => 'Dosen PBKK',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Portfolio placeholder Agentic AI milik akun demo.
        // Salah satunya adalah agent monitoring database health.
        if ($demo->projects()->count() === 0) {
            $demo->projects()->createMany([
                [
                    'judul' => '[Placeholder] Agent Asisten Akademik',
                    'deskripsi' => 'Placeholder ide proposal Agentic AI untuk membantu mahasiswa menyusun portofolio dan riwayat tugas kuliah secara otomatis.',
                    'tema_agent' => 'Ollama',
                    'api_key_secure' => 'sk-demo-akademik-001',
                ],
                [
                    'judul' => '[Placeholder] Agent Monitoring Database Health',
                    'deskripsi' => 'Agent Agentic AI yang memantau kesehatan database MySQL: latensi koneksi, jumlah users/projects, integritas foreign key, dan status seeding. Laporan tersedia di dashboard dan via php artisan db:health.',
                    'tema_agent' => 'LangChain',
                    'api_key_secure' => 'sk-demo-dbhealth-002',
                ],
                [
                    'judul' => '[Placeholder] Agent Evaluasi Tugas Otomatis',
                    'deskripsi' => 'Placeholder agent untuk rekap dan penilaian awal tugas kuliah berbasis riwayat project mahasiswa.',
                    'tema_agent' => 'CrewAI',
                    'api_key_secure' => 'sk-demo-evaluasi-003',
                ],
            ]);
        }
    }
}
