<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    /**
     * Validate a Bearer (or X-Api-Token) against configured API_TOKENS.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $provided = $request->bearerToken() ?: $request->header('X-Api-Token');
        $tokens = config('api_tokens.tokens', []);

        if (! is_string($provided) || $provided === '' || ! is_array($tokens) || $tokens === []) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        foreach ($tokens as $token) {
            if (is_string($token) && $token !== '' && hash_equals($token, $provided)) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
