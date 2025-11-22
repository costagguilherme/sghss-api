<?php

namespace App\Services\Externals;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ZoomApi
{
    private $accountId;
    private $clientId;
    private $clientSecret;
    private $baseUrl;

    public function __construct()
    {
        $this->accountId = env('ZOOM_ACCOUNT_ID');
        $this->clientId = env('ZOOM_CLIENT_ID');
        $this->clientSecret = env('ZOOM_CLIENT_SECRET');
        $this->baseUrl = env('ZOOM_BASE_URL');
    }

    private function getAccessToken()
    {
        return Cache::remember('zoom_access_token', 3300, function () {
            $response = Http::asForm()->withBasicAuth(
                $this->clientId,
                $this->clientSecret
            )->post("https://zoom.us/oauth/token", [
                'grant_type' => 'account_credentials',
                'account_id' => $this->accountId,
            ]);

            if ($response->failed()) {
                throw new \Exception('Zoom Auth failed: ' . $response->body());
            }

            return $response->json()['access_token'];
        });
    }

    public function createMeeting($userId = 'me', $data = [])
    {
        $response = Http::withToken($this->getAccessToken())
            ->post("{$this->baseUrl}/users/{$userId}/meetings", $data);

        return $response->json();
    }

    public function deleteMeeting($meetingId)
    {
        $response = Http::withToken($this->getAccessToken())
            ->delete("{$this->baseUrl}/meetings/{$meetingId}");

        return $response->status() === 204;
    }

}
