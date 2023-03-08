<?php

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Mail\Internal\EventTypeTable;
use Bitrix\Main\Mail\Internal\EventMessageTable;
use RDN\Error\Entities\Internals\LogTable;

Loc::loadMessages(__FILE__);

class rdn_error extends \CModule
{
    public const MODULE_ID = 'rdn.error';

    public $MODULE_ID;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION ;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public function __construct()
    {
        $this->MODULE_ID = self::MODULE_ID;

        $this->MODULE_VERSION = $this->getVersion();
        $this->MODULE_VERSION_DATE = $this->getVersionDate(date('Y-m-d H:i:s'));

        $this->MODULE_NAME = '[RDN] Error';
        $this->MODULE_DESCRIPTION = Loc::getMessage('RDN_ERROR__DESCRIPTION');

        $this->PARTNER_NAME = 'RDN';
        $this->PARTNER_URI = '';
    }

    public function DoInstall(): void
    {
        try {
            $this->installDB();
            $this->InstallEvents();
            $this->InstallFiles();

            ModuleManager::registerModule($this->MODULE_ID);
        } catch (\Exception $e) {
            $this->errorHandler($e->getMessage());
        }
    }

    public function DoUninstall(): void
    {
        try {
            $this->UnInstallDB();
            $this->UnInstallEvents();
            $this->UnInstallFiles();

            ModuleManager::unRegisterModule($this->MODULE_ID);
        } catch (\Exception $e) {
            $this->errorHandler($e->getMessage());
        }
    }


    /**
     * @throws SystemException
     * @throws ArgumentException
     */
    public function installDB(): bool
    {
        $this->upLogTable();
        $this->addEmailTemplate();

        return true;
    }

    /**
     * @throws LoaderException
     * @throws ArgumentException
     * @throws SqlQueryException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function UnInstallDB(): bool
    {
        $this->downLogTable();
        $this->deleteEmailTemplate();

        return true;
    }

    public function getEvents(): array
    {
        return [
//            [
//                'MODULE' => 'main',
//                'EVENT' => 'OnBuildGlobalMenu',
//                'CLASS' => '\RDN\Error\Admin\Menu',
//                'METHOD' => 'buildMenu'
//            ],
        ];
    }

    public function InstallEvents(): bool
    {
        $eventManager = EventManager::getInstance();

        if (!empty($arEvents = $this->getEvents())) {
            foreach ($arEvents as $arEvent) {
                $eventManager->registerEventHandler(
                    $arEvent['MODULE'],
                    $arEvent['EVENT'],
                    $this->MODULE_ID,
                    $arEvent['CLASS'],
                    $arEvent['METHOD']
                );
            }
        }

        return true;
    }

    public function UnInstallEvents(): bool
    {
        $eventManager = EventManager::getInstance();

        if (!empty($arEvents = $this->getEvents())) {
            foreach ($arEvents as $arEvent) {
                $eventManager->unregisterEventHandler(
                    $arEvent['MODULE'],
                    $arEvent['EVENT'],
                    $this->MODULE_ID,
                    $arEvent['CLASS'],
                    $arEvent['METHOD']
                );
            }
        }

        return true;
    }

    public function InstallFiles()
    {
        $this->createAdminFiles();
    }

    public function UnInstallFiles()
    {
        $this->removeAdminFiles();
    }

    ########[ HELPERS ]######################################################################################

    private function errorHandler(string $message): void
    {
        global $APPLICATION;
        $APPLICATION->ThrowException($message);
    }

    private function getVersion(): ?string
    {
        $arModuleVersion = $this->getModuleVersion();
        return ($arModuleVersion['VERSION']) ?: '1.0.0';
    }

    private function getVersionDate($default = ''): ?string
    {
        $arModuleVersion = $this->getModuleVersion();
        return ($arModuleVersion['VERSION_DATE']) ?: $default;
    }

    private function getModuleVersion(): array
    {
        static $arModuleVersion = [];
        require_once dirname(__FILE__) . '/version.php';
        return $arModuleVersion;
    }

    private function createAdminFiles(): void
    {
        if (!empty($files = $this->getAdminFiles())) {
            foreach ($files as $fileName => $fileFullPath) {
                $fileRelPath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $fileFullPath);
                $content = '<?php require $_SERVER["DOCUMENT_ROOT"] . "' . $fileRelPath . '";';

                file_put_contents(
                    $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $fileName,
                    str_replace('"', '\'', $content)
                );
            }
        }
    }

    private function removeAdminFiles(): void
    {
        if (!empty($files = $this->getAdminFiles())) {
            foreach ($files as $fileName => $fileFullPath) {
                if (is_file($file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . $fileName)) {
                    unlink($file);
                }
            }
        }
    }

    private function getAdminFiles(): array
    {
        $arFiles = [];

        $dirPath = dirname(__FILE__, 2) . '/admin/pages';
        $dirSource = opendir($dirPath);

        while ($fileName = readdir($dirSource)) {
            $filePath = $dirPath . '/' . $fileName;
            if (is_file($filePath)) {
                $arFiles[$fileName] = $filePath;
            }
        }

        return $arFiles;
    }

    private function runSQLBatch(string $name): void
    {
        global $DB, $DBType;

        $fileName = str_replace('.sql', '', $name);
        $filePath = dirname(__FILE__) . "/db/{$DBType}/{$fileName}.sql";

        if (is_file($filePath)) {
            $DB->RunSQLBatch($filePath);
        }
    }

    /**
     * @throws ArgumentException
     * @throws SqlQueryException
     * @throws SystemException
     */
    private function upLogTable(): void
    {
        $moduleDir = realpath(__DIR__ . '/..');

        @include $moduleDir . '/lib/Entities/LongtextField.php';
        @include $moduleDir . '/lib/Entities/Internals/LogTable.php';

        $connection = Application::getConnection();
        $tableName = LogTable::getTableName();

        if (!$connection->isTableExists($tableName)) {
            Base::getInstance(LogTable::class)->createDbTable();

            $connection->queryExecute("ALTER TABLE {$tableName} MODIFY COLUMN `CONTEXT` LONGTEXT COLLATE 'utf8mb3_general_ci' NULL;");
            $connection->queryExecute("ALTER TABLE {$tableName} CONVERT TO CHARACTER SET utf8, COLLATE utf8mb3_general_ci;");
        }
    }

    /**
     * @throws SqlQueryException
     * @throws LoaderException
     */
    private function downLogTable(): void
    {
        Loader::includeModule(self::MODULE_ID);

        $connection = Application::getConnection();
        $tableName = LogTable::getTableName();

        if ($connection->isTableExists($tableName)) {
            $connection->dropTable($tableName);
        }
    }

    /**
     * @throws SystemException
     * @throws ArgumentException
     */
    private function addEmailTemplate(): void
    {
        $eventType = EventTypeTable::createObject();
        $savingResult = $eventType
            ->setName('Ошибка на сайте')
            ->setEventName('RDN_ERROR_NOTICE')
            ->setDescription("#MESSAGE# - Сообщение\n#CONTEXT# - Контекст\n#LEVEL# - Уровень ошибки\n#RETRY_COUNT# - Кол-во повторов\n#TAG# - Тег\n#DATE# - Дата")
            ->setLid('ru')
            ->setSort(100)
            ->setEventType(EventTypeTable::TYPE_EMAIL)
            ->save();


        if ($savingResult->isSuccess()) {
            $enetMessage = EventMessageTable::createObject();
            $enetMessage
                ->setEventName('RDN_ERROR_NOTICE')
                ->setLid('s1')
                ->setActive('Y')
                ->setEmailFrom('#DEFAULT_EMAIL_FROM#')
                ->setEmailTo('#EMAIL#')
                ->setSubject('#SITE_NAME#: Ошибки на сайте.')
                ->setMessage("Уровень ошибки: #LEVEL#\nСообщение: #MESSAGE#\n\nТег: #TAG#\nДата: #DATE#")
                ->setMessagePhp('<?=$arParams["MESSAGE"];?>')
                ->setBodyType('html')
                ->save();
        }
    }

    /**
     * @throws ObjectPropertyException
     * @throws ArgumentException
     * @throws SystemException
     */
    private function deleteEmailTemplate(): void
    {
        EventTypeTable::query()
            ->setSelect(['ID'])
            ->where('EVENT_NAME', 'RDN_ERROR_NOTICE')
            ->setLimit(1)
            ->exec()
            ->fetchObject()
            ?->delete();


        EventMessageTable::query()
            ->setSelect(['ID'])
            ->where('EVENT_NAME', 'RDN_ERROR_NOTICE')
            ->setLimit(1)
            ->exec()
            ->fetchObject()
            ?->delete();
    }
}
