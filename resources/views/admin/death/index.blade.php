@extends('layouts.admin')

@section('title', 'ข้อมูลการตาย - AOPOD')
@section('header_title', 'ข้อมูลการตาย (Death Information)')

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
                    <form method="GET" action="{{ route('manage.death-data.index') }}" id="fiscalYearForm" class="d-flex align-items-center gap-2">
                        <input type="hidden" name="district" value="{{ $selectedDistrict }}">
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
                @if(auth()->user()->canAccessDeath())
                <div>
                    <button type="button" class="btn btn-danger px-4 py-2.5 fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#importExcelModal" style="border-radius: 12px; background: linear-gradient(135deg, #dc3545 0%, #ffc107 100%); border: none;">
                        <i class="fa-solid fa-file-excel me-2"></i> นำเข้าข้อมูลการตาย (Excel)
                    </button>
                </div>
                @endif
            </div>

            {{-- Tab Navigation --}}
            <ul class="nav nav-pills mb-4 pb-2 border-bottom" id="deathTab" role="tablist" style="gap: 10px;">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold px-4 py-2.5 d-flex align-items-center gap-2" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard-pane" type="button" role="tab" aria-controls="dashboard-pane" aria-selected="true" style="border-radius: 12px; transition: all 0.2s;">
                        <i class="fa-solid fa-chart-line"></i> แดชบอร์ดสถิติ
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold px-4 py-2.5 d-flex align-items-center gap-2" id="top20-tab" data-bs-toggle="tab" data-bs-target="#top20-pane" type="button" role="tab" aria-controls="top20-pane" aria-selected="false" style="border-radius: 12px; transition: all 0.2s;">
                        <i class="fa-solid fa-ranking-star"></i> 20 อันดับสาเหตุการตาย
                    </button>
                </li>
                @if(auth()->user()->canAccessDeath())
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold px-4 py-2.5 d-flex align-items-center gap-2" id="list-tab" data-bs-toggle="tab" data-bs-target="#list-pane" type="button" role="tab" aria-controls="list-pane" aria-selected="false" style="border-radius: 12px; transition: all 0.2s;">
                        <i class="fa-solid fa-list"></i> รายการข้อมูลการตาย
                    </button>
                </li>
                @endif
            </ul>

            {{-- Tab Content --}}
            <div class="tab-content" id="deathTabContent">
                {{-- Tab 1: แดชบอร์ดสถิติ --}}
                <div class="tab-pane fade show active" id="dashboard-pane" role="tabpanel" aria-labelledby="dashboard-tab">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-chart-line text-green me-2"></i> แดชบอร์ดวิเคราะห์ข้อมูลการตาย</h5>
                            <span class="text-secondary small">วิเคราะห์ข้อมูลคนเสียชีวิตรายเดือน แยกตามอำเภอ ประจำปี พ.ศ. {{ $selectedYear < 2400 ? $selectedYear + 543 : $selectedYear }}</span>
                        </div>
                    </div>

                    {{-- Chart Canvas --}}
                    <div class="p-4 bg-white rounded-4 border mb-4 shadow-sm" style="min-height: 400px; position: relative;">
                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-chart-bar text-primary me-2"></i> สถิติคนเสียชีวิตรายเดือน แยกตามอำเภอทั้ง 7 แห่ง (ปี พ.ศ. {{ $selectedYear < 2400 ? $selectedYear + 543 : $selectedYear }})</h6>
                        <div style="height: 380px; width: 100%;">
                            <canvas id="deathChart"></canvas>
                        </div>
                    </div>

                    {{-- Data Table Section below the Chart --}}
                    <div class="p-4 bg-white rounded-4 border shadow-sm mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-table text-secondary me-2"></i> ตารางสรุปจำนวนคนเสียชีวิตรายเดือน แยกตามอำเภอ (ปี พ.ศ. {{ $selectedYear < 2400 ? $selectedYear + 543 : $selectedYear }})</h6>
                            <button type="button" class="btn btn-sm btn-outline-success fw-bold px-3 py-1.5 d-flex align-items-center gap-1" onclick="exportTableToExcel('monthlyDeathTable', 'monthly_deaths_by_district')" style="border-radius: 10px;">
                                <i class="fa-solid fa-file-excel"></i> Excel
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover align-middle mb-0 text-center" id="monthlyDeathTable" style="font-size: 0.85rem;">
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

                {{-- Tab 2: 20 อันดับสาเหตุการตาย (Parent Tab containing district filter & nested sub-tabs) --}}
                <div class="tab-pane fade" id="top20-pane" role="tabpanel" aria-labelledby="top20-tab">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-ranking-star text-green me-2"></i> 20 อันดับสาเหตุการตาย</h5>
                            <span class="text-secondary small">ข้อมูล 20 อันดับสาเหตุการตาย {{ $selectedDistrict === 'all' ? 'จ.อำนาจเจริญ' : ($districtNames[$selectedDistrict] ?? '') }} ประจำปี พ.ศ. {{ $selectedYear < 2400 ? $selectedYear + 543 : $selectedYear }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <label class="fw-bold text-secondary mb-0" style="font-size: 0.9rem; white-space: nowrap;">เลือกอำเภอ:</label>
                            <form method="GET" action="{{ route('manage.death-data.index') }}" id="districtFilterForm">
                                <input type="hidden" name="fiscal_year" value="{{ $selectedYear }}">
                                <select name="district" class="form-select py-2" onchange="document.getElementById('districtFilterForm').submit();" style="border-radius: 12px; border-color: rgba(33, 192, 139, 0.25); box-shadow: none; min-width: 200px;">
                                    <option value="all" {{ (string)$selectedDistrict === 'all' ? 'selected' : '' }}>จ.อำนาจเจริญ</option>
                                    @foreach($districtNames as $code => $name)
                                        <option value="{{ $code }}" {{ (string)$selectedDistrict === (string)$code ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    </div>

                    {{-- Nested Sub-Tabs --}}
                    <ul class="nav nav-tabs mb-4" id="top20SubTab" role="tablist" style="gap: 5px; border-bottom: 2px solid #dee2e6;">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold px-4 py-2.5 text-danger border-0 border-bottom" id="sub-top20-tab" data-bs-toggle="tab" data-bs-target="#sub-top20-pane" type="button" role="tab" aria-controls="sub-top20-pane" aria-selected="true" style="border-radius: 0;">
                                <i class="fa-solid fa-ranking-star me-1"></i> 20 อันดับโรค (Primary Diagnosis)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold px-4 py-2.5 text-warning border-0 border-bottom" id="sub-group504-tab" data-bs-toggle="tab" data-bs-target="#sub-group504-pane" type="button" role="tab" aria-controls="sub-group504-pane" aria-selected="false" style="border-radius: 0;">
                                <i class="fa-solid fa-list-ol me-1"></i> 21 กลุ่มสาเหตุ (รง.504)
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="top20SubTabContent">
                        {{-- Sub-Tab A: 20 อันดับโรค --}}
                        <div class="tab-pane fade show active" id="sub-top20-pane" role="tabpanel" aria-labelledby="sub-top20-tab">
                            <div class="row g-4">
                                {{-- Left Column: Chart --}}
                                <div class="col-lg-6">
                                    <div class="p-4 bg-white rounded-4 border shadow-sm d-flex flex-column" style="height: 550px; position: relative;">
                                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-chart-bar text-danger me-2"></i> อันดับโรคการเสียชีวิต (ปี พ.ศ. {{ $selectedYear < 2400 ? $selectedYear + 543 : $selectedYear }})</h6>
                                        <div class="flex-grow-1" style="height: calc(100% - 40px); width: 100%;">
                                            <canvas id="topCausesChart"></canvas>
                                        </div>
                                    </div>
                                </div>

                                {{-- Right Column: Table --}}
                                <div class="col-lg-6">
                                    <div class="p-4 bg-white rounded-4 border shadow-sm d-flex flex-column" style="height: 550px;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-table text-secondary me-2"></i> ตารางสรุปอันดับโรคการเสียชีวิต (ปี พ.ศ. {{ $selectedYear < 2400 ? $selectedYear + 543 : $selectedYear }})</h6>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="text" id="topCausesSearch" class="form-control form-control-sm py-1.5" placeholder="ค้นหาโรค..." style="border-radius: 8px; max-width: 160px; font-size: 0.8rem; border-color: rgba(33, 192, 139, 0.25); box-shadow: none;">
                                                <button type="button" class="btn btn-sm btn-outline-success fw-bold px-3 py-1.5 d-flex align-items-center gap-1" onclick="exportTableToExcel('topCausesTable', '20_causes_death')" style="border-radius: 10px;">
                                                    <i class="fa-solid fa-file-excel"></i> Excel
                                                </button>
                                            </div>
                                        </div>
                                        <div class="table-responsive flex-grow-1" style="max-height: 420px; overflow-y: auto;">
                                            <table class="table table-hover align-middle mb-0" id="topCausesTable" style="font-size: 0.85rem;">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th class="text-center" style="width: 10%;">อันดับ</th>
                                                        <th>รหัส - ชื่อโรคส่งต่อ (ICD10)</th>
                                                        <th class="text-center" style="width: 15%;">ชาย</th>
                                                        <th class="text-center" style="width: 15%;">หญิง</th>
                                                        <th class="text-center fw-bold text-dark" style="width: 15%;">รวม</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($topCauses as $idx => $cause)
                                                    <tr>
                                                        <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                                                        <td>{{ $cause['description'] }}</td>
                                                        <td class="text-center text-primary">{{ $cause['male'] }}</td>
                                                        <td class="text-center text-pink">{{ $cause['female'] }}</td>
                                                        <td class="text-center fw-bold text-dark bg-light bg-opacity-50">{{ $cause['total'] }}</td>
                                                    </tr>
                                                    @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">ไม่พบข้อมูลสาเหตุการตาย</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Sub-Tab B: 21 กลุ่มสาเหตุ (รง.504) --}}
                        <div class="tab-pane fade" id="sub-group504-pane" role="tabpanel" aria-labelledby="sub-group504-tab">
                            <div class="row g-4">
                                {{-- Left Column: Chart --}}
                                <div class="col-lg-6">
                                    <div class="p-4 bg-white rounded-4 border shadow-sm d-flex flex-column" style="height: 550px; position: relative;">
                                        <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-chart-bar text-warning me-2"></i> สัดส่วนการเสียชีวิตแยกตาม 21 กลุ่มโรค (ปี พ.ศ. {{ $selectedYear < 2400 ? $selectedYear + 543 : $selectedYear }})</h6>
                                        <div class="flex-grow-1" style="height: calc(100% - 40px); width: 100%;">
                                            <canvas id="causeGroupsChart"></canvas>
                                        </div>
                                    </div>
                                </div>

                                {{-- Right Column: Table --}}
                                <div class="col-lg-6">
                                    <div class="p-4 bg-white rounded-4 border shadow-sm d-flex flex-column" style="height: 550px;">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-table text-secondary me-2"></i> ตารางสรุป 21 กลุ่มสาเหตุโรค (ปี พ.ศ. {{ $selectedYear < 2400 ? $selectedYear + 543 : $selectedYear }})</h6>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="text" id="causeGroupsSearch" class="form-control form-control-sm py-1.5" placeholder="ค้นหากลุ่มโรค..." style="border-radius: 8px; max-width: 160px; font-size: 0.8rem; border-color: rgba(33, 192, 139, 0.25); box-shadow: none;">
                                                <button type="button" class="btn btn-sm btn-outline-success fw-bold px-3 py-1.5 d-flex align-items-center gap-1" onclick="exportTableToExcel('causeGroupsTable', '21_groups_death')" style="border-radius: 10px;">
                                                    <i class="fa-solid fa-file-excel"></i> Excel
                                                </button>
                                            </div>
                                        </div>
                                        <div class="table-responsive flex-grow-1" style="max-height: 420px; overflow-y: auto;">
                                            <table class="table table-hover align-middle mb-0" id="causeGroupsTable" style="font-size: 0.85rem;">
                                                <thead class="table-light sticky-top">
                                                    <tr>
                                                        <th class="text-center" style="width: 10%;">อันดับ</th>
                                                        <th>กลุ่มสาเหตุ (รง.504)</th>
                                                        <th class="text-center" style="width: 15%;">ชาย</th>
                                                        <th class="text-center" style="width: 15%;">หญิง</th>
                                                        <th class="text-center fw-bold text-dark" style="width: 15%;">รวม</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @php $rank = 1; @endphp
                                                    @forelse($causeGroups as $group)
                                                    @if($group['total'] > 0)
                                                    <tr>
                                                        <td class="text-center fw-bold">{{ $rank++ }}</td>
                                                        <td>{{ $group['name'] }}</td>
                                                        <td class="text-center text-primary">{{ $group['male'] }}</td>
                                                        <td class="text-center text-pink">{{ $group['female'] }}</td>
                                                        <td class="text-center fw-bold text-dark bg-light bg-opacity-50">{{ $group['total'] }}</td>
                                                    </tr>
                                                    @endif
                                                    @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center text-muted py-4">ไม่พบข้อมูลสาเหตุการตาย</td>
                                                    </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                @if(auth()->user()->canAccessDeath())
                {{-- Tab 3: รายการข้อมูลการตาย --}}
                <div class="tab-pane fade" id="list-pane" role="tabpanel" aria-labelledby="list-tab">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark"><i class="fa-solid fa-skull text-danger me-2"></i> รายการข้อมูลการตาย</h5>
                            <span class="text-secondary small">ตารางแสดงรายละเอียดข้อมูลการตาย ประจำปี พ.ศ. {{ $selectedYear < 2400 ? $selectedYear + 543 : $selectedYear }} | พื้นที่: {{ $selectedDistrict === 'all' ? 'จ.อำนาจเจริญ' : ($districtNames[$selectedDistrict] ?? '') }}</span>
                        </div>
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
                                    <td class="fw-bold text-dark" style="mso-number-format:'\@';">{{ $death->pid }}</td>
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
                                            {{ sprintf('%02d/%02d/%d', $death->ddate, $death->dmon, $death->dyear < 2400 ? $death->dyear + 543 : $death->dyear) }}
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
                                            {{ sprintf('%02d/%02d/%d', $death->bdate, $death->bmon, $death->byear < 2400 ? $death->byear + 543 : $death->byear) }}
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
                @endif


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
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize DataTable for Deaths Table
        $('#deathsTable').DataTable({
            language: {
                url: "{{ asset('assets/vendor/datatables/th.json') }}"
            },
            ordering: true,
            pageLength: 10,
            initComplete: function() {
                var excelBtn = $('<button type="button" class="btn btn-sm btn-outline-success fw-bold px-3 py-1.5 ms-2 d-inline-flex align-items-center gap-1" onclick="exportTableToExcel(\'deathsTable\', \'deaths_list\')" style="border-radius: 10px;"><i class="fa-solid fa-file-excel"></i> Excel</button>');
                $('#deathsTable_filter').append(excelBtn);
            }
        });

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

        const ctx = document.getElementById('deathChart').getContext('2d');
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

        // --- Store and restore active tab from localStorage ---
        const activeTab = localStorage.getItem('death_active_tab');
        if (activeTab) {
            const tabEl = document.querySelector(`#deathTab button[data-bs-target="${activeTab}"]`);
            if (tabEl) {
                const tab = new bootstrap.Tab(tabEl);
                tab.show();
            }
        }
        
        $('#deathTab button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            localStorage.setItem('death_active_tab', $(e.target).attr('data-bs-target'));
        });

        // --- Store and restore active sub-tab from localStorage ---
        const activeSubTab = localStorage.getItem('death_active_sub_tab');
        if (activeSubTab) {
            const subTabEl = document.querySelector(`#top20SubTab button[data-bs-target="${activeSubTab}"]`);
            if (subTabEl) {
                const tab = new bootstrap.Tab(subTabEl);
                tab.show();
            }
        }
        
        $('#top20SubTab button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            localStorage.setItem('death_active_sub_tab', $(e.target).attr('data-bs-target'));
        });

        // --- Chart for Top 20 Causes of Death (Primary Diagnosis) ---
        const topCausesData = @json($topCauses);
        const topCausesLabels = topCausesData.map(item => item.code);
        const topCausesTotals = topCausesData.map(item => item.total);
        const topCausesTooltips = topCausesData.map(item => item.description);

        const ctxTop = document.getElementById('topCausesChart').getContext('2d');
        new Chart(ctxTop, {
            type: 'bar',
            data: {
                labels: topCausesLabels,
                datasets: [{
                    label: 'จำนวนผู้เสียชีวิต (ราย)',
                    data: topCausesTotals,
                    backgroundColor: 'rgba(220, 53, 69, 0.85)',
                    borderColor: '#dc3545',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const idx = context.dataIndex;
                                return ` จำนวน: ${context.parsed.x} ราย (${topCausesTooltips[idx]})`;
                            }
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 } },
                    y: { grid: { display: false } }
                }
            }
        });

        // --- Chart for 21 Cause Groups (รง.504) ---
        const causeGroupsData = @json($causeGroups).filter(item => item.total > 0).slice(0, 10);
        const causeGroupsLabels = causeGroupsData.map(item => `กลุ่มที่ ${item.group_num}`);
        const causeGroupsTotals = causeGroupsData.map(item => item.total);
        const causeGroupsNames = causeGroupsData.map(item => item.name);

        const ctxGroups = document.getElementById('causeGroupsChart').getContext('2d');
        new Chart(ctxGroups, {
            type: 'bar',
            data: {
                labels: causeGroupsLabels,
                datasets: [{
                    label: 'จำนวนผู้เสียชีวิต (ราย)',
                    data: causeGroupsTotals,
                    backgroundColor: 'rgba(245, 158, 11, 0.85)',
                    borderColor: '#f59e0b',
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const idx = context.dataIndex;
                                return ` จำนวน: ${context.parsed.x} ราย (${causeGroupsNames[idx]})`;
                            }
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 } },
                    y: { grid: { display: false } }
                }
            }
        });

        // Copy API URL function
        $('.copy-api-btn').on('click', function() {
            let targetId = $(this).data('target');
            let textToCopy = $('#' + targetId).text();
            
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(textToCopy).then(function() {
                    Swal.fire({
                        title: 'คัดลอกสำเร็จ!',
                        text: 'คัดลอกลิงก์ API เรียบร้อยแล้ว',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }).catch(function() {
                    fallbackCopy(textToCopy);
                });
            } else {
                fallbackCopy(textToCopy);
            }
        });

        function fallbackCopy(textToCopy) {
            let tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(textToCopy).select();
            document.execCommand('copy');
            tempInput.remove();
            Swal.fire({
                title: 'คัดลอกสำเร็จ!',
                text: 'คัดลอกลิงก์ API เรียบร้อยแล้ว',
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            });
        }

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
                url: "{{ route('manage.death-data.import') }}",
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
        // Search functionality for Top Causes Table
        $('#topCausesSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#topCausesTable tbody tr').filter(function() {
                var rowText = $(this).find('td:nth-child(2)').text().toLowerCase();
                $(this).toggle(rowText.indexOf(value) > -1);
            });
            var visibleRank = 1;
            $('#topCausesTable tbody tr:visible').each(function() {
                $(this).find('td:first-child').text(visibleRank++);
            });
        });

        // Search functionality for Cause Groups Table
        $('#causeGroupsSearch').on('keyup', function() {
            var value = $(this).val().toLowerCase();
            $('#causeGroupsTable tbody tr').filter(function() {
                var rowText = $(this).find('td:nth-child(2)').text().toLowerCase();
                $(this).toggle(rowText.indexOf(value) > -1);
            });
            var visibleRank = 1;
            $('#causeGroupsTable tbody tr:visible').each(function() {
                $(this).find('td:first-child').text(visibleRank++);
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
