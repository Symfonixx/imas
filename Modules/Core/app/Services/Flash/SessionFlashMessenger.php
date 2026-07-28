<?php

namespace Modules\Core\Services\Flash;

use Modules\Core\Contracts\Flash\FlashMessengerInterface;

class SessionFlashMessenger implements FlashMessengerInterface
{
    public function success(?string $message = null): void
    {
        // Pass null through so the macro falls back to its default message;
        // an empty string here would render no toast at all.
        session()->flushMessage(true, $message);
    }

    public function error(string $message): void
    {
        session()->flushMessage(false, $message);
    }
}
