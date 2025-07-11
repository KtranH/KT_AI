<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Http;

class TurnStileService
{
    /**
     * Verify turnstile
     * @param string $token Token
     * @param string $remoteip Remote IP
     * @return array Kết quả
     */
    public function verifyTurnstile($token, $remoteip)
    {
        $response = Http::asForm()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'secret' => env('TURNSTILE_SECRET_KEY'),
            'response' => $token,
            'remoteip' => $remoteip,
        ]);

        return $response->json();
    }
}
