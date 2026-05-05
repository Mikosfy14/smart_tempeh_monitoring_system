@extends('layouts.admin')

@section('title', 'Device Mapping — Smart Tempeh Monitoring')
@section('page-title', 'Device Mapping')

@section('content')
<div class="stagger-children">

    {{-- Link Device Form --}}
    <div class="card-static mb-6">
        <h2 class="text-lg font-bold mb-4">Link New Device</h2>
        <div id="device-errors" class="alert alert-error mb-4" style="display: none;"></div>
        <form id="link-device-form" onsubmit="linkDevice(event)">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Assign to User</label>
                    <select name="user_id" class="form-input" required id="device-user">
                        <option value="">— Select User —</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Device Name</label>
                    <input type="text" name="device_name" class="form-input" placeholder="e.g. Sensor Ruang A" required id="device-name">
                </div>
                <div>
                    <label class="form-label">Device Token</label>
                    <input type="text" name="device_token" class="form-input" placeholder="Unique token from ESP32" required id="device-token">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary" id="btn-link-device">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                    Link Device
                </button>
            </div>
        </form>
    </div>

    {{-- Devices Table --}}
    <div class="card-static overflow-x-auto">
        <h2 class="text-lg font-bold mb-4">Linked Devices</h2>
        <table class="data-table" id="devices-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Device Name</th>
                    <th>Token</th>
                    <th>Assigned User</th>
                    <th>Mode</th>
                    <th>Fan</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $i => $device)
                <tr id="device-row-{{ $device->id }}">
                    <td style="color: var(--color-text-muted);">{{ $i + 1 }}</td>
                    <td class="font-medium">{{ $device->device_name }}</td>
                    <td><code class="text-sm px-2 py-1 rounded" style="background: var(--color-bg-primary); color: var(--color-accent-teal);">{{ $device->device_token }}</code></td>
                    <td>{{ $device->user ? $device->user->name : '—' }}</td>
                    <td><span class="badge {{ $device->operation_mode === 'AUTO' ? 'badge-green' : 'badge-amber' }}">{{ $device->operation_mode }}</span></td>
                    <td><span class="badge {{ $device->fan_status === 'ON' ? 'badge-green' : 'badge-red' }}">{{ $device->fan_status }}</span></td>
                    <td style="color: var(--color-text-secondary);">{{ $device->created_at->format('d M Y') }}</td>
                    <td>
                        <button class="btn-icon" style="color: var(--color-accent-red);" onclick="unlinkDevice({{ $device->id }}, '{{ $device->device_name }}')" title="Unlink">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-8" style="color: var(--color-text-muted);">No devices linked yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- DELETE CONFIRM MODAL --}}
<div class="modal-overlay" id="unlink-modal">
    <div class="modal-content text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full mb-4" style="background: rgba(248, 113, 113, 0.1);">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" style="color: var(--color-accent-red);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" /></svg>
        </div>
        <h3 class="text-lg font-bold mb-2">Unlink Device</h3>
        <p class="text-sm mb-6" style="color: var(--color-text-secondary);">Remove <strong id="unlink-device-name" class="text-white"></strong>? All associated sensor logs will be deleted.</p>
        <input type="hidden" id="unlink-device-id">
        <div class="flex gap-3">
            <button class="btn btn-secondary flex-1" onclick="closeModal('unlink-modal')">Cancel</button>
            <button class="btn btn-danger flex-1" onclick="confirmUnlink()" id="btn-confirm-unlink">Unlink</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openModal(id) { document.getElementById(id).classList.add('active'); }
function closeModal(id) { document.getElementById(id).classList.remove('active'); }

async function linkDevice(e) {
    e.preventDefault();
    const errEl = document.getElementById('device-errors');
    errEl.style.display = 'none';

    const body = {
        user_id: document.getElementById('device-user').value,
        device_name: document.getElementById('device-name').value,
        device_token: document.getElementById('device-token').value,
    };

    try {
        const res = await fetch('{{ route("admin.devices.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify(body)
        });
        const data = await res.json();

        if (!res.ok) {
            const msgs = Object.values(data.errors || {}).flat().join('<br>');
            errEl.innerHTML = msgs || 'Validation failed';
            errEl.style.display = 'flex';
            return;
        }

        document.getElementById('link-device-form').reset();
        location.reload();
    } catch (err) {
        errEl.textContent = 'Network error, please try again.';
        errEl.style.display = 'flex';
    }
}

function unlinkDevice(id, name) {
    document.getElementById('unlink-device-id').value = id;
    document.getElementById('unlink-device-name').textContent = name;
    openModal('unlink-modal');
}

async function confirmUnlink() {
    const deviceId = document.getElementById('unlink-device-id').value;
    try {
        await fetch(`/admin/devices/${deviceId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': window.csrfToken, 'Accept': 'application/json' }
        });
        closeModal('unlink-modal');
        location.reload();
    } catch (err) { alert('Failed to unlink device.'); }
}
</script>
@endpush
