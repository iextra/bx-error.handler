<?php

namespace RDN\Error;

use Bitrix\Main\Diag\ExceptionHandlerLog;
use Bitrix\Main\Loader;
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
            // TODO
        } else {
            throw new RuntimeException("Module '{$moduleId}' not found.");
        }
    }

    public function initialize(array $options)
    {

    }
}
