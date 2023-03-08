<?php

namespace RDN\Error\Logger;

use Stringable;

class FileLogger extends BaseLogger
{
    public function log($level, Stringable|string $message, array $context = [], ?string $tag = null): void
    {
        // TODO: Implement log() method.
    }

    public function isNewMessage(): bool
    {
        // TODO: Implement isNewMessage() method.
        return false;
    }
}
