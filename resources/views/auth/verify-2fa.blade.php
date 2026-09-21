<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ยืนยันรหัส OTP (2FA หมอพร้อม) - AOPOD</title>
    
    <!-- Google Fonts & Bootstrap & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --primary-green: #18a573;
            --primary-gradient: linear-gradient(135deg, #18a573 0%, #21c08b 100%);
        }
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f2e2b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #334155;
            padding: 1.5rem;
        }
        .verify-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(33, 192, 139, 0.3);
            border-radius: 28px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            max-width: 460px;
            width: 100%;
        }
        .otp-input-field {
            letter-spacing: 0.6rem;
            padding-left: 1.5rem;
            font-size: 1.8rem;
            text-align: center;
            font-weight: 700;
            border-radius: 16px;
            border: 2px solid #e2e8f0;
            transition: all 0.25s ease;
            background-color: #f8fafc;
            color: #0f172a;
        }
        .otp-input-field:focus {
            border-color: #18a573;
            background-color: #ffffff;
            box-shadow: 0 0 0 0.25rem rgba(24, 165, 115, 0.2);
            outline: none;
        }
        .btn-verify {
            background: var(--primary-gradient);
            border: none;
            border-radius: 14px;
            font-weight: 600;
            letter-spacing: 0.02em;
            transition: all 0.2s ease;
        }
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(24, 165, 115, 0.35);
            background: linear-gradient(135deg, #158f63 0%, #1da87a 100%);
        }
        .shield-icon-wrapper {
            width: 76px;
            height: 76px;
            border-radius: 22px;
            background: linear-gradient(135deg, rgba(24, 165, 115, 0.15) 0%, rgba(33, 192, 139, 0.15) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            color: var(--primary-green);
            margin: 0 auto 1.5rem;
        }
        .channel-badge {
            background: rgba(24, 165, 115, 0.08);
            border: 1px solid rgba(24, 165, 115, 0.2);
            color: #0d6e4d;
            border-radius: 12px;
            padding: 0.6rem 1rem;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

<div class="verify-card p-4 p-sm-5 text-center">
    <!-- Icon -->
    <div class="shield-icon-wrapper shadow-sm">
        <i class="fa-solid fa-shield-halved"></i>
    </div>

    <h4 class="fw-bold text-dark mb-2">ยืนยันตัวตน 2 ขั้นตอน (2FA)</h4>
    <p class="text-secondary small mb-3" style="line-height: 1.6;">
        ระบบส่งรหัส OTP 6 หลักไปยังบัญชีของท่านเรียบร้อยแล้ว<br>
        กรุณาตรวจสอบข้อความแจ้งเตือนทาง:
    </p>

    <!-- Channels Badge -->
    <div class="channel-badge mb-4 d-flex justify-content-center align-items-center gap-3">
        <span><i class="fa-solid fa-mobile-screen text-success me-1"></i> <strong>แอปหมอพร้อม</strong></span>
        <span class="text-muted">|</span>
        <span><i class="fa-brands fa-line text-success me-1"></i> <strong>LINE OA หมอพร้อม</strong></span>
    </div>

    <!-- CID Missing Error Alert Banner -->
    @isset($cidError)
        <div class="alert alert-danger text-start small mb-4 shadow-sm" role="alert" style="border-radius: 12px;">
            <i class="fa-solid fa-triangle-exclamation me-2 fs-6"></i>
            <strong>แจ้งเตือน:</strong> {{ $cidError }}
        </div>
    @endisset

    <!-- Form for Verification -->
    <form action="{{ route('auth.2fa.verify') }}" method="POST" class="mb-3">
        @csrf
        
        <div class="mb-3">
            <input type="text" 
                   name="otp_code" 
                   id="otp_code" 
                   class="form-control otp-input-field shadow-sm @error('otp_code') is-invalid @enderror" 
                   value="{{ old('otp_code') }}"
                   maxlength="6" 
                   placeholder="------" 
                   pattern="[0-9]{6}" 
                   required 
                   autocomplete="one-time-code"
                   @isset($cidError) disabled @else autofocus @endisset>
            @error('otp_code')
                <div class="text-danger text-center mt-2 small fw-bold">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <button type="submit" class="btn btn-verify btn-lg w-100 text-white shadow-sm py-2.5 d-flex align-items-center justify-content-center gap-2" @isset($cidError) disabled @endisset>
            <i class="fa-solid fa-key"></i> ยืนยันรหัส OTP
        </button>
    </form>

    <!-- Form for Logout / Back to Login -->
    <form action="{{ route('logout') }}" method="POST" class="mb-3">
        @csrf
        <button type="submit" class="btn btn-light w-100 py-2 rounded-3 text-secondary border shadow-sm d-flex align-items-center justify-content-center gap-2 small">
            <i class="fa-solid fa-arrow-left"></i> กลับไปหน้าเข้าสู่ระบบ
        </button>
    </form>

    @empty($cidError)
        <!-- Timer / Resend Links -->
        <div class="text-secondary small pt-2">
            <div id="resend-container">
                <form action="{{ route('auth.2fa.resend') }}" method="POST" id="resend-form" class="d-none">
                    @csrf
                    ไม่ได้รับรหัส OTP ใช่ไหม? 
                    <button type="submit" class="btn btn-link btn-sm p-0 text-success text-decoration-none fw-bold">
                        ส่งรหัสใหม่อีกครั้ง
                    </button>
                </form>
                <span id="countdown-text" class="text-muted">
                    <i class="fa-regular fa-clock me-1"></i> ขอรหัสใหม่ได้ใน <strong id="timer-seconds" class="text-dark">120</strong> วินาที
                </span>
            </div>
        </div>
    @endempty
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const otpInput = document.getElementById('otp_code');

        // Auto submit form once user enters exactly 6 digits
        if (otpInput) {
            otpInput.addEventListener('input', function() {
                // Keep only numeric characters
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value.length === 6) {
                    this.closest('form').submit();
                }
            });
        }

        @empty($cidError)
        const timerSeconds = document.getElementById('timer-seconds');
        const countdownText = document.getElementById('countdown-text');
        const resendForm = document.getElementById('resend-form');

        @php
            $lastSent = session('moph_alert_last_sent');
            $secondsSinceLast = $lastSent ? (time() - $lastSent) : 120;
            $remaining = 120 - $secondsSinceLast;
            if ($remaining < 0) $remaining = 0;
        @endphp
        
        let timeLeft = {{ $remaining }};

        if (timerSeconds && countdownText && resendForm) {
            if (timeLeft <= 0) {
                countdownText.classList.add('d-none');
                resendForm.classList.remove('d-none');
            } else {
                const timerInterval = setInterval(function () {
                    timeLeft--;
                    timerSeconds.innerText = Math.ceil(timeLeft);

                    if (timeLeft <= 0) {
                        clearInterval(timerInterval);
                        countdownText.classList.add('d-none');
                        resendForm.classList.remove('d-none');
                    }
                }, 1000);
            }
        }
        @endempty

        @if (session('success_resend'))
            Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: '{{ session('success_resend') }}',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#18a573'
            }).then(() => {
                if (otpInput) otpInput.focus();
            });
        @endif
    });
</script>
</body>
</html>
