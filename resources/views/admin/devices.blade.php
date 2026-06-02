@extends('layouts.admin')

@section('title', 'Device Whitelist — Smart Tempeh Monitoring')
@section('page-title', 'Device Whitelist')

@section('content')
<div class="stagger-children">

    {{-- Header & Add Device --}}
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm" style="color: var(--color-text-secondary);">Manage hardware IDs and device assignments</p>
        <button class="btn btn-primary" onclick="openModal('add-modal')" id="btn-add-whitelist">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Add Hardware ID
        </button>
    </div>

    {{-- Whitelist Table --}}
    <div class="card-static overflow-x-auto">
        <table class="data-table table-data" id="whitelist-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Device ID</th>
                    <th>Status</th>
                    <th>Assigned User</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($masterDevices as $i => $master)
                <tr id="row-{{ $master->id }}">
                    {{-- Kolom 1: Nomor --}}
                    <td data-label="#" style="color: var(--color-text-muted);">
                        {{ $i + 1 }}
                    </td>

                    {{-- Kolom 2: Device ID --}}
                    <td data-label="Device ID">
                        <code class="text-sm px-2 py-1 rounded font-mono font-bold" style="background: var(--color-bg-primary); color: var(--color-accent-teal);">
                            {{ $master->device_id }}
                        </code>
                    </td>

                    {{-- Kolom 3: Status --}}
                    <td data-label="Status">
                        @if($master->is_registered)
                        <span class="badge badge-green">Registered</span>
                        @else
                        <span class="badge badge-blue">Available</span>
                        @endif
                    </td>

                    {{-- Kolom 4: Assigned User --}}
                    <td data-label="Assigned User">
                        @if($master->is_registered && $master->device && $master->device->user)
                        {{ $master->device->user->name }}
                        @else
                        <span style="color: var(--color-text-muted);">—</span>
                        @endif
                    </td>

                    {{-- Kolom 5: Created At --}}
                    <td data-label="Created At" style="color: var(--color-text-secondary);">
                        {{ $master->created_at->format('d M Y') }}
                    </td>

                    {{-- Kolom 6: Actions --}}
                    <td data-label="Actions">
                        {{-- justify-end agar deretan tombol merapat ke kanan di layar HP --}}
                        <div class="flex items-center gap-1 justify-end">
                            @if(!$master->is_registered)
                            {{-- Assign to User --}}
                            <button class="btn-icon" style="color: var(--color-accent-teal);" onclick="openAssignModal({{ $master->id }}, '{{ $master->device_id }}')" title="Assign to User">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </button>
                            {{-- Remove from Whitelist --}}
                            <button class="btn-icon" style="color: var(--color-accent-red);" onclick="deleteDevice({{ $master->id }}, '{{ $master->device_id }}')" title="Remove from Whitelist">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                            @else
                            {{-- Unassign from User --}}
                            <button class="btn-icon" style="color: var(--color-accent-amber);" onclick="unassignDevice({{ $master->id }}, '{{ $master->device_id }}')" title="Unassign from User">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6" />
                                </svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4" style="color: var(--color-text-muted);">Belum ada device di whitelist.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ADD MODAL --}}
<div class="modal-overlay" id="add-modal">
    <div class="modal-content">
        <div class="flex items-center justify-between mb-6">
            <h3 class="modal-title mb-0">Add Device to Whitelist</h3>
            <button class="btn-icon" onclick="closeModal('add-modal')">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="add-errors" class="alert alert-error mb-4" style="display: none;"></div>
        <form id="add-device-form" onsubmit="addDevice(event)">
            <div class="mb-6">
                <label class="form-label">Device ID</label>
                <input type="text" name="device_id" class="form-input" placeholder="e.g. TEMPE-001" required id="add-device-id" style="text-transform: uppercase;">
            </div>
            <button type="submit" class="btn btn-primary w-full" id="btn-submit-add">Add to Whitelist</button>
        </form>
    </div>
</div>

{{-- ASSIGN MODAL --}}
<div class="modal-overlay" id="assign-modal">
    <div class="modal-content">
        <div class="flex items-center justify-between mb-6">
            <h3 class="modal-title mb-0">Assign Device to User</h3>
            <button class="btn-icon" onclick="closeModal('assign-modal')">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="assign-errors" class="alert alert-error mb-4" style="display: none;"></div>
        <form id="assign-device-form" onsubmit="confirmAssign(event)">
            <input type="hidden" id="assign-master-id">
            <div class="mb-4">
                <label class="form-label">Device ID</label>
                <input type="text" class="form-input" id="assign-device-id-display" disabled style="color: var(--color-accent-teal);">
            </div>
            <div class="mb-4">
                <label class="form-label">Assign to User</label>
                <select name="user_id" class="form-input" required id="assign-user-id">
                    <option value="">— Pilih User —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-6">
                <label class="form-label">Label Rak <span style="color: var(--color-text-muted);">(opsional)</span></label>
                <input type="text" name="label_rak" class="form-input" placeholder="e.g. Rak Tempe Lantai 2" id="assign-label-rak">
            </div>
            <button type="submit" class="btn btn-primary w-full" id="btn-submit-assign">Assign Device</button>
        </form>
    </div>
</div>

{{-- DELETE CONFIRM MODAL --}}
<div class="modal-overlay" id="delete-modal">
    <div class="modal-content text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full mb-4" style="background: rgba(248, 113, 113, 0.1);">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" style="color: var(--color-accent-red);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold mb-2">Remove Device</h3>
        <p class="text-sm mb-6" style="color: var(--color-text-secondary);">Remove <strong id="delete-device-id" class="text-white"></strong> from the whitelist? Users will no longer be able to register it.</p>
        <input type="hidden" id="delete-id">
        <div class="flex gap-3">
            <button class="btn btn-secondary flex-1" onclick="closeModal('delete-modal')">Cancel</button>
            <button class="btn btn-danger flex-1" onclick="confirmDelete()" id="btn-confirm-delete">Remove</button>
        </div>
    </div>
</div>

{{-- UNASSIGN CONFIRM MODAL --}}
<div class="modal-overlay" id="unassign-modal">
    <div class="modal-content text-center">
        <div class="inline-flex items-center justify-center w-14 h-14 rounded-full mb-4" style="background: rgba(251, 191, 36, 0.1);">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" style="color: var(--color-accent-amber);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold mb-2">Unassign Device</h3>
        <p class="text-sm mb-6" style="color: var(--color-text-secondary);">Unassign <strong id="unassign-device-id" class="text-white"></strong> from its current user? The device will become available for reassignment.</p>
        <input type="hidden" id="unassign-id">
        <div class="flex gap-3">
            <button class="btn btn-secondary flex-1" onclick="closeModal('unassign-modal')">Cancel</button>
            <button class="btn btn-danger flex-1" onclick="confirmUnassign()" id="btn-confirm-unassign">Unassign</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    // ADD DEVICE TO WHITELIST
    async function addDevice(e) {
        e.preventDefault();
        const errEl = document.getElementById('add-errors');
        errEl.style.display = 'none';

        const body = {
            device_id: document.getElementById('add-device-id').value.toUpperCase()
        };

        try {
            const res = await fetch('{{ route("admin.master-devices.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(body)
            });
            const data = await res.json();

            if (!res.ok) {
                const msgs = Object.values(data.errors || {}).flat().join('<br>');
                errEl.innerHTML = msgs || data.message || 'Validation failed';
                errEl.style.display = 'flex';
                return;
            }

            closeModal('add-modal');
            location.reload();
        } catch (err) {
            errEl.textContent = 'Network error, please try again.';
            errEl.style.display = 'flex';
        }
    }

    // DELETE DEVICE FROM WHITELIST
    function deleteDevice(id, deviceId) {
        document.getElementById('delete-id').value = id;
        document.getElementById('delete-device-id').textContent = deviceId;
        openModal('delete-modal');
    }

    async function confirmDelete() {
        const id = document.getElementById('delete-id').value;
        try {
            await fetch(`{{ url('/stm-internal/master-devices') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                }
            });
            closeModal('delete-modal');
            location.reload();
        } catch (err) {
            alert('Failed to remove device.');
        }
    }

    // ASSIGN DEVICE TO USER
    function openAssignModal(masterId, deviceId) {
        document.getElementById('assign-master-id').value = masterId;
        document.getElementById('assign-device-id-display').value = deviceId;
        document.getElementById('assign-user-id').value = '';
        document.getElementById('assign-label-rak').value = '';
        document.getElementById('assign-errors').style.display = 'none';
        openModal('assign-modal');
    }

    async function confirmAssign(e) {
        e.preventDefault();
        const errEl = document.getElementById('assign-errors');
        errEl.style.display = 'none';
        const masterId = document.getElementById('assign-master-id').value;

        const body = {
            user_id: document.getElementById('assign-user-id').value,
            label_rak: document.getElementById('assign-label-rak').value || null,
        };

        try {
            const res = await fetch(`{{ url('/stm-internal/master-devices') }}/${masterId}/assign`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(body)
            });
            const data = await res.json();

            if (!res.ok) {
                const msgs = Object.values(data.errors || {}).flat().join('<br>');
                errEl.innerHTML = msgs || data.message || 'Validation failed';
                errEl.style.display = 'flex';
                return;
            }

            closeModal('assign-modal');
            location.reload();
        } catch (err) {
            errEl.textContent = 'Network error, please try again.';
            errEl.style.display = 'flex';
        }
    }

    // UNASSIGN DEVICE FROM USER
    function unassignDevice(masterId, deviceId) {
        document.getElementById('unassign-id').value = masterId;
        document.getElementById('unassign-device-id').textContent = deviceId;
        openModal('unassign-modal');
    }

    async function confirmUnassign() {
        const id = document.getElementById('unassign-id').value;
        try {
            await fetch(`{{ url('/stm-internal/master-devices') }}/${id}/unassign`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                }
            });
            closeModal('unassign-modal');
            location.reload();
        } catch (err) {
            alert('Failed to unassign device.');
        }
    }
</script>
@endpush