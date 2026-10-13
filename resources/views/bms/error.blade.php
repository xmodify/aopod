<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'แจ้งเตือนการเชื่อมต่อ HOSxP' }} - A-EMR</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: #f8fafc;
        }
        .error-card {
            background: #ffffff;
            color: #0f172a;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
            max-width: 580px;
            width: 100%;
            padding: 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .error-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #ef4444, #f59e0b);
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            background: #fef2f2;
            color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2rem;
            margin: 0 auto 1.5rem;
            border: 2px solid #fee2e2;
        }
        .btn-back {
            background: #0f172a;
            color: #ffffff;
            border-radius: 12px;
            padding: 0.65rem 1.8rem;
            font-weight: 500;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-back:hover {
            background: #1e293b;
            color: #38bdf8;
            transform: translateY(-2px);
        }
        .session-info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
            text-align: left;
            font-size: 0.85rem;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon-circle">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h3 class="fw-bold mb-3 text-danger">{{ $title ?? 'ไม่สามารถเข้าใช้งานได้' }}</h3>
        <p class="text-secondary leading-relaxed mb-4">
            {{ $message ?? 'เกิดข้อผิดพลาดในการตรวจสอบสิทธิ์การเชื่อมต่อกับ HOSxP' }}
        </p>

        @if(!empty($sessionData))
        <div class="session-info-box">
            <div class="fw-bold text-dark mb-1"><i class="fa-solid fa-circle-info text-primary me-1"></i> ข้อมูลที่ได้รับจาก HOSxP:</div>
            <div><strong>ผู้ใช้งาน:</strong> {{ $sessionData['name'] ?? '-' }}</div>
            <div><strong>รหัสสถานพยาบาล:</strong> {{ $sessionData['hospital_code'] ?? '-' }}</div>
            <div><strong>ตำแหน่ง/แผนก:</strong> {{ $sessionData['position'] ?? '-' }} {{ $sessionData['department'] ? '(' . $sessionData['department'] . ')' : '' }}</div>
        </div>
        @endif

        <div class="mt-4 pt-2">
            <a href="{{ url('/') }}" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i> กลับสู่หน้าหลัก
            </a>
        </div>
    </div>
</body>
</html>
