@extends('layouts.admin')

@section('title', 'ข้อมูลการเกิด - AOPOD')
@section('header_title', 'ข้อมูลการเกิด (Birth Information)')

@push('styles')
{{-- Load Chart.js and DataLabels plugin from local assets --}}
<script src="{{ asset('assets/vendor/chart.js/chart.umd.min.js') }}"></script>
<script src="{{ asset('assets/vendor/chartjs-plugin-datalabels/chartjs-plugin-datalabels.min.js') }}"></script>
@endpush

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="glass-card">
            {{-- Global Actions Header --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <form method="GET" action="{{ route('manage.birth-data.index') }}" id="fiscalYearForm" class="d-flex align-items-center gap-2">
                        <label class="fw-bold text-secondary mb-0" style="font-size: 0.9rem; white-space: nowrap;">เลือกปี พ.ศ.</label>
                        <select name="fiscal_year" class="form-select py-2" onchange="document.getElementById('fiscalYearForm').submit();" style="border-radius: 12px; border-color: rgba(33, 192, 139, 0.25); box-shadow: none; min-width: 140px;">
                            @foreach($fiscalYears as $year)
                                <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>ปี พ.ศ. {{ $year < 2400 ? $year + 543 : $year }}</option>
                            @endforeach
                            @if(empty($fiscalYears))
                                <option value="{{ $selectedYear }}">ปี พ.ศ. {{ $selectedYear < 2400 ? $selectedYear + 543 : $selectedYear }}</option>
                            @endif
                        </select>
                    </form>
                </div>
                @if(auth()->user()->canAccessBirth())
                <div>
                    <button type="button" class="btn btn-primary px-4 py-2.5 fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#importExcelModal" style="border-radius: 12px; background: linear-gradient(135deg, #0d6efd 0%, #21c08b 100%); border: none;">
                        <i class="fa-solid fa-file-excel me-2"></i> นำเข้าข้อมูลการเกิด (Excel)
                    </button>
                </div>
                @endif
            </div>

            {{-- Tab Navigation --}}
            <ul class="nav nav-pills mb-4 pb-2 border-bottom" id="birthTab" role="tablist" style="gap: 10px;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold px-4 py-2.5 d-flex align-items-center gap-2" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard-pane" type="button" role="tab" aria-controls="dashboard-pane" aria-selected="true" style="border-radius: 12px; transition: all 0.2s;">
                        <i class="fa-solid fa-chart-line"></i> แดชบอร์ดสถิติ
                    </button>
                </li>
                @if(auth()->user()->canAccessBirth())
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold px-4 py-2.5 d-flex align-items-center gap-2" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-pane" type="button" role="tab" aria-controls="list-pane" aria-selected="false" style="border-radius: 12px; transition: all 0.2s;">
                        <i class="fa-solid fa-list"></i> รายการข้อมูลการเกิด
                    </button>
                </li>
                @endif
            </ul>

            {{-- Tab Content --}}
            <div class="tab-content" id="birthTabContent">
                {{-- Tab 1: แดชบอร์ดสถิติ --}}
                <div class="tab-pane fade show active" id="dashboard-pane" role="tabpanel" aria-labelledby="dashboard-tab">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-chart-line text-green me-2"></i> แดชบอร์ดวิเคราะห์ข้อมูลการเกิด</h5>
                            <span class="text-secondary small">วิเคราะห์ข้อมูลการเกิดรายเดือน แยกตามอำเภอ </span>
                        </div>
                    </div>

                    {{-- Chart Canvas --}}
                    <div class="p-4 bg-white rounded-4 border mb-4 shadow-sm" style="min-height: 400px; position: relative;">
                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-chart-bar text-primary me-2"></i> สถิติการเกิดรายเดือน แยกตามอำเภอทั้ง 7 แห่ง (ปี พ.ศ. {{ $selectedYear < 2400 ? $selectedYear + 543 : $selectedYear }})</h6>
                        <div style="height: 380px; width: 100%;">
                            <canvas id="birthChart"></canvas>
                        </div>
                    </div>

                    <div class="p-4 bg-white rounded-4 border shadow-sm mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-table text-secondary me-2"></i> ตารางสรุปจำนวนคนเกิดรายเดือน แยกตามอำเภอ (ปี พ.ศ. {{ $selectedYear < 2400 ? $selectedYear + 543 : $selectedYear }})</h6>
                            <button type="button" class="btn btn-sm btn-outline-success fw-bold px-3 py-1.5 d-flex align-items-center gap-1" onclick="exportTableToExcel('monthlyBirthTable', 'monthly_births_by_district')" style="border-radius: 10px;">
                                <i class="fa-solid fa-file-excel"></i> Excel
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0 text-center" id="monthlyBirthTable" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 20%; text-align: left;">อำเภอ</th>
                                        @php
                                            $monthsLabels = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
                                            $monthsKeys = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
                                            $districtsList = ['3701', '3702', '3703', '3704', '3705', '3706', '3707'];
                                        @endphp
                                        @foreach($monthsLabels as $label)
                                            <th>{{ $label }}</th>
                                        @endforeach
                                        <th class="table-dark text-white">รวม</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $grandTotal = 0;
                                        $monthlyTotals = array_fill_keys($monthsKeys, 0);
                                    @endphp
                                    @foreach($districtsList as $dist)
                                        @php
                                            $rowTotal = 0;
                                        @endphp
                                        <tr>
                                            <td class="fw-bold text-dark text-start">{{ $districtNames[$dist] }}</td>
                                            @foreach($monthsKeys as $m)
                                                @php
                                                    $val = $chartData[$m][$dist] ?? 0;
                                                    $rowTotal += $val;
                                                    $monthlyTotals[$m] += $val;
                                                @endphp
                                                <td class="{{ $val > 0 ? 'fw-bold text-primary' : 'text-muted' }}">{{ $val }}</td>
                                            @endforeach
                                            <td class="fw-bold bg-light">{{ $rowTotal }}</td>
                                            @php
                                                $grandTotal += $rowTotal;
                                            @endphp
                                        </tr>
                                    @endforeach
                                    <tr class="table-light fw-bold text-dark">
                                        <td class="text-start">รวมทั้งหมด</td>
                                        @foreach($monthsKeys as $m)
                                            <td>{{ $monthlyTotals[$m] }}</td>
                                        @endforeach
                                        <td class="table-dark text-white">{{ $grandTotal }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                @if(auth()->user()->canAccessBirth())
                {{-- Tab 2: รายการข้อมูลการเกิด --}}
                <div class="tab-pane fade" id="list-pane" role="tabpanel" aria-labelledby="list-tab">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-baby text-green me-2"></i> รายการข้อมูลการเกิด</h5>
                            <span class="text-secondary small">ตารางแสดงรายละเอียดข้อมูลการเกิด ประจำปี พ.ศ. {{ $selectedYear < 2400 ? $selectedYear + 543 : $selectedYear }}</span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 w-100" id="birthsTable" style="font-size: 0.9rem;">
                            <thead>
                                <tr class="text-secondary" style="font-size: 0.85rem;">
                                    <th>จังหวัด</th>
                                    <th>อำเภอ</th>
                                    <th>ตำบล</th>
                                    <th>เพศ</th>
                                    <th>วันเกิด</th>
                                    <th>สัญชาติ</th>
                                    <th>น้ำหนัก (กรัม)</th>
                                    <th>อายุแม่ (ปี)</th>
                                    <th>เขต</th>
                                    <th>ที่อยู่แม่</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($births as $birth)
                                <tr>
                                    <td>{{ $birth->prov ?? '-' }}</td>
                                    <td>{{ $birth->amp ?? '-' }}</td>
                                    <td>{{ $birth->tb ?? '-' }}</td>
                                    <td>
                                        @if($birth->sex == '1')
                                            <span class="badge bg-primary bg-opacity-10 text-primary px-2.5 py-1.5 rounded-3">ชาย</span>
                                        @elseif($birth->sex == '2')
                                            <span class="badge bg-warning bg-opacity-10 text-dark px-2.5 py-1.5 rounded-3">หญิง</span>
                                        @else
                                            <span class="badge bg-light text-secondary px-2.5 py-1.5 rounded-3">{{ $birth->sex ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="fw-semibold text-slate-700">
                                        @if($birth->bdate && $birth->bmon && $birth->byear)
                                            {{ sprintf('%02d/%02d/%d', $birth->bdate, $birth->bmon, $birth->byear < 2400 ? $birth->byear + 543 : $birth->byear) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $birth->nat ?? '-' }}</td>
                                    <td>{{ $birth->weight ? number_format($birth->weight) : '-' }}</td>
                                    <td>{{ $birth->mage ?? '-' }}</td>
                                    <td>{{ $birth->ket ?? '-' }}</td>
                                    <td class="text-truncate" style="max-width: 200px;" title="{{ $birth->maddr }}">{{ $birth->maddr ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Import Modal --}}
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-labelledby="importExcelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 22px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(15px); border: 1px solid rgba(13, 110, 253, 0.25);">
            <form id="importExcelForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h4 class="modal-title fw-bold" id="importExcelModalLabel" style="background: linear-gradient(135deg, #0d6efd 0%, #21c08b 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">นำเข้าข้อมูลการเกิด</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4 text-center">
                        <div class="p-4 bg-primary bg-opacity-10 text-primary rounded-4 d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; font-size: 2rem;">
                            <i class="fa-solid fa-file-excel"></i>
                        </div>
                        <h6 class="fw-bold text-dark">อัปโหลดไฟล์ Excel (.xlsx, .xls)</h6>
                        <p class="text-secondary small">กรุณาอัปโหลดไฟล์ข้อมูลการเกิดที่มีโครงสร้างข้อมูลตรงกับระบบ (อ่านข้อมูลจาก Sheet2)</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">เลือกไฟล์ Excel</label>
                        <input type="file" name="file" class="form-control" accept=".xlsx, .xls, .csv" required style="border-radius: 12px; border-color: rgba(33, 192, 139, 0.25); padding: 0.6rem 0.8rem; box-shadow: none;">
                        <div class="form-text text-muted">ขนาดไฟล์สูงสุดไม่เกิน 20MB</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold text-white shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #0d6efd 0%, #21c08b 100%); border: none;">เริ่มนำเข้าข้อมูล</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable for Births Table if exists
        if ($('#birthsTable').length) {
            $('#birthsTable').DataTable({
                language: {
                    url: "{{ asset('assets/vendor/datatables/th.json') }}"
                },
                ordering: true,
                pageLength: 10,
                initComplete: function() {
                    var excelBtn = $('<button type="button" class="btn btn-sm btn-outline-success fw-bold px-3 py-1.5 ms-2 d-inline-flex align-items-center gap-1" onclick="exportTableToExcel(\'birthsTable\', \'births_list\')" style="border-radius: 10px;"><i class="fa-solid fa-file-excel"></i> Excel</button>');
                    $('#birthsTable_filter').append(excelBtn);
                }
            });
        }

        // Initialize Chart.js stacked bar chart
        const chartData = @json($chartData);
        const districtNames = @json($districtNames);
        const monthsOrder = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        const monthLabels = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        const districts = ['3701', '3702', '3703', '3704', '3705', '3706', '3707'];

        const colors = {
            '3701': '#0d6efd', // Blue
            '3702': '#21c08b', // Green
            '3703': '#f59e0b', // Orange
            '3704': '#10b981', // Emerald
            '3705': '#6366f1', // Indigo
            '3706': '#ec4899', // Pink
            '3707': '#8b5cf6'  // Purple
        };

        const datasets = districts.map(code => {
            const data = monthsOrder.map(m => chartData[m] ? (chartData[m][code] || 0) : 0);
            return {
                label: districtNames[code] || code,
                data: data,
                backgroundColor: colors[code] || '#6c757d',
                borderColor: colors[code] || '#6c757d',
                borderWidth: 1,
                borderRadius: 4
            };
        });

        const ctx = document.getElementById('birthChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: monthLabels,
                datasets: datasets
            },
            plugins: [ChartDataLabels],
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: true,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                plugins: {
                    datalabels: {
                        color: '#ffffff',
                        font: {
                            weight: 'bold',
                            family: 'Inter, Noto Sans Thai',
                            size: 11
                        },
                        formatter: function(value) {
                            return value > 0 ? value : '';
                        }
                    },
                    legend: {
                        position: 'top',
                        labels: {
                            boxWidth: 12,
                            padding: 15,
                            font: {
                                size: 12,
                                family: 'Inter, Noto Sans Thai'
                            }
                        }
                    },
                    tooltip: {
                        padding: 10,
                        bodyFont: {
                            family: 'Inter, Noto Sans Thai'
                        },
                        titleFont: {
                            family: 'Inter, Noto Sans Thai',
                            weight: 'bold'
                        }
                    }
                }
            }
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
                url: "{{ route('manage.birth-data.import') }}",
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

    // Excel Export Helper function (global scope) using SheetJS for real .xlsx files
    function exportTableToExcel(tableID, filename = ''){
        var $table = $('#' + tableID);
        if ($table.length === 0) {
            console.error('Table not found: ' + tableID);
            return;
        }
        
        var isDataTable = $.fn.DataTable.isDataTable('#' + tableID);
        var dt;
        var oldLength;
        
        if (isDataTable) {
            dt = $table.DataTable();
            oldLength = dt.page.len();
            // Show all rows
            dt.page.len(-1).draw(false);
        }
        
        // Use SheetJS to convert table DOM to a workbook
        var tableElement = document.getElementById(tableID);
        var wb = XLSX.utils.table_to_book(tableElement, { raw: true });
        
        // Restore length
        if (isDataTable) {
            dt.page.len(oldLength).draw(false);
        }
        
        var outFilename = filename ? filename + '_' + new Date().toISOString().slice(0,10) + '.xlsx' : 'excel_data.xlsx';
        XLSX.writeFile(wb, outFilename);
    }
</script>
@endpush
