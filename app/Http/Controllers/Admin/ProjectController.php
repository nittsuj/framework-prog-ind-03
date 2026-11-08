<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class ProjectController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.dashboard');
    }

    public function destroy(Project $project): RedirectResponse
    {
        abort_unless(auth()->user()->hasRole('Admin'), 403);

        $project->delete();

        return to_route('admin.dashboard')->with('status', 'Data proyek berhasil dihapus.');
    }
}
