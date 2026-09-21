@extends('layouts.admin')

@section('title', 'จัดการสมาชิก - AOPOD')
@section('header_title', 'จัดการสมาชิก (User Management)')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="fw-bold mb-1"><i class="fa-solid fa-users text-green me-2"></i> รายชื่อสมาชิกในระบบ</h5>
                    <span class="text-secondary small">จัดการบัญชีผู้ใช้งาน สิทธิ์การเข้าถึง และการเชื่อมโยง Provider ID</span>
                </div>
                <button type="button" class="btn btn-primary px-4 py-2 fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal" style="border-radius: 12px; background: linear-gradient(135deg, #0d6efd 0%, #21c08b 100%); border: none;">
                    <i class="fa-solid fa-user-plus me-2"></i> เพิ่มสมาชิกใหม่
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="usersTable">
                    <thead>
                        <tr class="text-secondary" style="font-size: 0.9rem;">
                            <th>ID</th>
                            <th>ชื่อ-นามสกุล / ตำแหน่ง</th>
                            <th>หน่วยบริการ (รพ.)</th>
                            <th>อีเมล / เลขบัตรประชาชน (CID)</th>
                            <th>Provider ID</th>
                            <th>สิทธิ์การใช้งาน</th>
                            <th>สถานะ</th>
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
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                        @if($user->position)
                                            <small class="text-secondary"><i class="fa-solid fa-id-badge me-1"></i>{{ $user->position }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($user->hospital)
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-2.5 py-1.5 rounded-3 fw-semibold" style="font-size: 0.85rem;">
                                        <i class="fa-solid fa-hospital me-1"></i> {{ $user->hospital->name }} ({{ $user->hospcode }})
                                    </span>
                                @elseif($user->hospcode)
                                    <span class="badge bg-light text-secondary border px-2.5 py-1.5 rounded-3" style="font-size: 0.85rem;">
                                        {{ $user->hospcode }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <div><i class="fa-regular fa-envelope me-1 text-secondary small"></i>{{ $user->email }}</div>
                                @if($user->cid)
                                    <small class="text-secondary"><i class="fa-solid fa-address-card me-1 text-success"></i><code>{{ $user->cid }}</code></small>
                                @else
                                    <small class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>ยังไม่ระบุ CID</small>
                                @endif
                            </td>
                            <td>
                                @if($user->provider_id)
                                    <span class="badge bg-success bg-opacity-10 text-success px-2.5 py-1.5 rounded-3" style="font-size: 0.82rem;" title="เชื่อมโยงแล้ว">
                                        <i class="fa-solid fa-check-circle me-1"></i> {{ $user->provider_id }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-secondary border px-2.5 py-1.5 rounded-3" style="font-size: 0.82rem;">
                                        ยังไม่ผูก Provider ID
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge bg-success bg-opacity-10 text-success px-2.5 py-1 rounded-3 mb-1" style="font-size: 0.82rem;">Admin</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-2.5 py-1 rounded-3 mb-1" style="font-size: 0.82rem;">User</span>
                                @endif
                                <div>
                                    @if($user->allow_death)
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-2 me-1" style="font-size: 0.75rem;" title="เข้าถึงข้อมูลการตาย"><i class="fa-solid fa-skull"></i> ตาย</span>
                                    @endif
                                    @if($user->allow_death_dashboard)
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-2 me-1" style="font-size: 0.75rem;" title="แดชบอร์ดการตาย"><i class="fa-solid fa-chart-line"></i> DB-ตาย</span>
                                    @endif
                                    @if($user->allow_birth)
                                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-2 me-1" style="font-size: 0.75rem;" title="เข้าถึงข้อมูลการเกิด"><i class="fa-solid fa-baby"></i> เกิด</span>
                                    @endif
                                    @if($user->allow_birth_dashboard)
                                        <span class="badge bg-info bg-opacity-10 text-info px-2 py-1 rounded-2" style="font-size: 0.75rem;" title="แดชบอร์ดการเกิด"><i class="fa-solid fa-chart-line"></i> DB-เกิด</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($user->isActive())
                                    <span class="badge bg-success text-white px-2.5 py-1 rounded-pill" style="font-size: 0.8rem;">เปิดใช้งาน</span>
                                @else
                                    <span class="badge bg-secondary text-white px-2.5 py-1 rounded-pill" style="font-size: 0.8rem;">ปิดใช้งาน</span>
                                @endif
                            </td>
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
                                            data-hospcode="{{ $user->hospcode }}"
                                            data-position="{{ $user->position }}"
                                            data-cid="{{ $user->cid }}"
                                            data-active="{{ $user->active }}"
                                            data-allow-death="{{ $user->allow_death }}"
                                            data-allow-death-dashboard="{{ $user->allow_death_dashboard }}"
                                            data-allow-birth="{{ $user->allow_birth }}"
                                            data-allow-birth-dashboard="{{ $user->allow_birth_dashboard }}"
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
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 22px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(15px); border: 1px solid rgba(33, 192, 139, 0.25);">
            <form id="addUserForm">
                @csrf
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h4 class="modal-title fw-bold" id="addUserModalLabel" style="background: linear-gradient(135deg, #0d6efd 0%, #21c08b 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">เพิ่มสมาชิกใหม่</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required placeholder="กรอกชื่อ-นามสกุล" style="border-radius: 12px; padding: 0.6rem 0.8rem;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">ตำแหน่งงาน</label>
                            <input type="text" name="position" class="form-control" placeholder="เช่น พยาบาลวิชาชีพ, นายแพทย์" style="border-radius: 12px; padding: 0.6rem 0.8rem;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">หน่วยบริการ / โรงพยาบาล</label>
                            <select name="hospcode" class="form-select" style="border-radius: 12px; padding: 0.6rem 0.8rem;">
                                <option value="">-- ไม่ระบุ --</option>
                                @foreach($hospitals as $hosp)
                                    <option value="{{ $hosp->hospcode }}">{{ $hosp->hospcode }} - {{ $hosp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">เลขบัตรประจำตัวประชาชน (CID 13 หลัก)</label>
                            <input type="text" name="cid" class="form-control" maxlength="13" placeholder="ระบุเฉพาะตัวเลข 13 หลักสำหรับ SSO" oninput="this.value = this.value.replace(/[^0-9]/g, '')" style="border-radius: 12px; padding: 0.6rem 0.8rem;">
                            <div class="form-text text-muted">จำเป็นสำหรับเข้าสู่ระบบด้วย Provider ID / Health ID</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">อีเมล / Username <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required placeholder="example@email.com" oninput="this.value = this.value.replace(/\s/g, '')" style="border-radius: 12px; padding: 0.6rem 0.8rem;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">รหัสผ่าน <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required placeholder="อย่างน้อย 8 ตัวอักษร" style="border-radius: 12px; padding: 0.6rem 0.8rem;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">สิทธิ์การใช้งาน <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required style="border-radius: 12px; padding: 0.6rem 0.8rem;">
                                <option value="user" selected>User (ผู้ใช้ทั่วไป)</option>
                                <option value="admin">Admin (ผู้ดูแลระบบ)</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-center pt-3">
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" name="active" id="addUserActive" value="Y" checked>
                                <label class="form-check-label fw-semibold text-secondary fs-6 ms-2" for="addUserActive">เปิดใช้งานบัญชีนี้ (Active)</label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-lock-open text-primary me-2"></i> สิทธิ์การเข้าถึงข้อมูลพิเศษ</h6>
                    
                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="allow_death" id="addUserAllowDeath" value="1">
                                <label class="form-check-label fw-semibold text-secondary small" for="addUserAllowDeath">เข้าถึงข้อมูลการตาย (allow_death)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="allow_death_dashboard" id="addUserAllowDeathDashboard" value="1">
                                <label class="form-check-label fw-semibold text-secondary small" for="addUserAllowDeathDashboard">เข้าถึงแดชบอร์ดการตาย (allow_death_dashboard)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="allow_birth" id="addUserAllowBirth" value="1">
                                <label class="form-check-label fw-semibold text-secondary small" for="addUserAllowBirth">เข้าถึงข้อมูลการเกิด (allow_birth)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="allow_birth_dashboard" id="addUserAllowBirthDashboard" value="1">
                                <label class="form-check-label fw-semibold text-secondary small" for="addUserAllowBirthDashboard">เข้าถึงแดชบอร์ดการเกิด (allow_birth_dashboard)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold text-white shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #0d6efd 0%, #21c08b 100%); border: none;">บันทึกข้อมูลสมาชิก</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit User Modal --}}
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 22px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(15px); border: 1px solid rgba(13, 110, 253, 0.25);">
            <form id="editUserForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="editUserId">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h4 class="modal-title fw-bold" id="editUserModalLabel" style="background: linear-gradient(135deg, #0d6efd 0%, #0d6efd 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">แก้ไขข้อมูลสมาชิก</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editUserName" class="form-control" required placeholder="กรอกชื่อ-นามสกุล" style="border-radius: 12px; padding: 0.6rem 0.8rem;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">ตำแหน่งงาน</label>
                            <input type="text" name="position" id="editUserPosition" class="form-control" placeholder="เช่น พยาบาลวิชาชีพ, นายแพทย์" style="border-radius: 12px; padding: 0.6rem 0.8rem;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">หน่วยบริการ / โรงพยาบาล</label>
                            <select name="hospcode" id="editUserHospcode" class="form-select" style="border-radius: 12px; padding: 0.6rem 0.8rem;">
                                <option value="">-- ไม่ระบุ --</option>
                                @foreach($hospitals as $hosp)
                                    <option value="{{ $hosp->hospcode }}">{{ $hosp->hospcode }} - {{ $hosp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">เลขบัตรประจำตัวประชาชน (CID 13 หลัก)</label>
                            <input type="text" name="cid" id="editUserCid" class="form-control" maxlength="13" placeholder="ระบุเฉพาะตัวเลข 13 หลักสำหรับ SSO" oninput="this.value = this.value.replace(/[^0-9]/g, '')" style="border-radius: 12px; padding: 0.6rem 0.8rem;">
                            <div class="form-text text-muted">จำเป็นสำหรับเข้าสู่ระบบด้วย Provider ID / Health ID</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">อีเมล / Username <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="editUserEmail" class="form-control" required placeholder="example@email.com" oninput="this.value = this.value.replace(/\s/g, '')" style="border-radius: 12px; padding: 0.6rem 0.8rem;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">รหัสผ่านใหม่ (ปล่อยว่างหากไม่ต้องการเปลี่ยน)</label>
                            <input type="password" name="password" class="form-control" placeholder="ปล่อยว่างหากไม่ต้องการแก้ไข" style="border-radius: 12px; padding: 0.6rem 0.8rem;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">สิทธิ์การใช้งาน <span class="text-danger">*</span></label>
                            <select name="role" id="editUserRole" class="form-select" required style="border-radius: 12px; padding: 0.6rem 0.8rem;">
                                <option value="user">User (ผู้ใช้ทั่วไป)</option>
                                <option value="admin">Admin (ผู้ดูแลระบบ)</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-center pt-3">
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" name="active" id="editUserActive" value="Y">
                                <label class="form-check-label fw-semibold text-secondary fs-6 ms-2" for="editUserActive">เปิดใช้งานบัญชีนี้ (Active)</label>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-lock-open text-primary me-2"></i> สิทธิ์การเข้าถึงข้อมูลพิเศษ</h6>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="allow_death" id="editUserAllowDeath" value="1">
                                <label class="form-check-label fw-semibold text-secondary small" for="editUserAllowDeath">เข้าถึงข้อมูลการตาย (allow_death)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="allow_death_dashboard" id="editUserAllowDeathDashboard" value="1">
                                <label class="form-check-label fw-semibold text-secondary small" for="editUserAllowDeathDashboard">เข้าถึงแดชบอร์ดการตาย (allow_death_dashboard)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="allow_birth" id="editUserAllowBirth" value="1">
                                <label class="form-check-label fw-semibold text-secondary small" for="editUserAllowBirth">เข้าถึงข้อมูลการเกิด (allow_birth)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="allow_birth_dashboard" id="editUserAllowBirthDashboard" value="1">
                                <label class="form-check-label fw-semibold text-secondary small" for="editUserAllowBirthDashboard">เข้าถึงแดชบอร์ดการเกิด (allow_birth_dashboard)</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold text-white shadow-sm" style="border-radius: 12px; background: #0d6efd; border: none;">อัปเดตข้อมูลสมาชิก</button>
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
                { orderable: false, targets: 7 } // Disable ordering on Action column
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
                url: "{{ route('manage.users.create') }}",
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
            const hospcode = $(this).data('hospcode') || '';
            const position = $(this).data('position') || '';
            const cid = $(this).data('cid') || '';
            const active = $(this).data('active');
            const allowDeath = $(this).data('allow-death');
            const allowDeathDashboard = $(this).data('allow-death-dashboard');
            const allowBirth = $(this).data('allow-birth');
            const allowBirthDashboard = $(this).data('allow-birth-dashboard');

            $('#editUserId').val(id);
            $('#editUserName').val(name);
            $('#editUserEmail').val(email);
            $('#editUserRole').val(role);
            $('#editUserHospcode').val(hospcode);
            $('#editUserPosition').val(position);
            $('#editUserCid').val(cid);
            $('#editUserActive').prop('checked', active === 'Y' || active === '1');
            $('#editUserAllowDeath').prop('checked', allowDeath == 1);
            $('#editUserAllowDeathDashboard').prop('checked', allowDeathDashboard == 1);
            $('#editUserAllowBirth').prop('checked', allowBirth == 1);
            $('#editUserAllowBirthDashboard').prop('checked', allowBirthDashboard == 1);

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
                url: "{{ route('manage.users.update', ':id') }}".replace(':id', id),
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
                        url: "{{ route('manage.users.delete', ':id') }}".replace(':id', id),
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
                        url: "{{ route('manage.users.reset-password', ':id') }}".replace(':id', id),
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
