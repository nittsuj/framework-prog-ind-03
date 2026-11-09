<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Tombol login instan untuk demo dosen/asdos.
 *
 * Hanya aktif bila config('app.demo_login') bernilai true (default: APP_ENV=local),
 * dan hanya untuk akun demo yang sudah didaftarkan di whitelist.
 */
class DemoLoginController extends Controller
{
    /**
     * @var array<string, string>
     */
    public const ACCOUNTS = [
        'admin' => 'dosenpbkk@its.ac.id',
        'staf' => 'staf@its.ac.id',
    ];

    public function store(Request $request, string $role): RedirectResponse
    {
        abort_unless(config('app.demo_login'), 404);
        abort_unless(array_key_exists($role, self::ACCOUNTS), 404);

        $user = User::where('email', self::ACCOUNTS[$role])->first();

        if (! $user) {
            return back()->withErrors(['email' => 'Akun demo belum di-seed (jalankan php artisan db:seed).']);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard');
    }
}
