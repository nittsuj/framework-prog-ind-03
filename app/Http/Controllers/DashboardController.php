<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Dashboard portofolio akademik milik user yang sedang login.
 */
class DashboardController extends Controller
{
    /**
     * Tampilkan hanya project milik user login, terurut terbaru,
     * beserta laporan kesehatan database dari Agentic AI monitor.
     */
    public function index(Request $request): View
    {
        $projects = Project::query()
            ->with('user')
            ->when($request->string('q')->trim()->value(), function ($query, string $keyword): void {
                $query->where(function ($query) use ($keyword): void {
                    $query->where('judul', 'like', "%{$keyword}%")
                        ->orWhere('tema_agent', 'like', "%{$keyword}%")
                        ->orWhereHas('user', fn ($users) => $users->where('name', 'like', "%{$keyword}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('dashboard', [
            'projects' => $projects,
            'stats' => [
                'users' => User::count(),
                'projects' => Project::count(),
                'themes' => Project::query()->distinct('tema_agent')->count('tema_agent'),
            ],
            'keyword' => $request->string('q')->trim()->value(),
        ]);
    }
}
