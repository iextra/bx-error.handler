<?php

namespace RDN\Error\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Stringable;

abstract class BaseLogger implements LoggerInterface, MessageStatusInterface
{
    abstract public function log($level, string|Stringable $message, array $context = [], ?string $tag = null): void;

    abstract public function isNewMessage(): bool;

    public function emergency(string|Stringable $message, array $context = [], ?string $tag = null): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context, $tag);
    }

    public function alert(string|Stringable $message, array $context = [], ?string $tag = null): void
    {
        $this->log(LogLevel::ALERT, $message, $context, $tag);
    }

    public function critical(string|Stringable $message, array $context = [], ?string $tag = null): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context, $tag);
    }

    public function error(string|Stringable $message, array $context = [], ?string $tag = null): void
    {
        $this->log(LogLevel::ERROR, $message, $context, $tag);
    }

    public function warning(string|Stringable $message, array $context = [], ?string $tag = null): void
    {
        $this->log(LogLevel::WARNING, $message, $context, $tag);
    }

    public function notice(string|Stringable $message, array $context = [], ?string $tag = null): void
    {
        $this->log(LogLevel::NOTICE, $message, $context, $tag);
    }

    public function info(string|Stringable $message, array $context = [], ?string $tag = null): void
    {
        $this->log(LogLevel::INFO, $message, $context, $tag);
    }

    public function debug(string|Stringable $message, array $context = [], ?string $tag = null): void
    {
        $this->log(LogLevel::DEBUG, $message, $context, $tag);
    }
}
