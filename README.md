# Secure Academic Portal — Database + Agentic AI DB Health Monitor

Portal portofolio akademik berbasis Laravel Breeze + MySQL (implementasi PRD Week 6).
Data profil dan tugas disimpan di database relasional (bukan variabel controller),
setiap project terikat ke user via foreign key cascade. Placeholder Agentic AI
diwujudkan sebagai **Agent Monitoring Database Health** (read-only).

## Cara jalan cepat (oneshot)

### Dengan Docker (tanpa PHP/Composer/MySQL/Node di laptop)

Pastikan Docker Engine dan Docker Compose plugin tersedia, lalu jalankan dari
folder proyek:

```bash
docker compose up --build
```

Buka http://localhost:8000. Migrasi dan akun demo dibuat otomatis pada startup
pertama, dan data disimpan di volume Docker `portal-data`. Login Admin
`dosenpbkk@its.ac.id` / `password`; login Staf `staf@its.ac.id` / `password`.
Untuk menghentikan server tekan `Ctrl+C`; jalankan kembali perintah yang sama
untuk melanjutkan dengan database yang tersimpan. Hapus volume dengan
`docker compose down -v` jika ingin menghapus seluruh data demo.

Cara lokal di bawah ini tetap membutuhkan PHP, Composer, dan MySQL.

Cukup **satu perintah** — database dibuat + di-seed otomatis saat aplikasi boot:

```bash
php artisan serve
```

Lalu buka http://127.0.0.1:8000 dan login demo
`dosenpbkk@its.ac.id` / `password`.

Alternatif setara:

```bash
composer oneshot
# atau di Windows: klik 2x oneshot.bat (sekalian menyalakan XAMPP MySQL)
```

Syarat: MySQL jalan di `127.0.0.1:3306` (mis. XAMPP Control Panel → Start MySQL).
Jika MySQL belum jalan, server tetap start tapi widget DB Health menampilkan
status `down` + saran perbaikan. `oneshot.bat` menyalakan MySQL XAMPP otomatis
bila terinstal di `C:\xampp`.

## Setup manual (bila perlu)

## Fitur PRD

- Migration `users` (bawaan Breeze) + `projects` (`user_id` FK cascade, `judul`,
  `deskripsi` nullable, `tema_agent` default `Ollama`, `api_key_secure` terenkripsi).
- Model `Project` (`$fillable`, `$hidden`, cast `encrypted`, `belongsTo`);
  `User` (`hasMany`).
- `ProjectFactory` (tema: Ollama, LangChain, AutoGen, CrewAI), `UserFactory`,
  `DatabaseSeeder`: 15 user × 2 project + akun demo `dosenpbkk@its.ac.id`
  (dibuat `firstOrCreate`, 3 project placeholder siap presentasi).
- Dashboard: `auth()->user()->projects()->latest()->get()` — hanya milik user login.
- Agentic AI DB Health: `App\Agents\DatabaseHealthAgent`, artisan
  `php artisan db:health`, widget dashboard + JSON `GET /dashboard/health`.

## Setup lokal

```bash
composer install
cp .env.example .env          # Windows: copy .env.example .env
php artisan key:generate
```

Buat database MySQL:

```sql
CREATE DATABASE academic_portal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Atur koneksi di `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=academic_portal
DB_USERNAME=root
DB_PASSWORD=
```

Lalu:

```bash
php artisan migrate --seed
# atau mulai bersih:
php artisan migrate:fresh --seed
php artisan serve
```

Login demo: `dosenpbkk@its.ac.id` / `password`.

## Cek kesehatan database (Agentic AI)

```bash
php artisan db:health
```

Memeriksa latensi koneksi, versi MySQL, keberadaan tabel, jumlah
users/projects, project yatim (harus 0), distribusi `tema_agent`,
kehadiran akun demo, dan kelengkapan seeding (16 users, 32+ projects).

## Alur demo 3 menit (Pertemuan 7)

```bash
php artisan migrate:fresh --seed
php artisan db:health
```

Tunjukkan isi tabel `users`/`projects`, login sebagai
`dosenpbkk@its.ac.id`, tampilkan dashboard dengan project miliknya.

## Catatan keamanan

- Query hanya via Eloquent (binding otomatis, anti SQL injection).
- `api_key_secure` memakai cast `encrypted` + `$hidden`.
- `.env` dan `vendor/` di-`.gitignore` dan tidak di-commit; kredensial hanya via `.env`.
- Standar kode PSR-12 (`vendor/bin/pint` lolos).
