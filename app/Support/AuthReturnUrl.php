<?php

namespace App\Support;

use Illuminate\Http\Request;

class AuthReturnUrl
{
    public static function remember(Request $request): ?string
    {
        $returnUrl = $request->input('return');

        if (! is_string($returnUrl) || $returnUrl === '') {
            return null;
        }

        $parts = parse_url($returnUrl);

        if ($parts === false || isset($parts['user']) || isset($parts['pass'])) {
            return null;
        }

        if (isset($parts['host'])) {
            if (strcasecmp($parts['host'], $request->getHost()) !== 0
                || (isset($parts['scheme']) && ! in_array(strtolower($parts['scheme']), ['http', 'https'], true))
                || (isset($parts['port']) && $parts['port'] !== $request->getPort())) {
                return null;
            }
        } elseif (isset($parts['scheme']) || ! str_starts_with($returnUrl, '/') || str_starts_with($returnUrl, '//')) {
            return null;
        }

        $path = '/'.ltrim($parts['path'] ?? '/', '/');

        if (in_array($path, ['/login', '/register'], true) || str_starts_with($path, '/auth/google')) {
            return null;
        }

        $destination = $path.(isset($parts['query']) ? '?'.$parts['query'] : '');
        $request->session()->put('url.intended', $destination);

        return $destination;
    }
}
