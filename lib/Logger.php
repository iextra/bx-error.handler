<?php

namespace RDN\Error;

use RDN\Error\Logger\DbLogger;
use RDN\Error\Notification\Manager;
use RDN\Error\Logger\FileLogger;
use Psr\Log\LogLevel;
use Stringable;

class Logger
{
    private static function log(
        string $level,
        string|Stringable $message,
        array $context = [],
        ?string $tag = null
    ): void
    {
        if (defined('RDN_STOP_ERROR_LOG')) {
            return;
        }

        $settings = new Settings();

        $logger = match ($settings->getLoggerType()) {
            Settings::LOGGER_TYPE__FILE => new FileLogger(),
            default => new DbLogger()
        };

        $logger->log($level, $message, $context, $tag);

        if ($logger->isNewMessage()) {
            $notification = new Manager($settings);
            $notification->send($level, $message, $context, $tag);
        }
    }

    public static function critical(string|Stringable $message, array $context = [], ?string $tag = null): void
    {
        self::log(LogLevel::CRITICAL, $message, $context, $tag);
    }

    public static function error(string|Stringable $message, array $context = [], ?string $tag = null): void
    {
        self::log(LogLevel::ERROR, $message, $context, $tag);
    }

    public static function warning(string|Stringable $message, array $context = [], ?string $tag = null): void
    {
        self::log(LogLevel::WARNING, $message, $context, $tag);
    }
}
