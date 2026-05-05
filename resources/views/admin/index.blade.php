@extends('layouts.admin')

@section('title', 'Admin Overview — Smart Tempeh Monitoring')
@section('page-title', 'Overview')

@section('content')
<div class="stagger-children">

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="card-static">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl" style="background: rgba(96, 165, 250, 0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" style="color: var(--color-accent-blue);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm" style="color: var(--color-text-secondary);">Total Users</p>
                    <p class="text-3xl font-bold">{{ $totalUsers }}</p>
                </div>
            </div>
        </div>

        <div class="card-static">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl" style="background: rgba(45, 212, 191, 0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" style="color: var(--color-accent-teal);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm" style="color: var(--color-text-secondary);">Total Devices</p>
                    <p class="text-3xl font-bold">{{ $totalDevices }}</p>
                </div>
            </div>
        </div>

        <div class="card-static">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl" style="background: rgba(52, 211, 153, 0.1);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" style="color: var(--color-accent-green);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm" style="color: var(--color-text-secondary);">Sensor Logs</p>
                    <p class="text-3xl font-bold">{{ number_format($totalLogs) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Users --}}
    <div class="card-static">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold">Recent Users</h2>
            <a href="{{ route('admin.users') }}" class="btn btn-sm btn-secondary">View All →</a>
        </div>

        @if($recentUsers->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>WhatsApp</th>
                    <th>Devices</th>
                    <th>Registered</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentUsers as $user)
                <tr>
                    <td class="font-medium">{{ $user->name }}</td>
                    <td style="color: var(--color-text-secondary);">{{ $user->email }}</td>
                    <td style="color: var(--color-accent-teal);">{{ $user->whatsapp_number ?? '—' }}</td>
                    <td><span class="badge badge-blue">{{ $user->devices_count }}</span></td>
                    <td style="color: var(--color-text-secondary);">{{ $user->created_at->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-center py-8" style="color: var(--color-text-muted);">No users registered yet.</p>
        @endif
    </div>

</div>
@endsection
