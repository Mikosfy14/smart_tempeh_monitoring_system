@extends('layouts.app')

@section('title', 'Edit Alat — ' . ($device->label_rak ?? $device->device_id))

@section('content')
<div class="stagger-children" style="max-width: 720px; margin: 0 auto;">

    {{-- Header & Back Button --}}
    <div class="flex flex-col md:flex-row md:items-center justify-center mb-8 relative gap-4">
        <a href="{{ route('device.detail', $device->id) }}" class="btn btn-secondary btn-sm md:absolute md:left-0 self-start" id="btn-back-detail">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
            Kembali
        </a>
        <div class="text-left md:text-center w-full">
            <h1 class="text-2xl font-bold">Edit Perangkat</h1>
            <p class="text-sm mt-1" style="color: var(--color-text-muted);">ID: {{ $device->device_id }}</p>
        </div>
    </div>

    <div class="card-static">
        <form action="{{ route('device.update', $device->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2" style="color: var(--color-text-secondary);">Nama Perangkat</label>
                <input type="text" name="device_name" class="form-input w-full" value="{{ old('device_name', $device->device_name) }}" placeholder="Masukkan nama perangkat">
                @error('device_name')
                    <p class="text-xs mt-1" style="color: var(--color-accent-red);">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2" style="color: var(--color-text-secondary);">Label Rak</label>
                <input type="text" name="label_rak" class="form-input w-full" value="{{ old('label_rak', $device->label_rak) }}" placeholder="Masukkan label rak">
                @error('label_rak')
                    <p class="text-xs mt-1" style="color: var(--color-accent-red);">{{ $message }}</p>
                @enderror
            </div>

            <hr style="border-color: var(--color-border-card); margin: 24px 0;">

            <h3 class="text-sm font-bold mb-4">Ambang Batas Notifikasi Peringatan</h3>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2" style="color: var(--color-text-secondary);">Suhu Maks (°C)</label>
                <input type="number" step="0.1" name="temp_threshold" class="form-input w-full" value="{{ old('temp_threshold', $device->temp_threshold ?? 35.0) }}">
                @error('temp_threshold')
                    <p class="text-xs mt-1" style="color: var(--color-accent-red);">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2" style="color: var(--color-text-secondary);">Amonia Maks (ppm)</label>
                <input type="number" step="0.1" name="amonia_threshold" class="form-input w-full" value="{{ old('amonia_threshold', $device->amonia_threshold ?? 25.0) }}">
                @error('amonia_threshold')
                    <p class="text-xs mt-1" style="color: var(--color-accent-red);">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium mb-2" style="color: var(--color-text-secondary);">Kelembapan Maks (%)</label>
                <input type="number" step="0.1" name="humidity_threshold" class="form-input w-full" value="{{ old('humidity_threshold', $device->humidity_threshold ?? 90.0) }}">
                @error('humidity_threshold')
                    <p class="text-xs mt-1" style="color: var(--color-accent-red);">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <a href="{{ route('device.detail', $device->id) }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>

        <hr style="border-color: var(--color-border-card); margin: 32px 0;">

        <div>
            <h3 class="text-sm font-bold mb-2" style="color: var(--color-accent-red);">Zona Berbahaya</h3>
            <p class="text-xs mb-4" style="color: var(--color-text-secondary);">
                Melepas alat akan menghilangkannya dari dashboard Anda, namun data riwayat tidak akan terhapus dari sistem.
            </p>
            <form id="form-unregister-device" action="{{ route('device.unregister') }}" method="POST">
                @csrf
                <input type="hidden" name="device_id" value="{{ $device->id }}">
                <button type="button" class="btn btn-danger" onclick="confirmUnregisterDevice()">Lepas Perangkat</button>
            </form>
        </div>
    </div>

</div>

@push('scripts')
<script>
    function confirmUnregisterDevice() {
        if (typeof Swal === 'undefined') {
            if (confirm('Apakah Anda yakin ingin melepas perangkat ini dari akun Anda?')) {
                document.getElementById('form-unregister-device').submit();
            }
            return;
        }

        Swal.fire({
            title: 'Lepas Perangkat?',
            html: 'Apakah Anda yakin ingin melepas perangkat ini dari akun Anda?<br><br>Perangkat akan dihilangkan dari dashboard, namun data riwayat tidak akan terhapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Lepas',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#334155',
            background: 'var(--color-bg-card, #1e293b)',
            color: 'var(--color-text-primary, #f1f5f9)',
            reverseButtons: true,
            focusCancel: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('form-unregister-device').submit();
            }
        });
    }
</script>
@endpush
@endsection
