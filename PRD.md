# PRD: Secure Academic Portal Database

Oct 10, 2026 · @Raymond

PRD ini menetapkan fondasi database MySQL (tabel users dan projects) untuk portal portofolio akademik berbasis Laravel Breeze, lengkap dengan seeding, dashboard dan aturan pengumpulan tugas.

## 1. Latar belakang dan tujuan

Portal portofolio minggu lalu menyimpan data profil dan tugas di variabel controller, sehingga data hilang saat kode berubah dan tidak bisa dimiliki per pengguna. Tugas ini mengganti penyimpanan statis tersebut dengan database relasional MySQL yang terstruktur dan aman.

**Tujuan produk**

- Menyimpan kredensial login, riwayat tugas kuliah dan ide proposal Agentic AI di database fisik MySQL.
- Mengikat setiap project ke satu user lewat foreign key dengan cascade delete.
- Menyediakan data dummy realistis dan akun demo agar portal bisa langsung dipresentasikan.
- Menampilkan hanya project milik user yang sedang login di dashboard.

**Tujuan pembelajaran:** migration, Eloquent model, mass assignment protection, factory dan seeder, serta query relasional yang aman.

## 2. Ruang lingkup

Proyek Agentic AI masih berupa placeholder: strukturnya disiapkan penuh, isinya diganti nanti oleh pemilik proyek.

| Termasuk | Tidak termasuk |
| --- | --- |
| Migration tabel users (bawaan Breeze) dan projects | Logika agent AI yang sebenarnya |
| Model Project dengan $fillable dan relasi | Integrasi API Ollama atau LLM lain |
| ProjectFactory, DatabaseSeeder, akun demo | CRUD lengkap project (create, edit, delete lewat UI) |
| Query dashboard milik user login, terurut terbaru | Fitur upload file atau profil lanjutan |
| README setup, .gitignore, repo GitHub | Deployment ke server produksi |

**Placeholder Agentic AI:** judul, deskripsi, tema\_agent dan api\_key\_secure diisi data contoh. Contoh judul: "\[Placeholder\] Agent Asisten Akademik". Nilai asli menggantikan placeholder tanpa mengubah skema.

## 3. Pengguna dan user stories

| Pengguna | Kebutuhan | Kriteria selesai |
| --- | --- | --- |
| Mahasiswa ITS | Login dengan email student ITS dan melihat project miliknya | Dashboard hanya menampilkan project dengan user\_id sama dengan user login |
| Mahasiswa ITS | Menyimpan ide proposal Agentic AI | Data tersimpan di tabel projects, bukan variabel controller |
| Dosen penguji | Menjalankan `php artisan migrate --seed` tanpa error | Tabel terisi 16 user dan project dummy |
| Dosen penguji | Login memakai akun demo | dosenpbkk@its.ac.id punya portofolio siap presentasi |
| Pengembang | Menjaga data aman dari mass assignment dan SQL injection | $fillable terdefinisi dan query memakai Eloquent |

## 4. Spesifikasi database wajib

Skema terdiri dari dua tabel: users dari Laravel Breeze dan projects yang dibuat lewat migration baru. Tipe data di bawah adalah rekomendasi yang memenuhi semua aturan kaku tugas.

**Tabel users** (bawaan Breeze, tidak dibuat ulang)

| Kolom | Tipe | Modifier | Keterangan |
| --- | --- | --- | --- |
| id | bigint unsigned | primary key | Auto increment |
| name | string(255) | - | Nama mahasiswa |
| email | string(255) | unique() | Email student ITS |
| email\_verified\_at | timestamp | nullable() | Bawaan Breeze |
| password | string(255) | - | Di-hash otomatis |
| remember\_token | string(100) | nullable() | Bawaan Breeze |
| created\_at, updated\_at | timestamp | nullable() | timestamps() |

**Tabel projects** (rancangan ide proposal Agentic AI)

| Kolom | Tipe | Modifier | Keterangan |
| --- | --- | --- | --- |
| id | bigint unsigned | primary key | `$table->id()` |
| user\_id | bigint unsigned | foreign key, cascade | `foreignId('user_id')->constrained()->cascadeOnDelete()` |
| judul | string(255) | - | Judul ide proposal |
| deskripsi | text | nullable() | Boleh kosong |
| tema\_agent | string(100) | default('Ollama') | Tema atau engine agent |
| api\_key\_secure | string(255) | - | Disimpan terenkripsi, lihat bagian 6 |
| created\_at, updated\_at | timestamp | nullable() | timestamps() |

**Aturan relasi dan model**

- Kolom `user_id` terikat ke `users.id`; menghapus user otomatis menghapus project miliknya (cascade).
- Model `App\Models\Project` wajib memakai `protected $fillable = ['judul', 'deskripsi', 'tema_agent', 'api_key_secure', 'user_id'];`.
- `Project` punya `belongsTo(User::class)` dan `User` punya `hasMany(Project::class)`.
- Nama file migration mengikuti konvensi, contoh `create_projects_table`; migration dijalankan setelah migration users.

## 5. Challenge A+: seeding dan dashboard

| Komponen | Perilaku yang diharapkan |
| --- | --- |
| ProjectFactory | Menghasilkan judul, deskripsi, tema\_agent dan api\_key\_secure realistis lewat Faker; tema\_agent dipilih dari daftar seperti Ollama, LangChain, AutoGen, CrewAI |
| UserFactory | Menghasilkan mahasiswa acak dengan email unik |
| DatabaseSeeder | Membuat 15 user acak, masing-masing otomatis punya 2 project (`User::factory(15)->has(Project::factory(2))->create()`) |
| Akun demo | 1 user `dosenpbkk@its.ac.id` dengan data portofolio siap presentasi; dibuat dengan `firstOrCreate` agar seeding berulang tidak error duplikasi |
| Query dashboard | `Project::where('user_id', auth()->id())->latest()->get()` atau `auth()->user()->projects()->latest()->get()` |

**Hasil akhir seeding:** 16 user dan sedikitnya 32 project dummy, ditambah project demo milik akun dosen.

**Catatan placeholder:** data project akun demo memakai teks placeholder Agentic AI sampai pemilik proyek memasukkan isi aslinya.

## 6. Kebutuhan non-fungsional dan keamanan

- **Mass assignment:** hanya kolom di `$fillable` yang boleh diisi massal; `id` dan timestamp tidak termasuk.
- **SQL injection:** semua query lewat Eloquent atau query builder dengan binding; tanpa `DB::raw` dari input pengguna.
- **Kerahasiaan api\_key\_secure:** tambahkan cast `'api_key_secure' => 'encrypted'` dan masukkan ke `$hidden` agar tidak tampil di output JSON atau tampilan. Ini rekomendasi tambahan di luar aturan kaku tugas.
- **Kredensial:** file `.env` dan folder `vendor/` masuk `.gitignore` dan tidak ter-commit; sediakan `.env.example` tanpa rahasia.
- **Standar kode:** PSR-12, komentar docblock pada model, factory dan seeder.
- **Keandalan seeding:** `php artisan migrate:fresh --seed` berjalan berulang kali tanpa error; email unik dijamin lewat `fake()->unique()->safeEmail()` atau pola email ITS yang unik.
- **Kompatibilitas:** Laravel dengan Breeze dan MySQL; konfigurasi koneksi hanya lewat `.env`.

## 7. Kriteria keberhasilan

Bobot penilaian total 100%; skema migration dan model menyumbang 60% dari nilai.

| Kriteria | Bobot | Cara memenuhi |
| --- | --- | --- |
| Rancangan skema migration | 35% | Tipe data tepat, unique() pada email, foreign key user\_id dengan cascade delete, nullable() dan default('Ollama') |
| Pemodelan data dan mass assignment guard | 25% | Nama model Project sesuai konvensi, $fillable lengkap, relasi belongsTo dan hasMany |
| Factories dan seeder | 20% | `migrate --seed` sukses tanpa duplikasi, 15 user x 2 project, akun demo tersedia |
| Integritas query dashboard | 10% | Hanya project user login, urut terbaru, aman dari SQL injection |
| Kerapian kode dan PSR-12 | 10% | Format bersih, terdokumentasi, .env tidak ter-commit |

**Checklist penerimaan**

- [ ] `composer install` dan `php artisan migrate --seed` berjalan di mesin bersih tanpa error
- [ ] Tabel users dan projects ada dengan foreign key cascade
- [ ] Menghapus satu user ikut menghapus project miliknya
- [ ] Login `dosenpbkk@its.ac.id` menampilkan portofolio siap pakai
- [ ] Dua akun berbeda melihat daftar project yang berbeda di dashboard
- [ ] `.env` dan `vendor/` tidak ada di repositori

## 8. Pengumpulan dan sesi demo

Tugas dikumpulkan paling lambat H-1 sebelum Pertemuan 7 dimulai, lewat portal LMS ITS.

| Ketentuan | Rincian |
| --- | --- |
| Tautan GitHub | Kumpulkan link repositori; `.gitignore` aktif sehingga `vendor/` dan `.env` tidak masuk Git |
| README.md | Instruksi singkat setup lokal: `composer install`, salin `.env`, atur koneksi MySQL, `php artisan migrate --seed` |
| Sesi demo | Pada Pertemuan 7, dosen memilih 3 mahasiswa secara acak untuk demo migrasi dan seeding selama 3 menit |
| Plagiarisme | Kode yang terdeteksi menjiplak mendapat nilai nol untuk seluruh anggota terkait |

**Alur demo 3 menit:** jalankan `php artisan migrate:fresh --seed`, tunjukkan isi tabel users dan projects, login sebagai `dosenpbkk@its.ac.id`, lalu tampilkan dashboard dengan project miliknya.

## 9. Risiko dan pertanyaan terbuka

| Risiko | Dampak | Mitigasi |
| --- | --- | --- |
| Email seeder bentrok dengan akun demo | Seeding gagal karena duplikasi | Akun demo dibuat dengan `firstOrCreate`; email Faker memakai `unique()` |
| Urutan migration salah | Foreign key gagal dibuat | Migration projects dibuat setelah users; cek dengan `migrate:fresh` |
| `.env` atau API key ter-commit | Nilai kebersihan kode turun, kunci bocor | Cek `.gitignore` sebelum push; pakai `.env.example` |
| Isi proyek Agentic AI belum siap | Demo tampil berisi placeholder | Placeholder dibuat realistis dan mudah diganti lewat seeder |

**Pertanyaan terbuka**

- [ ] Isi proyek Agentic AI: judul, deskripsi, tema\_agent dan api\_key\_secure (menunggu dari pemilik proyek)
- [ ] Versi Laravel dan PHP yang dipakai kelas (menentukan sintaks migration dan Faker)
- [ ] Apakah api\_key\_secure perlu enkripsi, atau cukup disimpan sebagai string biasa sesuai spesifikasi tugas
