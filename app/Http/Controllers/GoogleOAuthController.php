<?php

namespace App\Http\Controllers;

use App\Models\GoogleToken;
use Google\Client as GoogleClient;
use Google\Service\Calendar;
use Illuminate\Http\Request;

class GoogleOAuthController extends Controller
{
    public function redirect()
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes([Calendar::CALENDAR]);

        return redirect()->away($client->createAuthUrl());
    }

    public function callback(Request $request)
    {
        $client = new GoogleClient();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));

        $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));

        if (isset($token['error'])) {
            abort(500, 'Errore OAuth Google: ' . ($token['error_description'] ?? $token['error']));
        }

        $model = GoogleToken::firstOrCreate(['provider' => 'google']);
        $model->access_token = $token['access_token'] ?? null;
        $model->refresh_token = $token['refresh_token'] ?? $model->refresh_token; // refresh token arriva solo la prima volta
        $model->expires_at = now()->addSeconds($token['expires_in'] ?? 3600);
        $model->save();

        return redirect('/admin')->with('status', 'Google connesso correttamente.');
    }
}
