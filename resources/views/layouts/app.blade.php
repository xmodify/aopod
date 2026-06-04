<!doctype html>
<html lang="th" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" href="{{ asset('/images/logo.png') }}" type="image/x-icon">
  <title>@yield('title', 'Amnatcharoen One Province One Data : AOPOD')</title>

  {{-- Bootstrap & Icons --}}
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/vendor/font-awesome/css/all.min.css') }}">

  {{-- DataTables --}}
  <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/css/dataTables.bootstrap5.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/datatables-buttons/css/buttons.bootstrap5.min.css') }}">

  {{-- SweetAlert2 --}}
  <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

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

    /* === Premium Glassmorphic Action Button === */
    .btn-glass-action {
      background: rgba(33, 192, 139, 0.06) !important;
      color: var(--green-2) !important;
      border: 1px solid rgba(33, 192, 139, 0.18) !important;
      font-weight: 600 !important;
      font-size: 0.85rem !important;
      padding: 0.4rem 0.85rem !important;
      border-radius: 12px !important;
      backdrop-filter: blur(5px) !important;
      transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
      display: inline-flex !important;
      align-items: center !important;
      gap: 6px !important;
      text-decoration: none !important;
    }
    .btn-glass-action:hover {
      background: rgba(33, 192, 139, 0.12) !important;
      color: var(--green-2) !important;
      border-color: rgba(33, 192, 139, 0.3) !important;
      transform: translateY(-1px) !important;
      box-shadow: 0 4px 12px rgba(33, 192, 139, 0.1) !important;
    }
    .btn-glass-action:active {
      transform: translateY(0) !important;
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
                      <i class="fa-solid fa-address-card fs-5" style="color: #10b981;"></i> OPD
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
              <div class="navbar-nav ms-auto d-flex align-items-center gap-2 py-2 py-xl-0">
                  @auth
                      @if(Auth::user()->isAdmin() || Auth::user()->canAccessDeath() || Auth::user()->canAccessBirth())
                          <a class="btn-glass-action" href="{{ route('admin.index') }}">
                              <i class="fa-solid fa-gauge-high"></i> แผงควบคุม
                          </a>
                      @endif

                      <!-- User Actions Dropdown -->
                      <div class="dropdown">
                          <button class="btn-glass-action dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                              <i class="fa-solid fa-circle-user text-green fs-5"></i>
                              <span>{{ Auth::user()->name }}</span>
                          </button>
                          <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2" style="border-radius: 16px; min-width: 200px;">
                              <li>
                                  <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2 text-dark" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal" style="border-radius: 10px;">
                                      <i class="fa-solid fa-key text-warning"></i> เปลี่ยนรหัสผ่าน
                                  </a>
                              </li>
                              <li><hr class="dropdown-divider my-2"></li>
                              <li>
                                  <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2 text-danger" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="border-radius: 10px;">
                                      <i class="fa-solid fa-right-from-bracket"></i> ออกจากระบบ
                                  </a>
                              </li>
                          </ul>
                      </div>

                      <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                          @csrf
                      </form>
                  @else
                      <button type="button" class="btn-glass-action" data-bs-toggle="modal" data-bs-target="#loginModal">
                          <i class="fa-solid fa-right-to-bracket text-green"></i> Login
                      </button>
                  @endauth
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
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 22px; background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(15px); border: 1px solid rgba(33, 192, 139, 0.25);">
        <form method="POST" action="{{ route('login') }}">
          @csrf
          <div class="modal-header border-0 pb-0 pt-4 px-4">
            <h4 class="modal-title fw-bold" id="loginModalLabel" style="background: linear-gradient(135deg, #0d6efd 0%, #21c08b 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Login</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
              <div class="mb-3">
                  <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">Username</label>
                  <div class="input-group">
                      <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px; border-color: rgba(33, 192, 139, 0.25); color: #10b981;"><i class="bi bi-person"></i></span>
                      <input type="email" name="email" class="form-control border-start-0" placeholder="Enter your username" required style="border-radius: 0 12px 12px 0; border-color: rgba(33, 192, 139, 0.25); font-size: 0.95rem; padding: 0.6rem 0.8rem; box-shadow: none;">
                  </div>
              </div>
              <div class="mb-4">
                  <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">Password</label>
                  <div class="input-group">
                      <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px; border-color: rgba(33, 192, 139, 0.25); color: #10b981;"><i class="bi bi-lock"></i></span>
                      <input type="password" name="password" class="form-control border-start-0" placeholder="Enter your password" required style="border-radius: 0 12px 12px 0; border-color: rgba(33, 192, 139, 0.25); font-size: 0.95rem; padding: 0.6rem 0.8rem; box-shadow: none;">
                  </div>
              </div>
          </div>
          <div class="modal-footer border-0 pt-0 pb-4 px-4">
            <button type="submit" class="btn w-100 py-2.5 fw-bold text-white shadow" style="border-radius: 12px; background: linear-gradient(135deg, #18a573 0%, #21c08b 100%); transition: all 0.2s ease;">Login</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Change Password Modal --}}
  <div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg" style="border-radius: 22px; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(15px); border: 1px solid rgba(245, 158, 11, 0.25);">
        <form method="POST" action="{{ route('change-password') }}">
          @csrf
          <div class="modal-header border-0 pb-0 pt-4 px-4">
            <h4 class="modal-title fw-bold" id="changePasswordModalLabel" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">เปลี่ยนรหัสผ่าน</h4>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4">
              <div class="mb-3">
                  <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">รหัสผ่านเดิม</label>
                  <div class="input-group">
                      <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px; border-color: rgba(245, 158, 11, 0.25); color: #f59e0b;"><i class="bi bi-shield-lock"></i></span>
                      <input type="password" name="current_password" class="form-control border-start-0" placeholder="รหัสผ่านปัจจุบัน" required style="border-radius: 0 12px 12px 0; border-color: rgba(245, 158, 11, 0.25); font-size: 0.95rem; padding: 0.6rem 0.8rem; box-shadow: none;">
                  </div>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">รหัสผ่านใหม่</label>
                  <div class="input-group">
                      <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px; border-color: rgba(245, 158, 11, 0.25); color: #f59e0b;"><i class="bi bi-key"></i></span>
                      <input type="password" name="new_password" class="form-control border-start-0" placeholder="รหัสผ่านใหม่ (อย่างน้อย 8 ตัวอักษร)" required style="border-radius: 0 12px 12px 0; border-color: rgba(245, 158, 11, 0.25); font-size: 0.95rem; padding: 0.6rem 0.8rem; box-shadow: none;">
                  </div>
              </div>
              <div class="mb-3">
                  <label class="form-label fw-semibold text-secondary" style="font-size: 0.9rem;">ยืนยันรหัสผ่านใหม่</label>
                  <div class="input-group">
                      <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px; border-color: rgba(245, 158, 11, 0.25); color: #f59e0b;"><i class="bi bi-check-circle"></i></span>
                      <input type="password" name="new_password_confirmation" class="form-control border-start-0" placeholder="ยืนยันรหัสผ่านใหม่อีกครั้ง" required style="border-radius: 0 12px 12px 0; border-color: rgba(245, 158, 11, 0.25); font-size: 0.95rem; padding: 0.6rem 0.8rem; box-shadow: none;">
                  </div>
              </div>
          </div>
          <div class="modal-footer border-0 pt-0 pb-4 px-4">
            <button type="submit" class="btn w-100 py-2.5 fw-bold text-white shadow" style="border-radius: 12px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); transition: all 0.2s ease;">บันทึกรหัสผ่านใหม่</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  {{-- Scripts --}}
    <!-- jQuery -->
    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    <!-- DataTables core -->
    <script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>

    <!-- Buttons + Export -->
    <script src="{{ asset('assets/vendor/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables-buttons/js/buttons.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/datatables-buttons/js/buttons.html5.min.js') }}"></script>

    <!-- JSZip (required for Excel export) -->
    <script src="{{ asset('assets/vendor/jszip/jszip.min.js') }}"></script>


  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/chart.js/chart.umd.min.js') }}"></script>

  @stack('scripts')
</body>
</html>
