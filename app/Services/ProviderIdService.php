<?php

namespace App\Services;

use App\Models\MainSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProviderIdService
{
    /**
     * Get Provider ID / Health ID configuration value from main_setting table or config fallback.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $key, $default = null)
    {
        $dbValue = MainSetting::get($key);
        if ($dbValue !== null && $dbValue !== '') {
            return $dbValue;
        }

        return match ($key) {
            'provider_id_active' => config('moph.provider_id_active', $default ?? 'Y'),
            'health_id_client_id' => config('moph.health_id.client_id', $default),
            'health_id_client_secret' => config('moph.health_id.client_secret', $default),
            'provider_id_client_id' => config('moph.provider_id.client_id', $default),
            'provider_id_secret_key' => config('moph.provider_id.secret_key', $default),
            default => $default,
        };
    }

    /**
     * Check if Provider ID login is active.
     *
     * @return bool
     */
    public function isProviderIdActive(): bool
    {
        $active = $this->getConfig('provider_id_active', 'Y');
        return strtoupper((string) $active) === 'Y' || $active === '1' || $active === true;
    }

    /**
     * Build the Health ID OAuth Redirect URL.
     *
     * @param string $state
     * @return string
     */
    public function getHealthIdAuthUrl(string $state): string
    {
        $clientId = $this->getConfig('health_id_client_id');
        $redirectUri = $this->getRedirectUri();
        $authUrl = config('moph.health_id.auth_url', 'https://moph.id.th/oauth/redirect');

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'state' => $state,
        ]);

        return $authUrl . '?' . $query;
    }

    /**
     * Get redirect URI formatted properly (Fixed in code to callback route).
     *
     * @return string
     */
    public function getRedirectUri(): string
    {
        return route('auth.health-id.callback');
    }

    /**
     * Step 1: Exchange Authorization Code for Health ID Token.
     *
     * @param string $code
     * @return string
     * @throws \Exception
     */
    public function exchangeHealthIdToken(string $code): string
    {
        $clientId = $this->getConfig('health_id_client_id');
        $clientSecret = $this->getConfig('health_id_client_secret');
        $redirectUri = $this->getRedirectUri();
        $tokenUrl = config('moph.health_id.token_url', 'https://moph.id.th/api/v1/token');

        $response = Http::withoutVerifying()->asForm()->post($tokenUrl, [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if (!$response->successful()) {
            Log::error('Health ID Token Exchange Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('ไม่สามารถขอรับ Token จาก Health ID ได้: ' . ($response->json('message') ?? $response->body()));
        }

        $token = $response->json('data.access_token') ?? $response->json('access_token');
        if (empty($token)) {
            throw new \Exception('ไม่พบ access_token ในผลลัพธ์จาก Health ID');
        }

        return $token;
    }

    /**
     * Step 2: Exchange Health ID Token for Provider ID Access Token.
     *
     * @param string $healthIdToken
     * @return string
     * @throws \Exception
     */
    public function exchangeProviderIdToken(string $healthIdToken): string
    {
        $clientId = $this->getConfig('provider_id_client_id');
        $secretKey = $this->getConfig('provider_id_secret_key');
        $tokenUrl = config('moph.provider_id.token_url', 'https://provider.id.th/api/v1/services/token');

        $response = Http::withoutVerifying()->post($tokenUrl, [
            'client_id' => $clientId,
            'secret_key' => $secretKey,
            'token_by' => 'Health ID',
            'token' => $healthIdToken,
        ]);

        if ($response->status() === 400) {
            throw new \Exception('บัญชีนี้ยังไม่ได้รับสิทธิ์หรือไม่มีข้อมูลเลขผู้ให้บริการ (Provider ID) ในระบบกระทรวงสาธารณสุข');
        }

        if (!$response->successful()) {
            Log::error('Provider ID Token Exchange Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('ไม่สามารถขอรับ Token จาก Provider ID ได้: ' . ($response->json('message') ?? $response->body()));
        }

        $token = $response->json('data.access_token') ?? $response->json('access_token');
        if (empty($token)) {
            throw new \Exception('ไม่พบ access_token ในผลลัพธ์จาก Provider ID');
        }

        return $token;
    }

    /**
     * Step 3: Fetch Provider Profile, hash_cid, and FDH Token.
     *
     * @param string $providerToken
     * @return array
     * @throws \Exception
     */
    public function getProviderProfile(string $providerToken): array
    {
        $clientId = $this->getConfig('provider_id_client_id');
        $secretKey = $this->getConfig('provider_id_secret_key');
        $profileUrl = config('moph.provider_id.profile_url', 'https://provider.id.th/api/v1/services/profile');

        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'Bearer ' . $providerToken,
            'client-id' => $clientId,
            'secret-key' => $secretKey,
        ])->get($profileUrl, [
            'moph_idp_permission' => 1
        ]);

        if (!$response->successful()) {
            Log::error('Provider Profile Request Failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception('ไม่สามารถดึงข้อมูล Profile จาก Provider ID ได้: ' . ($response->json('message') ?? $response->body()));
        }

        $data = $response->json('data') ?? $response->json();
        return is_array($data) ? $data : [];
    }
}
