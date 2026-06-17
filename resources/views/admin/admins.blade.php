@extends('layouts.admin')

@section('title', 'Admin Management — Rizhomatix')
@section('page-title', 'Manajemen Admin')

@section('content')
<div class="stagger-children">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm" style="color: var(--color-text-secondary);">Kelola akun administrator sistem</p>
        <button class="btn btn-primary" onclick="openModal('create-modal')" id="btn-add-admin">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Admin
        </button>
    </div>

    {{-- Admins Table --}}
    <div class="card-static overflow-x-auto">
        <table class="data-table table-data" id="admins-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="admins-tbody">
                @forelse($admins as $i => $admin)
                <tr id="admin-row-{{ $admin->id }}">
                    <td data-label="#">{{ $i + 1 }}</td>
                    <td data-label="Username" class="font-medium">{{ $admin->username }}</td>
                    <td data-label="Role">
                        @if($admin->is_master)
                            <span class="badge badge-amber">Master Admin</span>
                        @else
                            <span class="badge badge-blue">Admin</span>
                        @endif
                    </td>
                    <td data-label="Dibuat" style="color: var(--color-text-secondary);">{{ $admin->created_at->format('d/m/y H:i') }}</td>
                    <td data-label="Aksi">
                        <div class="flex items-center gap-1">
                            @if(!$admin->is_master || $admin->id === auth('admin')->id())
                            <button class="btn-icon" onclick="openEditModal({{ $admin->id }}, '{{ $admin->username }}')" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            @endif

                            @if(!$admin->is_master && $admin->id !== auth('admin')->id())
                            <button class="btn-icon" style="color: var(--color-accent-red);" onclick="deleteAdmin({{ $admin->id }}, '{{ $admin->username }}')" title="Hapus">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                            @endif

                            @if($admin->is_master && $admin->id !== auth('admin')->id())
                            <span class="text-xs" style="color: var(--color-text-muted);">—</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr id="no-admins-row">
                    <td colspan="5" class="text-center py-8" style="color: var(--color-text-muted);">Belum ada admin terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- CREATE ADMIN MODAL --}}
<div class="modal-overlay" id="create-modal">
    <div class="modal-content">
        <div class="flex items-center justify-between mb-6">
            <h3 class="modal-title mb-0">Tambah Admin Baru</h3>
            <button class="btn-icon" onclick="closeModal('create-modal')">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="create-errors" class="alert alert-error mb-4" style="display: none;"></div>
        <form id="create-admin-form" onsubmit="createAdmin(event)">
            <div class="mb-4">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" placeholder="Masukkan username" required id="create-username">
            </div>
            
            <div class="mb-6">
                <label class="form-label">Password</label>
                <div class="form-input-icon-wrapper">
                    <svg class="form-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    
                    <input type="password" name="password" class="form-input form-input-has-toggle" placeholder="Min 6 karakter" required id="create-password">
                    
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('create-password', this)" tabindex="-1" aria-label="Tampilkan password">
                        <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg class="eye-closed hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.024 10.024 0 014.13-5.246M8.82 8.82L4.7 4.7M9.88 9.88a3 3 0 104.24 4.24m2.07-2.07a9.962 9.962 0 012.33 3.95M21 12a9.964 9.964 0 00-3.21-6.88M3 3l18 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="mb-4 p-3 rounded-lg" style="background: rgba(96, 165, 250, 0.08); border: 1px solid rgba(96, 165, 250, 0.15);">
                <p class="text-xs" style="color: var(--color-text-secondary);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 inline-block mr-1" style="color: var(--color-accent-blue);" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Admin baru akan dibuat sebagai <strong>Admin Biasa</strong>. Hanya Master Admin yang dapat diatur melalui database.
                </p>
            </div>
            <button type="submit" class="btn btn-primary w-full" id="btn-submit-create">Buat Admin</button>
        </form>
    </div>
</div>

{{-- EDIT ADMIN MODAL --}}
<div class="modal-overlay" id="edit-modal">
    <div class="modal-content">
        <div class="flex items-center justify-between mb-6">
            <h3 class="modal-title mb-0">Edit Admin</h3>
            <button class="btn-icon" onclick="closeModal('edit-modal')">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="edit-errors" class="alert alert-error mb-4" style="display: none;"></div>
        <form id="edit-admin-form" onsubmit="updateAdmin(event)">
            <input type="hidden" name="admin_id" id="edit-admin-id">
            <div class="mb-4">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-input" required id="edit-username">
            </div>
            
            <div class="mb-6">
                <label class="form-label">Password Baru <span style="color: var(--color-text-muted);">(kosongkan jika tidak diubah)</span></label>
                <div class="form-input-icon-wrapper">
                    <svg class="form-input-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    
                    <input type="password" name="password" class="form-input form-input-has-toggle" placeholder="••••••••" id="edit-password">
                    
                    <button type="button" class="password-toggle-btn" onclick="togglePasswordVisibility('edit-password', this)" tabindex="-1" aria-label="Tampilkan password">
                        <svg class="eye-open" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg class="eye-closed hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.024 10.024 0 014.13-5.246M8.82 8.82L4.7 4.7M9.88 9.88a3 3 0 104.24 4.24m2.07-2.07a9.962 9.962 0 012.33 3.95M21 12a9.964 9.964 0 00-3.21-6.88M3 3l18 18" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-full" id="btn-submit-edit">Simpan Perubahan</button>
        </form>
    </div>
</div>

{{-- DELETE CONFIRM MODAL --}}
<div class="modal-overlay fixed inset-0 z-50 flex items-center justify-center bg-black/40" id="delete-modal">
    
    <div class="modal-content w-full max-w-sm bg-white rounded-2xl p-6 text-center shadow-xl">
        
        <div class="mx-auto flex items-center justify-center w-14 h-14 rounded-full bg-red-50 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
        </div>
        
        <h3 class="text-lg font-bold text-slate-800 mb-2">Hapus Admin</h3>
        <p class="text-sm text-slate-500 mb-6 leading-relaxed">
            Apakah Anda yakin ingin menghapus admin <strong id="delete-admin-name" class="font-semibold text-slate-700"></strong>? <br>
            Tindakan ini tidak dapat dibatalkan.
        </p>
        
        <input type="hidden" id="delete-admin-id">
        
        <div class="flex gap-3 w-full">
            <button class="flex-1 py-2.5 px-4 bg-white border border-slate-200 rounded-xl text-slate-700 font-semibold hover:bg-slate-50 transition-colors" onclick="closeModal('delete-modal')">
                Batal
            </button>
            <button class="flex-1 py-2.5 px-4 bg-red-50 border border-red-200 rounded-xl text-red-600 font-semibold hover:bg-red-100 transition-colors" onclick="confirmDelete()" id="btn-confirm-delete">
                Hapus
            </button>
        </div>
        
    </div>
</div>
@endsection

@push('scripts')
<script>
    const baseUrl = '{{ url("/stm-internal/admins") }}';

    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    async function createAdmin(e) {
        e.preventDefault();
        const errEl = document.getElementById('create-errors');
        errEl.style.display = 'none';
        const submitBtn = document.getElementById('btn-submit-create');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Membuat...';

        const body = {
            username: document.getElementById('create-username').value,
            password: document.getElementById('create-password').value,
        };

        try {
            const res = await fetch(baseUrl, {
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
                errEl.innerHTML = msgs || data.message || 'Validasi gagal';
                errEl.style.display = 'flex';
                submitBtn.disabled = false;
                submitBtn.textContent = 'Buat Admin';
                return;
            }

            closeModal('create-modal');
            document.getElementById('create-admin-form').reset();
            window.location.reload();
        } catch (err) {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Buat Admin';
            window.location.reload();
        }
    }

    function openEditModal(id, username) {
        document.getElementById('edit-admin-id').value = id;
        document.getElementById('edit-username').value = username;
        document.getElementById('edit-password').value = '';
        document.getElementById('edit-errors').style.display = 'none';
        openModal('edit-modal');
    }

    async function updateAdmin(e) {
        e.preventDefault();
        const errEl = document.getElementById('edit-errors');
        errEl.style.display = 'none';
        const adminId = document.getElementById('edit-admin-id').value;

        const body = {
            username: document.getElementById('edit-username').value,
        };
        const pw = document.getElementById('edit-password').value;
        if (pw) body.password = pw;

        try {
            const res = await fetch(`${baseUrl}/${adminId}`, {
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
                errEl.innerHTML = msgs || data.message || 'Validasi gagal';
                errEl.style.display = 'flex';
                return;
            }

            closeModal('edit-modal');
            location.reload();
        } catch (err) {
            errEl.textContent = 'Terjadi kesalahan jaringan, coba lagi.';
            errEl.style.display = 'flex';
        }
    }

    function deleteAdmin(id, username) {
        document.getElementById('delete-admin-id').value = id;
        document.getElementById('delete-admin-name').textContent = username;
        openModal('delete-modal');
    }

    async function confirmDelete() {
        const adminId = document.getElementById('delete-admin-id').value;
        const deleteBtn = document.getElementById('btn-confirm-delete');
        deleteBtn.disabled = true;
        deleteBtn.textContent = 'Menghapus...';

        try {
            const res = await fetch(`${baseUrl}/${adminId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': window.csrfToken,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();

            if (!res.ok) {
                closeModal('delete-modal');
                alert(data.message || 'Gagal menghapus admin.');
                deleteBtn.disabled = false;
                deleteBtn.textContent = 'Hapus';
                return;
            }

            closeModal('delete-modal');
            location.reload();
        } catch (err) {
            alert('Gagal menghapus admin.');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Hapus';
        }
    }

    function togglePasswordVisibility(inputId, btn) {
        const input = document.getElementById(inputId);
        if (!input) return;
        
        const eyeOpen = btn.querySelector('.eye-open');
        const eyeClosed = btn.querySelector('.eye-closed');
        
        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
        } else {
            input.type = 'password';
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
        }
    }
</script>
@endpush
