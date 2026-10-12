<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in!") }}
                    <p class="mt-1 text-sm text-gray-500">
                        Menampilkan {{ $projects->count() }} project milik {{ auth()->user()->email }}.
                    </p>
                </div>
            </div>

            {{-- Agentic AI: Database Health Monitor --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-gray-800">Agent Monitoring Database Health</h3>
                    <p class="text-sm text-gray-500">
                        Status: <span class="font-semibold">{{ strtoupper($dbHealth['status']) }}</span>
                        · {{ $dbHealth['database'] }} ({{ $dbHealth['driver'] }})
                        · {{ $dbHealth['latency_ms'] }} ms
                        · dicek {{ $dbHealth['checked_at'] }}
                    </p>
                    <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div class="p-3 bg-gray-50 rounded">Users: <strong>{{ $dbHealth['counts']['users'] }}</strong></div>
                        <div class="p-3 bg-gray-50 rounded">Projects: <strong>{{ $dbHealth['counts']['projects'] }}</strong></div>
                        <div class="p-3 bg-gray-50 rounded">Orphaned: <strong>{{ $dbHealth['counts']['orphaned_projects'] }}</strong></div>
                        <div class="p-3 bg-gray-50 rounded">Demo: <strong>{{ $dbHealth['demo_account_present'] ? 'OK' : 'Missing' }}</strong></div>
                    </div>
                    <ul class="mt-3 text-sm text-gray-600 list-disc list-inside">
                        @foreach ($dbHealth['recommendations'] as $rec)
                            <li>{{ $rec }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('dashboard.health') }}" class="mt-2 inline-block text-sm text-indigo-600 underline">Lihat JSON health</a>
                </div>
            </div>

            {{-- Daftar project milik user login --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-gray-800">Project Saya</h3>
                    @forelse ($projects as $project)
                        <div class="mt-4 p-4 border rounded-lg">
                            <div class="font-semibold">{{ $project->judul }}</div>
                            <div class="text-sm text-gray-500">
                                Tema: {{ $project->tema_agent }} · {{ $project->created_at?->diffForHumans() }}
                            </div>
                            @if ($project->deskripsi)
                                <p class="mt-2 text-sm text-gray-700">{{ $project->deskripsi }}</p>
                            @endif
                        </div>
                    @empty
                        <p class="mt-2 text-sm text-gray-500">Belum ada project.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
