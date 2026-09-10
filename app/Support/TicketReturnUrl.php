<?php

namespace App\Support;

use Illuminate\Http\Request;

class TicketReturnUrl
{
    /**
     * Resolve uma URL de retorno interna e restrita às listagens de tickets.
     */
    public static function resolve(Request $request, string $fallbackRoute = 'tickets.index'): string
    {
        $fallback = route($fallbackRoute);
        $candidate = $request->input('return_to');

        if (!is_string($candidate) || $candidate === '') {
            return $fallback;
        }

        $candidateParts = parse_url($candidate);
        $appParts = parse_url(url('/'));

        if (!is_array($candidateParts) || !is_array($appParts)) {
            return $fallback;
        }

        $sameHost = isset($candidateParts['host'], $appParts['host'])
            && strcasecmp($candidateParts['host'], $appParts['host']) === 0;
        $samePort = ($candidateParts['port'] ?? null) === ($appParts['port'] ?? null);
        $safeScheme = in_array($candidateParts['scheme'] ?? '', ['http', 'https'], true);
        $allowedPaths = self::allowedPaths();

        if (!$sameHost || !$samePort || !$safeScheme || !in_array($candidateParts['path'] ?? '', $allowedPaths, true)) {
            return $fallback;
        }

        return $candidate;
    }

    private static function allowedPaths(): array
    {
        return [
            parse_url(route('tickets.index'), PHP_URL_PATH),
            parse_url(route('tickets.my'), PHP_URL_PATH),
            parse_url(route('tickets.pendentes'), PHP_URL_PATH),
            parse_url(route('tickets.cliente.index'), PHP_URL_PATH),
            parse_url(route('home'), PHP_URL_PATH),
        ];
    }
}
