@extends('layouts.admin')

@section('title', 'ข้อมูลการตาย - AOPOD')
@section('header_title', 'ข้อมูลการตาย (Death Information)')

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="glass-card">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <h5 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-skull text-danger me-2"></i> รายการข้อมูลการตาย</h5>
                    <span class="text-secondary small">ตารางแสดงรายละเอียดข้อมูลการตายและนำเข้าไฟล์จากระบบ Excel</span>
                </div>
                <button type="button" class="btn btn-danger px-4 py-2.5 fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#importExcelModal" style="border-radius: 12px; background: linear-gradient(135deg, #dc3545 0%, #ffc107 100%); border: none;">
                    <i class="fa-solid fa-file-excel me-2"></i> นำเข้าข้อมูลการตาย (Excel)
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 w-100" id="deathsTable" style="font-size: 0.9rem;">
                    <thead>
                        <tr class="text-secondary" style="font-size: 0.85rem;">
                            <th>PID</th>
                            <th>เพศ</th>
                            <th>อายุ (ปี)</th>
                            <th>วันเสียชีวิต</th>
                            <th>รหัสสำนักทะเบียน</th>
                            <th>รหัส รพ.</th>
                            <th>รหัสที่อยู่</th>
                            <th>สาเหตุ (ICD-10)</th>
                            <th>วันเกิด</th>
                            <th>สถานที่ตาย</th>
                            <th>ประเภท รพ.</th>
                            <th>รหัสจังหวัด</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deaths as $death)
                        <tr>
                            <td class="fw-bold text-dark">{{ $death->pid }}</td>
                            <td>
                                @if($death->sex == '1')
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-2.5 py-1.5 rounded-3">ชาย</span>
                                @elseif($death->sex == '2')
                                    <span class="badge bg-warning bg-opacity-10 text-dark px-2.5 py-1.5 rounded-3">หญิง</span>
                                @else
                                    <span class="badge bg-light text-secondary px-2.5 py-1.5 rounded-3">{{ $death->sex ?? '-' }}</span>
                                @endif
                            </td>
                            <td>{{ $death->age ?? '-' }}</td>
                            <td class="fw-semibold text-slate-700">
                                @if($death->ddate && $death->dmon && $death->dyear)
                                    {{ sprintf('%02d/%02d/%d', $death->ddate, $death->dmon, $death->dyear) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $death->drcode ?? '-' }}</td>
                            <td>{{ $death->hos_id ?? '-' }}</td>
                            <td>{{ $death->lccaattmm ?? '-' }}</td>
                            <td>
                                <span class="badge bg-danger bg-opacity-10 text-danger px-2.5 py-1.5 rounded-3 fw-bold">{{ $death->ncause ?? '-' }}</span>
                            </td>
                            <td>
                                @if($death->bdate && $death->bmon && $death->byear)
                                    {{ sprintf('%02d/%02d/%d', $death->bdate, $death->bmon, $death->byear) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $death->dplace ?? '-' }}</td>
                            <td>
                                @if($death->ghos == '1')
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-2">รพ.รัฐ</span>
                                @elseif($death->ghos == '3')
                                    <span class="badge bg-info bg-opacity-10 text-info px-2 py-1 rounded-2">รพ.เอกชน</span>
                                @else
                                    <span class="badge bg-light text-secondary px-2 py-1 rounded-2">นอก รพ.</span>
                                @endif
                            </td>
                            <td>{{ $death->codepro ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Import Modal --}}
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 22px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(15px); border: 1px solid rgba(220, 53, 69, 0.25);">
            <form id="importExcelForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h4 class="modal-title fw-bold" id="importExcelModalLabel" style="background: linear-gradient(135deg, #dc3545 0%, #ffc107 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">นำเข้าข้อมูลการตาย</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4 text-center">
                        <div class="p-4 bg-danger bg-opacity-10 text-danger rounded-4 d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; font-size: 2rem;">
                            <i class="fa-solid fa-file-excel"></i>
                        </div>
                        <h6 class="fw-bold text-dark">อัปโหลดไฟล์ Excel (.xlsx, .xls)</h6>
                        <p class="text-secondary small">กรุณาอัปโหลดไฟล์ข้อมูลการตายที่มีโครงสร้างข้อมูลตรงกับระบบ (อ่านข้อมูลจาก Sheet2)</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">เลือกไฟล์ Excel</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required style="border-radius: 12px; border-color: rgba(220, 53, 69, 0.25); padding: 0.6rem 0.8rem; box-shadow: none;">
                        <div class="form-text text-muted">ขนาดไฟล์สูงสุดไม่เกิน 20MB</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="submit" class="btn btn-danger w-100 py-2.5 fw-bold text-white shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #dc3545 0%, #ffc107 100%); border: none;">เริ่มนำเข้าข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable for Deaths Table
        $('#deathsTable').DataTable({
            language: {
                url: "{{ asset('assets/vendor/datatables/th.json') }}"
            },
            ordering: true,
            pageLength: 10
        });

        // Handle Import Form Submission
        $('#importExcelForm').on('submit', function(e) {
            e.preventDefault();
            
            let formData = new FormData(this);
            
            Swal.fire({
                title: 'กำลังนำเข้าข้อมูล...',
                html: 'โปรดรอสักครู่ ระบบกำลังประมวลผลไฟล์ Excel และอัปเดตฐานข้อมูล...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: "{{ route('admin.death-data.import') }}",
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.fire({
                        title: 'นำเข้าข้อมูลสำเร็จ!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonColor: '#18a573'
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                    let err = 'เกิดข้อผิดพลาดในการนำเข้าข้อมูล';
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
    });
</script>
@endpush
