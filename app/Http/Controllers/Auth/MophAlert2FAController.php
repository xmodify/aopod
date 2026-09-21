<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\MainSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MophAlert2FAController extends Controller
{
    /**
     * Show the 2FA OTP verification page.
     */
    public function index()
    {
        if (!auth()->check()) {
            return redirect()->to(url('/'));
        }

        // If user already verified 2FA, redirect to intended target
        if (session('moph_alert_2fa_verified') === true) {
            return redirect()->intended(route('manage.index'));
        }

        $user = auth()->user();

        // Check if user CID is missing or invalid
        if (empty($user->cid) || strlen($user->cid) !== 13) {
            return view('auth.verify-2fa', [
                'cidError' => 'บัญชีของท่านยังไม่ระบุเลขบัตรประชาชน (CID 13 หลัก) ในระบบ กรุณาติดต่อผู้ดูแลระบบเพื่อเพิ่มข้อมูล CID สำหรับรับรหัส OTP'
            ]);
        }

        // If no active OTP generated yet or expired, generate and send a new one
        if (!session()->has('moph_alert_otp') || time() > session('moph_alert_otp_expires')) {
            $this->generateAndSendOTP();
        }

        return view('auth.verify-2fa');
    }

    /**
     * Verify the submitted OTP code.
     */
    public function verifyOTP(Request $request)
    {
        $request->validate([
            'otp_code' => 'required|string|size:6',
        ], [
            'otp_code.required' => 'กรุณากรอกรหัส OTP',
            'otp_code.size' => 'รหัส OTP ต้องมีขนาด 6 หลัก',
        ]);

        $enteredOtp = $request->otp_code;
        $cachedOtp = session('moph_alert_otp');
        $expiresAt = session('moph_alert_otp_expires');

        if (!$cachedOtp || !$expiresAt || time() > $expiresAt) {
            return redirect()->back()
                ->withErrors(['otp_code' => 'รหัส OTP หมดอายุแล้ว กรุณากดส่งรหัสใหม่อีกครั้ง'])
                ->withInput($request->only('otp_code'));
        }

        if ($enteredOtp === $cachedOtp) {
            // Mark 2FA as verified in session
            session(['moph_alert_2fa_verified' => true]);
            
            // Clean up temporary OTP data
            session()->forget(['moph_alert_otp', 'moph_alert_otp_expires', 'moph_alert_last_sent']);

            return redirect()->intended(route('manage.index'));
        }

        return redirect()->back()
            ->withErrors(['otp_code' => 'รหัส OTP ไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง'])
            ->withInput($request->only('otp_code'));
    }

    /**
     * Resend a new OTP.
     */
    public function resendOTP()
    {
        $lastSent = session('moph_alert_last_sent');
        $cooldown = 120; // 120 seconds cooldown

        if ($lastSent && (time() - $lastSent) < $cooldown) {
            $secondsLeft = $cooldown - (time() - $lastSent);
            return redirect()->back()->withErrors(['resend' => "กรุณารอสักครู่ (เหลือเวลาอีก {$secondsLeft} วินาที)"]);
        }

        $this->generateAndSendOTP();

        return redirect()->back()->with('success_resend', 'ส่งรหัส OTP ใหม่เรียบร้อยแล้ว');
    }

    /**
     * Helper to generate and trigger sending the OTP.
     */
    protected function generateAndSendOTP()
    {
        $user = auth()->user();
        $otp = (string) rand(100000, 999999);
        
        // Save to session (120 seconds lifetime)
        session([
            'moph_alert_otp' => $otp,
            'moph_alert_otp_expires' => time() + 120,
            'moph_alert_last_sent' => time(),
        ]);

        // Send via Moph Alert API
        $this->sendOTPViaMophAlert($user->cid ?? '', $otp);
    }

    /**
     * Core API integration to call MOPH Alert API (Mor Prom App & LINE OA).
     */
    protected function sendOTPViaMophAlert($cid, $otp)
    {
        if (empty($cid)) {
            Log::warning('Cannot send Moph Alert OTP: User CID is empty.');
            return false;
        }

        $clientId = MainSetting::get('moph_alert_client_id', config('moph.alert.client_id', ''));
        $clientSecret = MainSetting::get('moph_alert_client_secret', config('moph.alert.client_secret', ''));
        $url = config('moph.alert.url', 'https://morpromt2c.moph.go.th/alert/v3.1/messages');

        $boldDigits = [
            '0' => '𝟬', '1' => '𝟭', '2' => '𝟮', '3' => '𝟯', '4' => '𝟰',
            '5' => '𝟱', '6' => '𝟲', '7' => '𝟳', '8' => '𝟴', '9' => '𝟵'
        ];
        $boldOtp = strtr($otp, $boldDigits);

        try {
            Log::info("Attempting to send OTP $otp via Moph Alert for CID $cid");

            $response = Http::withoutVerifying()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'client-key' => $clientId,
                    'secret-key' => $clientSecret,
                ])
                ->post($url, [
                    'cid' => [(string)$cid], // Must be array of string CID
                    'messages' => [
                        [
                            'text' => "รหัสยืนยันตัวตน (2FA) สำหรับเข้าระบบ AOPOD ของท่านคือ $boldOtp",
                            'type' => 'text'
                        ]
                    ],
                    'message_title' => "รหัส OTP เข้าสู่ระบบ AOPOD",
                    'message_html' => "<div>รหัสยืนยันตัวตน (2FA) สำหรับเข้าระบบ AOPOD ของท่านคือ <strong>$otp</strong></div>",
                    'message_text' => "รหัส OTP ของท่านคือ $otp",
                    'message_type' => "HPT"
                ]);

            $data = $response->json();
            $msgCode = $data['message_code'] ?? ($data['code'] ?? null);

            if ($response->successful() && ($msgCode == 200 || is_null($msgCode))) {
                Log::info("Moph Alert OTP sent successfully for CID: $cid");
                return true;
            } else {
                Log::error("Failed to send Moph Alert OTP. HTTP Status: " . $response->status() . " Body: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Error sending Moph Alert OTP: " . $e->getMessage());
            return false;
        }
    }
}
