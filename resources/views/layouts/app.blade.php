<!doctype html>
<html lang="th" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" href="{{ asset('/images/logo.png') }}" type="image/x-icon">
  <title>@yield('title', 'Amnatcharoen One Province One Data : AOPOD')</title>

  {{-- Bootstrap & Icons --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  {{-- DataTables --}}
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

  {{-- SweetAlert2 --}}
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  {{-- Custom Styles --}}
  <style>
    :root{
      --green:#18a573;
      --green-2:#21c08b;
      --blue:#0d6efd;
      --bg-1:#e9fbf2;
      --glass-bg:rgba(255,255,255,.7);
      --glass-bd:rgba(33, 192, 139, .35);
      --shadow:0 10px 30px rgba(24,165,115,.15);
      --radius:22px;
    }
    body{
      min-height:100vh;
      background:
        radial-gradient(1200px 800px at 10% -10%, rgba(33,192,139,.18), transparent 60%),
        radial-gradient(1000px 600px at 110% 10%, rgba(13,110,253,.14), transparent 60%),
        linear-gradient(135deg, #f6fffb 0%, var(--bg-1) 40%, #ffffff 100%);
      animation: floatBg 24s ease-in-out infinite alternate;
      background-attachment: fixed;
    }
    @keyframes floatBg{0%{background-position:0 0}100%{background-position:5% -3%}}
    .brand-title,h1,h2,h3,h4,.nav-link,.table thead th{color:var(--blue);}
    .glass{background:var(--glass-bg);border:1px solid var(--glass-bd);backdrop-filter:blur(10px);border-radius:var(--radius);box-shadow:var(--shadow);}
    .text-green{color:var(--green)!important;}
    hr {
      border-top: 1px solid var(--glass-bd) !important;
      opacity: 0.55 !important;
      margin: 2rem 0 !important;
    }

    /* === Modern Premium Tab Pills Style (Global) === */
    .nav-pills .nav-link {
      background: rgba(255, 255, 255, 0.4) !important;
      backdrop-filter: blur(10px) !important;
      border: 1px solid rgba(0, 0, 0, 0.08) !important;
      border-radius: 14px !important;
      padding: 10px 18px !important;
      color: #475569 !important;
      font-weight: 600 !important;
      transition: all 0.25s ease-in-out !important;
      box-shadow: 0 2px 6px rgba(0,0,0,0.02) !important;
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 8px;
    }
    .nav-pills .nav-link:hover {
      transform: translateY(-2px);
      background: rgba(255, 255, 255, 0.8) !important;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
    }

    /* Hospital-specific Active Tab Styling */
    #tab-10985.active {
      background: linear-gradient(135deg, #f5f3ff 0%, #ffffff 100%) !important;
      border-left: 5px solid #8b5cf6 !important;
      border-top: 1px solid rgba(139, 92, 246, 0.15) !important;
      border-right: 1px solid rgba(139, 92, 246, 0.15) !important;
      border-bottom: 1px solid rgba(139, 92, 246, 0.15) !important;
      color: #7c3aed !important;
      box-shadow: 0 6px 15px rgba(139, 92, 246, 0.12) !important;
    }
    #tab-10986.active {
      background: linear-gradient(135deg, #ecfeff 0%, #ffffff 100%) !important;
      border-left: 5px solid #06b6d4 !important;
      border-top: 1px solid rgba(6, 182, 212, 0.15) !important;
      border-right: 1px solid rgba(6, 182, 212, 0.15) !important;
      border-bottom: 1px solid rgba(6, 182, 212, 0.15) !important;
      color: #0891b2 !important;
      box-shadow: 0 6px 15px rgba(6, 182, 212, 0.12) !important;
    }
    #tab-10987.active {
      background: linear-gradient(135deg, #fdf2f8 0%, #ffffff 100%) !important;
      border-left: 5px solid #ec4899 !important;
      border-top: 1px solid rgba(236, 72, 153, 0.15) !important;
      border-right: 1px solid rgba(236, 72, 153, 0.15) !important;
      border-bottom: 1px solid rgba(236, 72, 153, 0.15) !important;
      color: #db2777 !important;
      box-shadow: 0 6px 15px rgba(236, 72, 153, 0.12) !important;
    }
    #tab-10988.active {
      background: linear-gradient(135deg, #fef3c7 0%, #ffffff 100%) !important;
      border-left: 5px solid #f59e0b !important;
      border-top: 1px solid rgba(245, 158, 11, 0.15) !important;
      border-right: 1px solid rgba(245, 158, 11, 0.15) !important;
      border-bottom: 1px solid rgba(245, 158, 11, 0.15) !important;
      color: #d97706 !important;
      box-shadow: 0 6px 15px rgba(245, 158, 11, 0.12) !important;
    }
    #tab-10989.active {
      background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%) !important;
      border-left: 5px solid #3b82f6 !important;
      border-top: 1px solid rgba(59, 130, 246, 0.15) !important;
      border-right: 1px solid rgba(59, 130, 246, 0.15) !important;
      border-bottom: 1px solid rgba(59, 130, 246, 0.15) !important;
      color: #2563eb !important;
      box-shadow: 0 6px 15px rgba(59, 130, 246, 0.12) !important;
    }
    #tab-10990.active {
      background: linear-gradient(135deg, #ecfdf5 0%, #ffffff 100%) !important;
      border-left: 5px solid #10b981 !important;
      border-top: 1px solid rgba(16, 185, 129, 0.15) !important;
      border-right: 1px solid rgba(16, 185, 129, 0.15) !important;
      border-bottom: 1px solid rgba(16, 185, 129, 0.15) !important;
      color: #059669 !important;
      box-shadow: 0 6px 15px rgba(16, 185, 129, 0.12) !important;
    }
    #tab-10703.active {
      background: linear-gradient(135deg, #fff1f2 0%, #ffffff 100%) !important;
      border-left: 5px solid #f43f5e !important;
      border-top: 1px solid rgba(244, 63, 94, 0.15) !important;
      border-right: 1px solid rgba(244, 63, 94, 0.15) !important;
      border-bottom: 1px solid rgba(244, 63, 94, 0.15) !important;
      color: #e11d48 !important;
      box-shadow: 0 6px 15px rgba(244, 63, 94, 0.12) !important;
    }

    /* === Hospital-specific Table Icon & Header Custom Colors === */
    #pane-10985 h6.fw-bold, #pane-10985 i.text-primary, #pane-10985 .text-primary, #pane-10985 .text-purple {
      color: #8b5cf6 !important;
    }
    #pane-10985 .tr-total {
      border-top: 2px solid #8b5cf6 !important;
    }
    #pane-10985 .tr-total td.text-primary {
      color: #8b5cf6 !important;
    }

    #pane-10986 h6.fw-bold, #pane-10986 i.text-primary, #pane-10986 .text-primary {
      color: #06b6d4 !important;
    }
    #pane-10986 .tr-total {
      border-top: 2px solid #06b6d4 !important;
    }
    #pane-10986 .tr-total td.text-primary {
      color: #06b6d4 !important;
    }

    #pane-10987 h6.fw-bold, #pane-10987 i.text-primary, #pane-10987 .text-primary {
      color: #ec4899 !important;
    }
    #pane-10987 .tr-total {
      border-top: 2px solid #ec4899 !important;
    }
    #pane-10987 .tr-total td.text-primary {
      color: #ec4899 !important;
    }

    #pane-10988 h6.fw-bold, #pane-10988 i.text-primary, #pane-10988 .text-primary {
      color: #f59e0b !important;
    }
    #pane-10988 .tr-total {
      border-top: 2px solid #f59e0b !important;
    }
    #pane-10988 .tr-total td.text-primary {
      color: #f59e0b !important;
    }

    #pane-10989 h6.fw-bold, #pane-10989 i.text-primary, #pane-10989 .text-primary {
      color: #3b82f6 !important;
    }
    #pane-10989 .tr-total {
      border-top: 2px solid #3b82f6 !important;
    }
    #pane-10989 .tr-total td.text-primary {
      color: #3b82f6 !important;
    }

    #pane-10990 h6.fw-bold, #pane-10990 i.text-primary, #pane-10990 .text-primary {
      color: #10b981 !important;
    }
    #pane-10990 .tr-total {
      border-top: 2px solid #10b981 !important;
    }
    #pane-10990 .tr-total td.text-primary {
      color: #10b981 !important;
    }

    #pane-10703 h6.fw-bold, #pane-10703 i.text-primary, #pane-10703 .text-primary {
      color: #f43f5e !important;
    }
    #pane-10703 .tr-total {
      border-top: 2px solid #f43f5e !important;
    }
    #pane-10703 .tr-total td.text-primary {
      color: #f43f5e !important;
    }
  </style>

  @stack('styles')
</head>

<body>
  {{-- NAVBAR --}}
  <nav class="navbar navbar-expand-lg bg-white bg-opacity-75 border-bottom sticky-top glass" style="border-radius:0">
      <div class="container-fluid">
          <a class="navbar-brand d-flex align-items-center text-primary brand-title fw-bold" href="{{ url('web/') }}">
              <i class="fa-solid fa-bed-pulse text-danger fs-5 me-2"></i> IPD
          </a>

          <a class="navbar-brand d-flex align-items-center text-primary brand-title fw-bold" href="{{ url('web/opd') }}">
              <i class="bi bi-person-vcard text-green me-2"></i> OPD
          </a>

          <a class="navbar-brand d-flex align-items-center text-primary brand-title fw-bold" href="{{ url('web/refer') }}">
              <i class="fa-solid fa-truck-medical text-danger fs-5 me-2"></i> Refer
          </a>

          <a class="navbar-brand d-flex align-items-center text-primary brand-title fw-bold" href="{{ url('web/operation') }}">
              <i class="fa-solid fa-heart-pulse text-purple fs-5 me-2" style="color:#8b5cf6;"></i> ผ่าตัด
          </a>

          <a class="navbar-brand d-flex align-items-center text-primary brand-title fw-bold" href="{{ url('web/claim') }}">
              <i class="bi bi-coin fs-5 text-warning me-2"></i> Claim
          </a>

          <a class="navbar-brand d-flex align-items-center text-primary brand-title fw-bold" 
              href="https://apps-amno.moph.go.th/hdcamnat/reports/insurance/report_summary2.php" target="_blank">
              <i class="fa-solid fa-money-bill-wave fs-5 me-2" style="color:#7e57c2;"></i></i> AIM
          </a>

          <a class="navbar-brand d-flex align-items-center text-primary brand-title fw-bold ms-3"  
              href="https://hosoffice-chanuman.moph.go.th/inventory" target="_blank">
              <i class="bi bi-box-seam text-info me-2"></i> Inventory
          </a>
          <a class="navbar-brand d-flex align-items-center text-primary brand-title fw-bold ms-3"
            href="https://hosoffice-chanuman.moph.go.th/pharmacy" target="_blank">
            <i class="bi bi-capsule-pill text-success me-2"></i> One Province One Formula
          </a>

          {{-- <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav">
              <span class="navbar-toggler-icon"></span>
          </button> --}}

          <div class="collapse navbar-collapse" id="topnav">
              {{-- <ul class="navbar-nav ms-auto">
                  @auth
                      <li class="nav-item dropdown">
                          <a class="nav-link dropdown-toggle text-primary" href="#" id="userDropdown" role="button"
                             data-bs-toggle="dropdown">{{ Auth::user()->name }}</a>
                          <ul class="dropdown-menu dropdown-menu-end">
                              <li>
                                  <form action="{{ route('logout') }}" method="POST">@csrf
                                      <button type="submit" class="dropdown-item text-primary">Logout</button>
                                  </form>
                              </li>
                          </ul>
                      </li>
                  @else
                      <li class="nav-item">
                          <a class="nav-link text-primary" href="#" data-bs-toggle="modal"
                             data-bs-target="#loginModal"><strong>Login</strong></a>
                      </li>
                  @endauth
              </ul> --}}
          </div>
      </div>
  </nav>

  {{-- MAIN CONTENT --}}
  <main class="py-4">
    @yield('content')
  </main>

  {{-- FOOTER --}}
  <footer class="py-4">
    <div class="container text-center text-secondary small">
      © {{ now()->year }} Amnatcharoen One Province One Data : AOPOD
    </div>
  </footer>

  {{-- Login Modal --}}
  <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title" id="loginModalLabel">เข้าสู่ระบบ</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label">อีเมล</label>
                  <input type="email" name="email" class="form-control" required>
              </div>
              <div class="mb-3">
                  <label class="form-label">รหัสผ่าน</label>
                  <input type="password" name="password" class="form-control" required>
              </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary w-100">เข้าสู่ระบบ</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Scripts --}}
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables core -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <!-- Buttons + Export -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <!-- JSZip (required for Excel export) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  @stack('scripts')
</body>
</html>
