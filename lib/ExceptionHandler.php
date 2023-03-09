<?php

namespace RDN\Error;

use Bitrix\Main\Diag\ExceptionHandlerLog;
use Bitrix\Main\Loader;
use Psr\Log\LogLevel;
use RuntimeException;
use Throwable;

class ExceptionHandler extends ExceptionHandlerLog
{
    /**
     * @param Throwable $exception
     * @param $logType
     * @return void
     */
    public function write($exception, $logType): void
    {
        $moduleId = 'rdn.error';

        if (Loader::includeModule($moduleId)) {

            $context = false // TODO - Вынести в настройки
                ? $exception->getTrace()
                : $this->traceToArray($exception->getTraceAsString());

            $message = $this->getMessage($exception);

            match ($logType) {
                LogLevel::CRITICAL => Logger::critical($message, $context),
                LogLevel::WARNING => Logger::warning($message, $context),
                default => Logger::error($message, $context)
            };

        } else {
            throw new RuntimeException("Module '{$moduleId}' not found.");
        }
    }

    public function initialize(array $options)
    {

    }

    protected function getMessage(Throwable $exception): string
    {
        $message = $exception->getMessage();

        if (!str_contains($exception->getMessage(), 'called in')) {
            $message .= sprintf(
                ', called in %s on line %s',
                $exception->getFile(),
                $exception->getLine()
            );
        }

        return $message;
    }

    protected function traceToArray(string $trace): array
    {
        $data = array_filter(
            explode('#', $trace),
            function ($value) {
                return !empty($value);
            }
        );

        if (false) { // TODO - Вынести в настройки
            return $data;
        } else {
            return empty($_SERVER['DOCUMENT_ROOT'])
                ? $data
                : array_map(function ($value) {
                    return str_replace($_SERVER['DOCUMENT_ROOT'], '', $value);
                }, $data);
        }
    }
}
