<!doctype html>
<html lang="th" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="icon" href="{{ asset('/images/logo.png') }}" type="image/x-icon">
  <title>@yield('title', 'Admin Panel - AOPOD')</title>

  {{-- Bootstrap & Icons --}}
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/vendor/font-awesome/css/all.min.css') }}">

  {{-- DataTables --}}
  <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/css/dataTables.bootstrap5.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/datatables-buttons/css/buttons.bootstrap5.min.css') }}">

  {{-- SweetAlert2 --}}
  <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

  <style>
    :root {
      --admin-primary: #0f172a;
      --admin-sidebar-bg: rgba(15, 23, 42, 0.95);
      --green: #18a573;
      --green-2: #21c08b;
      --blue: #0d6efd;
      --bg-1: #f8fafc;
      --glass-bg: rgba(255, 255, 255, 0.75);
      --glass-bd: rgba(33, 192, 139, 0.25);
      --shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
      --radius: 20px;
    }

    body {
      min-height: 100vh;
      background:
        radial-gradient(1200px 800px at 10% -10%, rgba(33, 192, 139, 0.1), transparent 60%),
        radial-gradient(1000px 600px at 110% 10%, rgba(13, 110, 253, 0.08), transparent 60%),
        linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
      background-attachment: fixed;
      font-family: 'Inter', 'Noto Sans Thai', sans-serif;
    }

    /* === Sidebar Styling === */
    .sidebar {
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      z-index: 100;
      width: 280px;
      padding: 1.5rem 1rem;
      background: var(--admin-sidebar-bg);
      backdrop-filter: blur(15px);
      border-right: 1px solid rgba(255, 255, 255, 0.1);
      transition: all 0.3s ease-in-out;
      box-shadow: 10px 0 30px rgba(0,0,0,0.1);
    }

    .sidebar-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 0.5rem 1rem;
      margin-bottom: 2rem;
      text-decoration: none;
    }

    .sidebar-menu {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .sidebar-menu-item {
      margin-bottom: 0.5rem;
    }

    .sidebar-link {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 0.8rem 1.2rem;
      color: #94a3b8;
      font-weight: 500;
      text-decoration: none;
      border-radius: 12px;
      transition: all 0.2s ease-in-out;
    }

    .sidebar-link:hover {
      color: #ffffff;
      background: rgba(255, 255, 255, 0.08);
      transform: translateX(4px);
    }

    .sidebar-link.active {
      color: #ffffff;
      background: linear-gradient(135deg, var(--green-2) 0%, var(--green) 100%);
      box-shadow: 0 4px 12px rgba(24, 165, 115, 0.3);
    }

    .sidebar-link i {
      font-size: 1.15rem;
      width: 24px;
      text-align: center;
    }

    /* === Main Content Area === */
    .main-wrapper {
      margin-left: 280px;
      transition: all 0.3s ease-in-out;
    }

    /* === Header/Topbar === */
    .topbar {
      height: 70px;
      background: rgba(255, 255, 255, 0.8);
      backdrop-filter: blur(15px);
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      padding: 0 2rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 99;
    }

    .glass-card {
      background: var(--glass-bg);
      border: 1px solid var(--glass-bd);
      backdrop-filter: blur(10px);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 1.8rem;
      transition: all 0.3s ease;
    }

    .glass-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 15px 35px rgba(15, 23, 42, 0.12);
    }

    /* === Responsive Design & Toggling === */
    .sidebar-toggle-btn {
      display: block;
      background: none;
      border: none;
      font-size: 1.5rem;
      color: var(--admin-primary);
      cursor: pointer;
      transition: color 0.2s;
    }
    .sidebar-toggle-btn:hover {
      color: var(--green-2);
    }

    @media (max-width: 991.98px) {
      .sidebar {
        left: -280px;
      }
      .sidebar.show {
        left: 0;
      }
      .main-wrapper {
        margin-left: 0;
      }
    }

    /* Desktop behavior when collapsed */
    @media (min-width: 992px) {
      body.sidebar-toggled .sidebar {
        left: -280px;
      }
      body.sidebar-toggled .main-wrapper {
        margin-left: 0;
      }
    }

    /* Mobile Backdrop Overlay */
    .sidebar-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.4);
      backdrop-filter: blur(4px);
      z-index: 98;
      transition: all 0.3s ease-in-out;
    }

    @media (max-width: 991.98px) {
      body.sidebar-overlay-active .sidebar-overlay {
        display: block;
      }
    }
  </style>
  @stack('styles')
</head>
<body>
  {{-- Overlay for closing sidebar on mobile --}}
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  {{-- SIDEBAR --}}
  <aside class="sidebar" id="sidebar">
    <!-- Close button for mobile -->
    <button class="btn d-lg-none text-white border-0 p-0 fs-4 position-absolute" id="sidebarClose" style="top: 20px; right: 20px; z-index: 101;" aria-label="Close Sidebar">
      <i class="fa-solid fa-xmark"></i>
    </button>

    <!-- Brand -->
    <a href="{{ route('admin.index') }}" class="sidebar-brand">
      <svg width="36" height="36" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <linearGradient id="logoGrad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#0d6efd" />
            <stop offset="100%" stop-color="#21c08b" />
          </linearGradient>
        </defs>
        <path d="M100 15 L173 57 L173 143 L100 185 L27 143 L27 57 Z" stroke="url(#logoGrad)" stroke-width="14" stroke-linejoin="round" fill="rgba(255,255,255,0.15)"/>
        <path d="M55 100 L80 100 L93 65 L107 135 L120 85 L128 100 L145 100" stroke="#21c08b" stroke-width="14" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <div class="d-flex flex-column">
        <span class="fs-5 fw-bold text-white leading-tight">AOPOD</span>
        <span class="text-muted" style="font-size: 0.65rem; font-weight: 600;">ADMIN PANEL</span>
      </div>
    </a>

    <!-- Menu -->
    <ul class="sidebar-menu">
      <li class="sidebar-menu-item">
        <a href="{{ url('web') }}" class="sidebar-link">
          <i class="fa-solid fa-house-laptop fs-5" style="color: #21c08b !important;"></i>
          <span>กลับหน้าเว็บหลัก</span>
        </a>
      </li>

      <!-- More administrative menus can be added here easily -->
      <li class="sidebar-menu-item mt-4">
        <div class="text-muted small px-3 mb-2 fw-bold text-uppercase" style="letter-spacing: 1px;">ระบบหลัก</div>
      </li>

      <li class="sidebar-menu-item">
        <a href="{{ route('admin.users') }}" class="sidebar-link {{ Request::is('admin/users*') ? 'active' : '' }}">
          <i class="fa-solid fa-user-gear fs-5" style="color: #0d6efd !important;"></i>
          <span>จัดการสมาชิก</span>
        </a>
      </li>

      @if(Auth::user()->canAccessDeath())
      <li class="sidebar-menu-item">
        <a href="{{ route('admin.death-data.index') }}" class="sidebar-link {{ Request::is('admin/death-data*') ? 'active' : '' }}">
          <i class="fa-solid fa-skull fs-5" style="color: #dc3545 !important;"></i>
          <span>ข้อมูลการตาย</span>
        </a>
      </li>
      @endif

      @if(Auth::user()->canAccessBirth())
      <li class="sidebar-menu-item">
        <a href="{{ route('admin.birth-data.index') }}" class="sidebar-link {{ Request::is('admin/birth-data*') ? 'active' : '' }}">
          <i class="fa-solid fa-baby fs-5" style="color: #21c08b !important;"></i>
          <span>ข้อมูลการเกิด</span>
        </a>
      </li>
      @endif

      <li class="sidebar-menu-item">
        <a href="{{ route('admin.settings') }}" class="sidebar-link {{ Request::is('admin/settings') ? 'active' : '' }}">
          <i class="fa-solid fa-gears fs-5" style="color: #21c08b !important;"></i>
          <span>ตั้งค่าระบบ</span>
        </a>
      </li>
      
      <li class="sidebar-menu-item mt-4">
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="sidebar-link text-danger">
          <i class="fa-solid fa-right-from-bracket"></i>
          <span>ออกจากระบบ</span>
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
          @csrf
        </form>
      </li>
    </ul>
  </aside>

  {{-- MAIN WRAPPER --}}
  <div class="main-wrapper">
    <!-- Topbar -->
    <header class="topbar">
      <div class="d-flex align-items-center gap-3">
        <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
          <i class="fa-solid fa-bars"></i>
        </button>
        <h4 class="mb-0 fw-bold text-slate-800">@yield('header_title', 'แดชบอร์ดผู้ดูแลระบบ')</h4>
      </div>

      <!-- User Actions -->
      <div class="d-flex align-items-center gap-3">
        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-3 fw-semibold" style="font-size: 0.85rem; border: 1px solid rgba(0,0,0,0.05);">V.690603_2300</span>
        <div class="dropdown">
          <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-2 border shadow-sm px-3 py-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius: 12px;">
            <i class="fa-solid fa-circle-user text-green fs-5"></i>
            <span class="fw-semibold text-dark">{{ Auth::user()->name }}</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 p-2" style="border-radius: 12px; min-width: 180px;">
            <li><span class="dropdown-item-text text-muted small">สิทธิ์การใช้งาน: {{ ucfirst(Auth::user()->role) }}</span></li>
            <li><hr class="dropdown-divider my-1"></li>
            <li>
              <a class="dropdown-item py-2 px-3 d-flex align-items-center gap-2 text-dark" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal" style="border-radius: 8px;">
                <i class="fa-solid fa-key text-warning"></i> เปลี่ยนรหัสผ่าน
              </a>
            </li>
            <li><hr class="dropdown-divider my-1"></li>
            <li>
              <a class="dropdown-item text-danger d-flex align-items-center gap-2 py-2 px-3" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="border-radius: 8px;">
                <i class="fa-solid fa-right-from-bracket"></i> ออกจากระบบ
              </a>
            </li>
          </ul>
        </div>
      </div>
    </header>

    <!-- Content Area -->
    <main class="p-4">
      @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert" style="border-radius: 14px;">
          <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert" style="border-radius: 14px;">
          <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @yield('content')
    </main>
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
  <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>

  <script>
    // Sidebar toggle for desktop and mobile
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const sidebarClose = document.getElementById('sidebarClose');

    function toggleSidebar(e) {
      if (e) e.preventDefault();
      if (window.innerWidth >= 992) {
        document.body.classList.toggle('sidebar-toggled');
      } else {
        sidebar.classList.toggle('show');
        document.body.classList.toggle('sidebar-overlay-active');
      }
    }

    if (sidebarToggle) {
      sidebarToggle.addEventListener('click', toggleSidebar);
    }
    if (sidebarOverlay) {
      sidebarOverlay.addEventListener('click', toggleSidebar);
    }
    if (sidebarClose) {
      sidebarClose.addEventListener('click', toggleSidebar);
    }
  </script>
  @stack('scripts')
</body>
</html>
