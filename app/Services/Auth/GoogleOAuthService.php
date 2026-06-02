<?php

namespace App\Services\Auth;

use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\GoogleUser;
use RuntimeException;

class GoogleOAuthService
{
    public function getAuthorizationData(): array
    {
        $provider = $this->provider();
        $url = $provider->getAuthorizationUrl();

        return [
            'url' => $url,
            'state' => $provider->getState(),
        ];
    }

    public function getUserFromCode(string $code): GoogleUser
    {
        $provider = $this->provider();

        $token = $provider->getAccessToken('authorization_code', [
            'code' => $code,
        ]);

        return $provider->getResourceOwner($token);
    }

    private function provider(): Google
    {
        $clientId = (string) config('services.google.client_id');
        $clientSecret = (string) config('services.google.client_secret');
        $redirectUri = (string) config('services.google.redirect');

        if ($clientId === '' || $clientSecret === '' || $redirectUri === '') {
            throw new RuntimeException('Configura GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET y GOOGLE_REDIRECT_URI para usar el acceso con Google.');
        }

        return new Google([
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
            'redirectUri' => $redirectUri,
            'prompt' => config('services.google.prompt', 'select_account'),
        ]);
    }
}
