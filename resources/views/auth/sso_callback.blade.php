<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันตัวตน Provider ID - AOPOD</title>
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/vendor/font-awesome/css/all.min.css') }}">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0fdf4 0%, #e2e8f0 100%);
            font-family: 'Inter', 'Noto Sans Thai', sans-serif;
            margin: 0;
            padding: 20px;
        }
        .status-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border-radius: 24px;
            padding: 2.5rem;
            max-width: 440px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(33, 192, 139, 0.2);
        }
        .icon-box {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem auto;
            font-size: 2.5rem;
        }
        .icon-success {
            background: rgba(24, 165, 115, 0.12);
            color: #18a573;
        }
        .icon-error {
            background: rgba(220, 53, 69, 0.12);
            color: #dc3545;
        }
    </style>
</head>
<body>

<div class="status-card">
    @if($success)
        <div class="icon-box icon-success">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <h4 class="fw-bold text-dark mb-2">เข้าสู่ระบบสำเร็จ!</h4>
        <p class="text-secondary mb-4">{{ $message ?? 'กำลังนำท่านเข้าสู่ระบบ...' }}</p>
        <div class="spinner-border text-success" role="status" style="width: 2rem; height: 2rem;">
            <span class="visually-hidden">Loading...</span>
        </div>
    @else
        <div class="icon-box icon-error">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>
        <h4 class="fw-bold text-dark mb-2">เข้าสู่ระบบไม่สำเร็จ</h4>
        <p class="text-secondary mb-4">{{ $message ?? 'ไม่สามารถยืนยันตัวตนได้' }}</p>
        <button type="button" class="btn btn-secondary px-4 py-2 rounded-pill fw-bold" onclick="window.close();">ปิดหน้าต่างนี้</button>
    @endif
</div>

<script>
    (function() {
        const isSuccess = {{ $success ? 'true' : 'false' }};
        const message = @json($message);
        const redirectUrl = @json($redirectUrl ?? url('/'));

        // Post message to parent window if opened as popup
        if (window.opener && !window.opener.closed) {
            try {
                window.opener.postMessage({
                    type: 'PROVIDER_ID_AUTH_RESULT',
                    status: isSuccess ? 'success' : 'error',
                    message: message,
                    redirectUrl: redirectUrl
                }, '*');
            } catch (e) {
                console.error('Error posting message to opener:', e);
            }

            if (isSuccess) {
                setTimeout(() => {
                    window.close();
                }, 1000);
            }
        } else {
            // If opened in standalone window/tab
            if (isSuccess) {
                setTimeout(() => {
                    window.location.href = redirectUrl;
                }, 1200);
            }
        }
    })();
</script>

</body>
</html>
