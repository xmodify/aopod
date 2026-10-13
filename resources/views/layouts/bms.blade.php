<!doctype html>
<html lang="th" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="icon" href="{{ asset('/images/logo.png') }}" type="image/x-icon">
  <title>@yield('title', 'A-EMR - AOPOD')</title>

  {{-- Bootstrap & Icons --}}
  <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/vendor/font-awesome/css/all.min.css') }}">

  {{-- DataTables --}}
  <link rel="stylesheet" href="{{ asset('assets/vendor/datatables/css/dataTables.bootstrap5.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/vendor/datatables-buttons/css/buttons.bootstrap5.min.css') }}">

  {{-- SweetAlert2 --}}
  <script src="{{ asset('assets/vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>

  @stack('styles')

  <style>
    :root {
      --admin-primary: #0f172a;
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
      font-family: 'Inter', 'Noto Sans Thai', 'Prompt', sans-serif;
      margin: 0;
      padding: 0;
    }

    /* === Full-width Main Wrapper (No Sidebar) === */
    .main-wrapper-full {
      margin-left: 0 !important;
      width: 100% !important;
      transition: all 0.3s ease-in-out;
    }

    /* === Topbar === */
    .topbar-frameless {
      height: 42px;
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(15px);
      border-bottom: 1px solid rgba(0, 0, 0, 0.08);
      padding: 0 1rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 99;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
  </style>
</head>
<body>

  <div class="main-wrapper-full">
    <!-- Topbar (Clean & Compact) -->
    <header class="topbar-frameless">
      <div class="d-flex align-items-center gap-2">
        <span class="fw-bold text-slate-800 d-flex align-items-center gap-2" style="font-size: 0.95rem; letter-spacing: -0.2px;">
          <i class="fa-solid fa-hospital-user text-primary" style="font-size: 1rem;"></i>
          @yield('header_title', 'A-EMR')
        </span>
      </div>

      <div class="d-flex align-items-center gap-2">
        @yield('header_right')
      </div>
    </header>

    <!-- Content Area -->
    <main class="p-2 px-md-3 py-md-2">
      @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert" style="border-radius: 12px;">
          <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      @yield('content')
    </main>
  </div>

  {{-- Scripts --}}
  <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
  <script src="{{ asset('assets/vendor/datatables/js/dataTables.bootstrap5.min.js') }}"></script>

  @stack('scripts')
</body>
</html>
