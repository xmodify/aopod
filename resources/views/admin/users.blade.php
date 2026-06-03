@extends('layouts.admin')

@section('title', 'จัดการสมาชิก - AOPOD')
@section('header_title', 'จัดการสมาชิก (User Management)')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0"><i class="fa-solid fa-users text-green me-2"></i> รายชื่อสมาชิกในระบบ</h5>
                <button type="button" class="btn btn-primary px-4 py-2 fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal" style="border-radius: 12px; background: linear-gradient(135deg, #0d6efd 0%, #21c08b 100%); border: none;">
                    <i class="fa-solid fa-user-plus me-2"></i> เพิ่มสมาชิกใหม่
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="usersTable">
                    <thead>
                        <tr class="text-secondary" style="font-size: 0.9rem;">
                            <th>ID</th>
                            <th>ชื่อ-นามสกุล</th>
                            <th>อีเมล / Username</th>
                            <th>สิทธิ์การใช้งาน</th>
                            <th>สร้างเมื่อ</th>
                            <th class="text-end">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="fw-bold">#{{ $user->id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="p-2 bg-light rounded-circle text-center d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; color: #475569;">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <span class="fw-semibold text-dark">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-3" style="font-size: 0.85rem;">Admin</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-3" style="font-size: 0.85rem;">User</span>
                                @endif
                            </td>
                            <td class="text-secondary small">{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}</td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-light border reset-password-btn" 
                                            data-id="{{ $user->id }}" 
                                            data-name="{{ $user->name }}" 
                                            style="border-radius: 8px;"
                                            title="รีเซ็ตรหัสผ่านเป็น 12345678"
                                            aria-label="Reset Password">
                                        <i class="fa-solid fa-key text-warning"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border edit-user-btn" 
                                            data-id="{{ $user->id }}" 
                                            data-name="{{ $user->name }}" 
                                            data-email="{{ $user->email }}" 
                                            data-role="{{ $user->role }}"
                                            style="border-radius: 8px;"
                                            aria-label="Edit User">
                                        <i class="fa-solid fa-user-pen text-primary"></i>
                                    </button>
                                    @if($user->id !== Auth::user()->id)
                                    <button type="button" class="btn btn-sm btn-light border delete-user-btn" 
                                            data-id="{{ $user->id }}" 
                                            data-name="{{ $user->name }}"
                                            style="border-radius: 8px;"
                                            aria-label="Delete User">
                                        <i class="fa-solid fa-trash-can text-danger"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add User Modal --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 22px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(15px); border: 1px solid rgba(33, 192, 139, 0.25);">
            <form id="addUserForm">
                @csrf
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h4 class="modal-title fw-bold" id="addUserModalLabel" style="background: linear-gradient(135deg, #0d6efd 0%, #21c08b 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">เพิ่มสมาชิกใหม่</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">ชื่อ-นามสกุล</label>
                        <input type="text" name="name" class="form-control" required placeholder="กรอกชื่อ-นามสกุล" style="border-radius: 12px; border-color: rgba(33, 192, 139, 0.25); padding: 0.6rem 0.8rem; box-shadow: none;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">อีเมล / Username</label>
                        <input type="email" name="email" class="form-control" required placeholder="example@email.com" style="border-radius: 12px; border-color: rgba(33, 192, 139, 0.25); padding: 0.6rem 0.8rem; box-shadow: none;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">รหัสผ่าน</label>
                        <input type="password" name="password" class="form-control" required placeholder="อย่างน้อย 8 ตัวอักษร" style="border-radius: 12px; border-color: rgba(33, 192, 139, 0.25); padding: 0.6rem 0.8rem; box-shadow: none;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">สิทธิ์การใช้งาน</label>
                        <select name="role" class="form-select" required style="border-radius: 12px; border-color: rgba(33, 192, 139, 0.25); padding: 0.6rem 0.8rem; box-shadow: none;">
                            <option value="user" selected>User (ผู้ใช้ทั่วไป)</option>
                            <option value="admin">Admin (ผู้ดูแลระบบ)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold text-white shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #0d6efd 0%, #21c08b 100%); border: none;">บันทึกข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit User Modal --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 22px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(15px); border: 1px solid rgba(13, 110, 253, 0.25);">
            <form id="editUserForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="editUserId">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h4 class="modal-title fw-bold" id="editUserModalLabel" style="background: linear-gradient(135deg, #0d6efd 0%, #0d6efd 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">แก้ไขข้อมูลสมาชิก</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">ชื่อ-นามสกุล</label>
                        <input type="text" name="name" id="editUserName" class="form-control" required placeholder="กรอกชื่อ-นามสกุล" style="border-radius: 12px; border-color: rgba(13, 110, 253, 0.25); padding: 0.6rem 0.8rem; box-shadow: none;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">อีเมล / Username</label>
                        <input type="email" name="email" id="editUserEmail" class="form-control" required placeholder="example@email.com" style="border-radius: 12px; border-color: rgba(13, 110, 253, 0.25); padding: 0.6rem 0.8rem; box-shadow: none;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">รหัสผ่านใหม่ (ปล่อยว่างหากไม่ต้องการเปลี่ยน)</label>
                        <input type="password" name="password" class="form-control" placeholder="ปล่อยว่างหากไม่ต้องการแก้ไข" style="border-radius: 12px; border-color: rgba(13, 110, 253, 0.25); padding: 0.6rem 0.8rem; box-shadow: none;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">สิทธิ์การใช้งาน</label>
                        <select name="role" id="editUserRole" class="form-select" required style="border-radius: 12px; border-color: rgba(13, 110, 253, 0.25); padding: 0.6rem 0.8rem; box-shadow: none;">
                            <option value="user">User (ผู้ใช้ทั่วไป)</option>
                            <option value="admin">Admin (ผู้ดูแลระบบ)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold text-white shadow-sm" style="border-radius: 12px; background: #0d6efd; border: none;">อัปเดตข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#usersTable').DataTable({
            language: {
                url: "{{ asset('assets/vendor/datatables/th.json') }}"
            },
            ordering: true,
            pageLength: 10,
            columnDefs: [
                { orderable: false, targets: 5 } // Disable ordering on Action column
            ]
        });

        // Handle Add User Form Submission
        $('#addUserForm').on('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'กำลังบันทึกข้อมูล...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('admin.users.create') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        title: 'สำเร็จ!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#18a573'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let err = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        err = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        text: err,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        });

        // Open Edit Modal and fill data
        $('.edit-user-btn').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const email = $(this).data('email');
            const role = $(this).data('role');

            $('#editUserId').val(id);
            $('#editUserName').val(name);
            $('#editUserEmail').val(email);
            $('#editUserRole').val(role);

            $('#editUserModal').modal('show');
        });

        // Handle Edit User Form Submission
        $('#editUserForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#editUserId').val();
            Swal.fire({
                title: 'กำลังอัปเดตข้อมูล...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: `/admin/users/${id}`,
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire({
                        title: 'สำเร็จ!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#18a573'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let err = 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล';
                    if(xhr.responseJSON && xhr.responseJSON.message) {
                        err = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'เกิดข้อผิดพลาด',
                        text: err,
                        icon: 'error',
                        confirmButtonColor: '#dc3545'
                    });
                }
            });
        });

        // Handle Delete User Click
        $('.delete-user-btn').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            Swal.fire({
                title: 'ต้องการลบสมาชิกใช่หรือไม่?',
                text: `คุณกำลังจะลบสมาชิก "${name}" ออกจากระบบ`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, ลบเลย',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังลบข้อมูล...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `/admin/users/${id}`,
                        method: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'ลบสำเร็จ!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#18a573'
                            }).then(() => {
                                location.reload();
                            });
                        },
                        error: function(xhr) {
                            let err = 'เกิดข้อผิดพลาดในการลบข้อมูล';
                            if(xhr.responseJSON && xhr.responseJSON.message) {
                                err = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                title: 'เกิดข้อผิดพลาด',
                                text: err,
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                }
            });
        });

        // Handle Reset Password Click
        $('.reset-password-btn').on('click', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');

            Swal.fire({
                title: 'ยืนยันการรีเซ็ตรหัสผ่าน?',
                text: `คุณต้องการรีเซ็ตรหัสผ่านของ "${name}" เป็น "12345678" ใช่หรือไม่?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, รีเซ็ตเลย',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังรีเซ็ตรหัสผ่าน...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: `/admin/users/${id}/reset-password`,
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire({
                                title: 'สำเร็จ!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonColor: '#18a573'
                            });
                        },
                        error: function(xhr) {
                            let err = 'เกิดข้อผิดพลาดในการรีเซ็ตรหัสผ่าน';
                            if(xhr.responseJSON && xhr.responseJSON.message) {
                                err = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                title: 'เกิดข้อผิดพลาด',
                                text: err,
                                icon: 'error',
                                confirmButtonColor: '#dc3545'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
