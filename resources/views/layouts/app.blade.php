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

          <a class="navbar-brand d-flex align-items-center text-primary brand-title fw-bold" href="{{ url('web/claim') }}">
              <i class="bi bi-coin fs-5 text-warning me-2"></i></i> Claim
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
