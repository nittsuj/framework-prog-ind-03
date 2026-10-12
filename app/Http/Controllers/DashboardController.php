<?php

namespace App\Http\Controllers;

use App\Agents\DatabaseHealthAgent;
use Illuminate\Contracts\View\View;

/**
 * Dashboard portofolio akademik milik user yang sedang login.
 */
class DashboardController extends Controller
{
    /**
     * Tampilkan hanya project milik user login, terurut terbaru,
     * beserta laporan kesehatan database dari Agentic AI monitor.
     */
    public function index(DatabaseHealthAgent $health): View
    {
        // Query relasional aman via Eloquent (terproteksi dari SQL injection).
        $projects = auth()->user()->projects()->latest()->get();

        return view('dashboard', [
            'projects' => $projects,
            'dbHealth' => $health->analyze(),
        ]);
    }
}
