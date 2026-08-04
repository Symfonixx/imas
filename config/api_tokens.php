<?php

return [

    /*
    |--------------------------------------------------------------------------
    | External / chatbot API tokens
    |--------------------------------------------------------------------------
    |
    | Comma-separated Bearer tokens accepted by the auth.api_token middleware.
    | Clients must send: Authorization: Bearer <token>
    | (or X-Api-Token: <token>).
    |
    */

    'tokens' => array_values(array_filter(array_map(
        static fn (string $token): string => trim($token),
        explode(',', (string) env('API_TOKENS', '')),
    ))),

];
