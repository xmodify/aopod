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

    /* === Premium Full-Width Bottom-Rounded Glassmorphic Navbar === */
    .navbar-glass-container {
      margin: 0 !important;
      width: 100% !important;
      max-width: 100% !important;
      border-radius: 0 0 24px 24px !important;
      background: rgba(255, 255, 255, 0.85) !important;
      backdrop-filter: blur(15px) !important;
      border-bottom: 1px solid var(--glass-bd) !important;
      border-left: none !important;
      border-right: none !important;
      border-top: none !important;
      box-shadow: 0 8px 32px 0 rgba(24, 165, 115, 0.05) !important;
      transition: all 0.3s ease-in-out !important;
      padding: 0.5rem 2.5rem !important;
    }
    
    .nav-menu-link {
      color: #475569 !important;
      font-weight: 600 !important;
      font-size: 0.92rem !important;
      padding: 0.5rem 1rem !important;
      border-radius: 12px !important;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
      display: inline-flex !important;
      align-items: center !important;
      gap: 6px !important;
      border: 1px solid transparent !important;
      text-decoration: none !important;
    }
    
    .nav-menu-link:hover {
      color: var(--green-2) !important;
      background: rgba(33, 192, 139, 0.08) !important;
      transform: translateY(-1.5px);
    }
    
    /* Dynamic active states for each module */
    .nav-menu-link.active-ipd {
      background: rgba(244, 63, 94, 0.08) !important;
      color: #e11d48 !important;
      border: 1px solid rgba(244, 63, 94, 0.15) !important;
    }
    .nav-menu-link.active-opd {
      background: rgba(16, 185, 129, 0.08) !important;
      color: #059669 !important;
      border: 1px solid rgba(16, 185, 129, 0.15) !important;
    }
    .nav-menu-link.active-refer {
      background: rgba( red, 0.08) !important; /* using red from theme */
      color: #dc2626 !important;
      border: 1px solid rgba(239, 68, 68, 0.15) !important;
    }
    .nav-menu-link.active-refer-cust {
      background: rgba(239, 68, 68, 0.08) !important;
      color: #dc2626 !important;
      border: 1px solid rgba(239, 68, 68, 0.15) !important;
    }
    .nav-menu-link.active-op {
      background: rgba(139, 92, 246, 0.08) !important;
      color: #7c3aed !important;
      border: 1px solid rgba(139, 92, 246, 0.15) !important;
    }
    .nav-menu-link.active-claim {
      background: rgba(245, 158, 11, 0.08) !important;
      color: #d97706 !important;
      border: 1px solid rgba(245, 158, 11, 0.15) !important;
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
  <nav class="navbar navbar-expand-xl sticky-top navbar-glass-container">
      <div class="container-fluid px-0">
          <!-- Logo Brand -->
          <a class="navbar-brand d-flex align-items-center me-4" href="{{ url('web/') }}">
              <!-- SVG Logo -->
              <svg width="40" height="40" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" style="filter: drop-shadow(0px 4px 8px rgba(33, 192, 139, 0.25));">
                <defs>
                  <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#0d6efd" />
                    <stop offset="100%" stop-color="#21c08b" />
                  </linearGradient>
                  <linearGradient id="logoGrad2" x1="100%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#21c08b" />
                    <stop offset="100%" stop-color="#18a573" />
                  </linearGradient>
                </defs>
                <path d="M100 15 L173 57 L173 143 L100 185 L27 143 L27 57 Z" stroke="url(#logoGrad)" stroke-width="12" stroke-linejoin="round" fill="rgba(255,255,255,0.75)"/>
                <path d="M55 100 L80 100 L93 65 L107 135 L120 85 L128 100 L145 100" stroke="url(#logoGrad2)" stroke-width="12" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="93" cy="65" r="8" fill="#0d6efd" />
                <circle cx="107" cy="135" r="8" fill="#21c08b" />
                <circle cx="120" cy="85" r="8" fill="#18a573" />
              </svg>
              <!-- Logo Typography -->
              <div class="d-flex flex-column ms-2">
                <span class="fs-5 fw-bold leading-tight" style="background: linear-gradient(135deg, #0d6efd 0%, #21c08b 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; letter-spacing: 0.5px; line-height: 1.1;">AOPOD</span>
                <span class="text-secondary" style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.2px; line-height: 1;">One Province One Data</span>
              </div>
          </a>

          <!-- Collapse button for mobile -->
          <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#topnav" aria-controls="topnav" aria-expanded="false" aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
          </button>

          <!-- Navigation Links -->
          <div class="collapse navbar-collapse" id="topnav">
              <div class="navbar-nav me-auto d-flex flex-wrap gap-2 py-2 py-xl-0">
                  <a class="nav-menu-link {{ Request::is('web') || Request::is('web/') ? 'active-ipd' : '' }}" href="{{ url('web/') }}">
                      <i class="fa-solid fa-bed-pulse fs-5" style="color: #f43f5e;"></i> IPD
                  </a>
        
                  <a class="nav-menu-link {{ Request::is('web/opd') ? 'active-opd' : '' }}" href="{{ url('web/opd') }}">
                      <i class="bi bi-person-vcard fs-5" style="color: #10b981;"></i> OPD
                  </a>
        
                  <a class="nav-menu-link {{ Request::is('web/refer') ? 'active-refer-cust' : '' }}" href="{{ url('web/refer') }}">
                      <i class="fa-solid fa-truck-medical fs-5" style="color: #ef4444;"></i> Refer
                  </a>
        
                  <a class="nav-menu-link {{ Request::is('web/operation') ? 'active-op' : '' }}" href="{{ url('web/operation') }}">
                      <i class="fa-solid fa-heart-pulse fs-5" style="color: #8b5cf6;"></i> ผ่าตัด
                  </a>
        
                  <a class="nav-menu-link {{ Request::is('web/claim') ? 'active-claim' : '' }}" href="{{ url('web/claim') }}">
                      <i class="bi bi-coin fs-5" style="color: #f59e0b;"></i> Claim
                  </a>
        
                  <a class="nav-menu-link" href="https://apps-amno.moph.go.th/hdcamnat/reports/insurance/report_summary2.php" target="_blank">
                      <i class="fa-solid fa-money-bill-wave fs-5" style="color: #7e57c2;"></i> AIM
                  </a>
        
                  <a class="nav-menu-link" href="https://hosoffice-chanuman.moph.go.th/inventory" target="_blank">
                      <i class="bi bi-box-seam fs-5" style="color: #06b6d4;"></i> Inventory
                  </a>
                  
                  <a class="nav-menu-link" href="https://hosoffice-chanuman.moph.go.th/pharmacy" target="_blank">
                      <i class="bi bi-capsule-pill fs-5" style="color: #10b981;"></i> One Province One Formula
                  </a>
              </div>
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
