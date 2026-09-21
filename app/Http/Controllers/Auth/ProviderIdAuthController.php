<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ProviderIdService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProviderIdAuthController extends Controller
{
    protected ProviderIdService $providerIdService;

    public function __construct(ProviderIdService $providerIdService)
    {
        $this->providerIdService = $providerIdService;
    }

    /**
     * Redirect user to Health ID / MOPH SSO Provider.
     */
    public function redirectToProvider(Request $request)
    {
        if (!$this->providerIdService->isProviderIdActive()) {
            return redirect()->to(url('/'))->with('error', 'ระบบปิดการเข้าสู่ระบบด้วย Provider ID ชั่วคราว');
        }

        $clientId = $this->providerIdService->getConfig('health_id_client_id');
        if (empty($clientId)) {
            return redirect()->to(url('/'))->with('error', 'ยังไม่ได้ตั้งค่า Client ID ของ Health ID ในระบบ');
        }

        $state = Str::random(40);
        $request->session()->put('oauth_state', $state);

        $authUrl = $this->providerIdService->getHealthIdAuthUrl($state);

        return redirect()->away($authUrl);
    }

    /**
     * Handle OAuth Callback from Health ID.
     */
    public function handleProviderCallback(Request $request)
    {
        // 1. Check for error in callback
        if ($request->has('error')) {
            $errorMessage = $request->get('error_description') ?? $request->get('error') ?? 'เกิดข้อผิดพลาดในการยืนยันตัวตน';
            return $this->returnAuthResponse(false, $errorMessage);
        }

        // 2. Validate CSRF State
        $sessionState = $request->session()->get('oauth_state');
        $callbackState = $request->get('state');

        if (empty($sessionState) || empty($callbackState) || !hash_equals($sessionState, $callbackState)) {
            Log::warning('Provider ID SSO State Mismatch', [
                'session_state' => $sessionState,
                'callback_state' => $callbackState,
            ]);
            return $this->returnAuthResponse(false, 'CSRF State ไม่ถูกต้อง หรือเซสชันหมดอายุ กรุณาลองเข้าสู่ระบบใหม่อีกครั้ง');
        }

        $request->session()->forget('oauth_state');

        $code = $request->get('code');
        if (empty($code)) {
            return $this->returnAuthResponse(false, 'ไม่พบ Authorization Code จากระบบ Health ID');
        }

        try {
            // Step 1: Exchange Code -> Health ID Token
            $healthIdToken = $this->providerIdService->exchangeHealthIdToken($code);

            // Step 2: Exchange Health ID Token -> Provider ID Token
            $providerToken = $this->providerIdService->exchangeProviderIdToken($healthIdToken);

            // Step 3: Get Provider Profile & hash_cid
            $profileData = $this->providerIdService->getProviderProfile($providerToken);

            $hashCid = $profileData['hash_cid'] ?? null;
            $providerId = $profileData['provider_id'] ?? ($profileData['id'] ?? ($profileData['provider_code'] ?? null));
            $hospcode = $profileData['hospcode'] ?? ($profileData['hospital_code'] ?? ($profileData['hcode'] ?? null));
            $position = $profileData['position'] ?? ($profileData['position_name'] ?? ($profileData['title'] ?? null));

            if (empty($hashCid)) {
                Log::warning('Provider ID Profile without hash_cid', ['profile' => $profileData]);
                return $this->returnAuthResponse(false, 'ไม่พบข้อมูล hash_cid จากระบบ Provider ID');
            }

            // Step 4: Match User with SHA2(cid, 256) = $hashCid
            $user = User::whereRaw('SHA2(cid, 256) = ?', [$hashCid])
                ->where(function ($query) {
                    $query->where('active', 'Y')
                          ->orWhere('active', '1');
                })
                ->first();

            if (!$user) {
                Log::notice('Provider ID User not found for hash_cid', ['hash_cid' => $hashCid]);
                return $this->returnAuthResponse(false, 'เลขบัตรประชาชนของท่านยังไม่ผ่านการลงทะเบียน หรือบัญชีถูกระงับการใช้งานในระบบ AOPOD');
            }

            // Extract FDH token from organization list matching hospital code (same as h-rims)
            $orgData = $profileData['organization'] ?? [];
            $mophFdhToken = null;
            $targetHcode = $user->hospcode ?? $hospcode;

            if (!empty($orgData) && is_array($orgData)) {
                // 1. Find organization matching user hospital code
                foreach ($orgData as $org) {
                    $orgHcode = $org['hospital_code'] ?? ($org['hcode'] ?? ($org['unit_code'] ?? null));
                    if ($targetHcode && $orgHcode == $targetHcode && !empty($org['moph_access_token_idp_fdh'])) {
                        $mophFdhToken = $org['moph_access_token_idp_fdh'];
                        break;
                    }
                }
                // 2. If not matched with target hospcode, fallback to first organization with FDH token
                if (empty($mophFdhToken)) {
                    foreach ($orgData as $org) {
                        if (!empty($org['moph_access_token_idp_fdh'])) {
                            $mophFdhToken = $org['moph_access_token_idp_fdh'];
                            break;
                        }
                    }
                }
            }

            // Fallback to direct field, Provider ID token, or Health ID token
            if (empty($mophFdhToken)) {
                $mophFdhToken = $profileData['moph_access_token_idp_fdh'] ?? ($profileData['fdh_token'] ?? ($providerToken ?? ($healthIdToken ?? null)));
            }

            // Update user record with latest Provider ID and FDH Token
            if (!empty($providerId)) {
                $user->provider_id = $providerId;
            }
            if (!empty($mophFdhToken)) {
                $user->moph_token = $mophFdhToken;
                $user->moph_token_expire = now()->addHours(12);
            }
            if (empty($user->hospcode) && !empty($hospcode)) {
                $user->hospcode = $hospcode;
            }
            if (empty($user->position) && !empty($position)) {
                $user->position = $position;
            }
            $user->save();

            // Store token and profile in session
            session([
                'moph_fdh_token' => $mophFdhToken,
                'provider_id' => $providerId,
                'provider_profile' => $profileData,
                'moph_alert_2fa_verified' => true, // Provider ID SSO is already 2FA verified via MOPH IDP
            ]);

            // Authenticate user
            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            return $this->returnAuthResponse(true, 'เข้าสู่ระบบสำเร็จ ยินดีต้อนรับ ' . $user->name, route('manage.index'));

        } catch (\Exception $e) {
            Log::error('Provider ID SSO Callback Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return $this->returnAuthResponse(false, $e->getMessage());
        }
    }

    /**
     * Return formatted response view for popup and redirect.
     */
    protected function returnAuthResponse(bool $success, string $message, ?string $redirectUrl = null)
    {
        return view('auth.sso_callback', [
            'success' => $success,
            'message' => $message,
            'redirectUrl' => $redirectUrl ?? url('/'),
        ]);
    }
}
