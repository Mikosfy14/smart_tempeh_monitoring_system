@extends('layouts.admin')

@section('title', 'User Management — Smart Tempeh Monitoring')
@section('page-title', 'User Management')

@section('content')
<div class="stagger-children">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm" style="color: var(--color-text-secondary);">Manage user accounts and WhatsApp numbers</p>
        <button class="btn btn-primary" onclick="openModal('create-modal')" id="btn-register-user">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Register User
        </button>
    </div>

    {{-- Users Table --}}
    <div class="card-static overflow-x-auto">
        <table class="data-table table-data" id="users-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>WhatsApp</th>
                    <th>Devices</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="users-tbody">
                @forelse($users as $i => $user)
                <tr id="user-row-{{ $user->id }}">
                    <td data-label="#">{{ $i + 1 }}</td>
                    <td data-label="Name" class="font-medium">{{ $user->name }}</td>
                    <td data-label="Email">{{ $user->email }}</td>
                    <td data-label="WhatsApp" style="color: var(--color-accent-teal);">{{ $user->whatsapp_number ?? '—' }}</td>
                    <td data-label="Devices"><span class="badge badge-blue">{{ $user->devices_count }}</span></td>
                    <td data-label="Created" style="color: var(--color-text-secondary);">{{ $user->created_at->format('d/m/y') }}</td>
                    <td data-label="Actions">
                        <div class="flex items-center gap-1">
                            <button class="btn-icon" onclick="openEditModal({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->whatsapp_number }}')" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button class="btn-icon" style="color: var(--color-accent-red);" onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr id="no-users-row">
                    <td colspan="7" class="text-center py-8" style="color: var(--color-text-muted);">No users registered yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- CREATE USER MODAL --}}
<div class="modal-overlay" id="create-modal">
    <div class="modal-content">
        <div class="flex items-center justify-between mb-6">
            <h3 class="modal-title mb-0">Register New User</h3>
            <button class="btn-icon" onclick="closeModal('create-modal')">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="create-errors" class="alert alert-error mb-4" style="display: none;"></div>
        <form id="create-user-form" onsubmit="createUser(event)">
            <div class="mb-4">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-input" placeholder="Full name" required id="create-name">
            </div>
            <div class="mb-4">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" placeholder="user@example.com" required id="create-email">
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="Min 6 characters" required id="create-password">
            </div>
            <div class="mb-6">
                <label class="form-label">WhatsApp Number</label>
                <input type="text" name="whatsapp_number" class="form-input" placeholder="628xxxxxxxxxx" id="create-whatsapp">
            </div>
            <button type="submit" class="btn btn-primary w-full" id="btn-submit-create">Create Account</button>
        </form>
    </div>
</div>

{{-- EDIT USER MODAL --}}
<div class="modal-overlay" id="edit-modal">
    <div class="modal-content">
        <div class="flex items-center justify-between mb-6">
            <h3 class="modal-title mb-0">Edit User</h3>
            <button class="btn-icon" onclick="closeModal('edit-modal')">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="edit-errors" class="alert alert-error mb-4" style="display: none;"></div>
        <form id="edit-user-form" onsubmit="updateUser(event)">
            <input type="hidden" name="user_id" id="edit-user-id">
            <div class="mb-4">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-input" required id="edit-name">
            </div>
            <div class="mb-4">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-input" required id="edit-email">
            </div>
            <div class="mb-4">
                <label class="form-label">New Password <span style="color: var(--color-text-muted);">(leave blank to keep)</span></label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" id="edit-password">
            </div>
            <div class="mb-6">
                <label class="form-label">WhatsApp Number</label>
                <input type="text" name="whatsapp_number" class="form-input" placeholder="628xxxxxxxxxx" id="edit-whatsapp">
            </div>
            <button type="submit" class="btn btn-primary w-full" id="btn-submit-edit">Save Changes</button>
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
        <h3 class="text-lg font-bold mb-2">Delete User</h3>
        <p class="text-sm mb-6" style="color: var(--color-text-secondary);">Are you sure you want to delete <strong id="delete-user-name" class="text-white"></strong>? This action cannot be undone.</p>
        <input type="hidden" id="delete-user-id">
        <div class="flex gap-3">
            <button class="btn btn-secondary flex-1" onclick="closeModal('delete-modal')">Cancel</button>
            <button class="btn btn-danger flex-1" onclick="confirmDelete()" id="btn-confirm-delete">Delete</button>
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

    async function createUser(e) {
        e.preventDefault();
        const errEl = document.getElementById('create-errors');
        errEl.style.display = 'none';
        const submitBtn = document.getElementById('btn-submit-create');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Creating...';

        const body = {
            name: document.getElementById('create-name').value,
            email: document.getElementById('create-email').value,
            password: document.getElementById('create-password').value,
            whatsapp_number: document.getElementById('create-whatsapp').value || null,
        };

        try {
            const res = await fetch('{{ route("admin.users.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(body)
            });

            if (res.redirected) {
                window.location.href = res.url;
                return;
            }

            const data = await res.json();

            if (!res.ok) {
                const msgs = Object.values(data.errors || {}).flat().join('<br>');
                errEl.innerHTML = msgs || 'Validation failed';
                errEl.style.display = 'flex';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Create Account';
                return;
            }

            closeModal('create-modal');
            document.getElementById('create-user-form').reset();
            window.location.reload();
        } catch (err) {
            // If the request succeeded but reload interrupted parsing, just reload
            submitBtn.disabled = false;
            submitBtn.textContent = 'Create Account';
            window.location.reload();
        }
    }

    function openEditModal(id, name, email, whatsapp) {
        document.getElementById('edit-user-id').value = id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-email').value = email;
        document.getElementById('edit-whatsapp').value = whatsapp || '';
        document.getElementById('edit-password').value = '';
        document.getElementById('edit-errors').style.display = 'none';
        openModal('edit-modal');
    }

    async function updateUser(e) {
        e.preventDefault();
        const errEl = document.getElementById('edit-errors');
        errEl.style.display = 'none';
        const userId = document.getElementById('edit-user-id').value;

        const body = {
            name: document.getElementById('edit-name').value,
            email: document.getElementById('edit-email').value,
            whatsapp_number: document.getElementById('edit-whatsapp').value || null,
        };
        const pw = document.getElementById('edit-password').value;
        if (pw) body.password = pw;

        try {
            const res = await fetch(`{{ url('/stm-internal/users') }}/${userId}`, {
                method: 'PUT',
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
                errEl.innerHTML = msgs || 'Validation failed';
                errEl.style.display = 'flex';
                return;
            }

            closeModal('edit-modal');
            location.reload();
        } catch (err) {
            errEl.textContent = 'Network error, please try again.';
            errEl.style.display = 'flex';
        }
    }

    function deleteUser(id, name) {
        document.getElementById('delete-user-id').value = id;
        document.getElementById('delete-user-name').textContent = name;
        openModal('delete-modal');
    }

    async function confirmDelete() {
        const userId = document.getElementById('delete-user-id').value;
        try {
            await fetch(`{{ url('/stm-internal/users') }}/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                }
            });
            closeModal('delete-modal');
            location.reload();
        } catch (err) {
            alert('Failed to delete user.');
        }
    }
</script>
@endpush