@extends('adminlte::page')

@section('title', 'Dashboard Akademik')

@section('content_header')
    <h1>Dashboard Akademik</h1>
@stop

@section('content')
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-4 col-12"><div class="small-box bg-info"><div class="inner"><h3>{{ number_format($stats['users']) }}</h3><p>Total Pengguna</p></div><div class="icon"><i class="fas fa-users"></i></div></div></div>
        <div class="col-lg-4 col-12"><div class="small-box bg-success"><div class="inner"><h3>{{ number_format($stats['projects']) }}</h3><p>Total Data Proyek</p></div><div class="icon"><i class="fas fa-folder-open"></i></div></div></div>
        <div class="col-lg-4 col-12"><div class="small-box bg-warning"><div class="inner"><h3>{{ number_format($stats['themes']) }}</h3><p>Tema Agent</p></div><div class="icon"><i class="fas fa-layer-group"></i></div></div></div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Data Proyek</h3></div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.dashboard') }}" class="form-inline mb-3">
                <input class="form-control mr-2" type="search" name="q" value="{{ $keyword }}" placeholder="Cari judul, tema, atau pemilik">
                <button class="btn btn-primary" type="submit">Cari</button>
                @if ($keyword)<a class="btn btn-default ml-2" href="{{ route('admin.dashboard') }}">Reset</a>@endif
            </form>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead><tr><th>Judul</th><th>Pemilik</th><th>Tema Agent</th><th>Dibuat</th>@if (auth()->user()->hasRole('Admin'))<th>Aksi</th>@endif</tr></thead>
                    <tbody>
                    @forelse ($projects as $project)
                        <tr>
                            <td>{{ $project->judul }}</td><td>{{ $project->user->name }}</td><td>{{ $project->tema_agent }}</td><td>{{ $project->created_at?->format('d M Y') }}</td>
                            @if (auth()->user()->hasRole('Admin'))
                                <td><form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Hapus data proyek ini?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" type="submit"><i class="fas fa-trash"></i> Hapus Data</button></form></td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">Tidak ada data proyek yang cocok.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            {{ $projects->links() }}
        </div>
    </div>
@stop
