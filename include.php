<?php

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Application;
use Psr\Log\LogLevel;

try {
    Loader::registerAutoLoadClasses('rdn.error', [
        'RDN\Error\Log' => 'lib/Log.php',
        'RDN\Error\Settings' => 'lib/Settings.php',
        'RDN\Error\ExceptionHandler' => 'lib/ExceptionHandler.php',
        'RDN\Error\Entities\Log' => 'lib/entities/Log.php',
        'RDN\Error\Controller\AjaxController' => 'lib/controller/AjaxController.php',
        'RDN\Error\Notification\AdminSection' => 'lib/notification/AdminSection.php',
        'RDN\Error\Notification\Email' => 'lib/notification/Email.php',
        'RDN\Error\Notification\Telegram' => 'lib/notification/Telegram.php',
        'RDN\Error\Entities\Internals\LogTable' => 'lib/entities/internals/LogTable.php',
        'RDN\Error\Entities\LongtextField' => 'lib/entities/LongtextField.php',
        'Bitrix\Main\Entity\LongtextField' => 'lib/Entities/LongtextField.php',
    ]);

} catch (LoaderException $e) {
   Application::getInstance()
        ->createExceptionHandlerLog()
        ->write($e, LogLevel::ERROR);
}
