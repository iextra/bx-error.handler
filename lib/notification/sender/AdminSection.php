<?php

namespace RDN\Error\Notification\Sender;

class AdminSection
{
    public function alert(string $message): void
    {
        global $DB;
        global $CACHE_MANAGER;

        $table = 'b_admin_notify';
        $moduleId = 'rdn.error';
        $type = 'E';

        $arFields = [
            'MODULE_ID'	=> $moduleId,
            'TAG' => 'SITE_ERROR',
            'MESSAGE' => $message,
            'ENABLE_CLOSE' => 'Y',
            'PUBLIC_SECTION' => 'N',
            'NOTIFY_TYPE' => $type
        ];

        $sql = "SELECT `ID` FROM `{$table}` WHERE `MODULE_ID` = '{$moduleId}' AND `NOTIFY_TYPE` = '{$type}' LIMIT 1";
        if (empty($DB->Query($sql)->Fetch())) {
            $DB->Add($table, $arFields, ['MESSAGE']);
        }

        $CACHE_MANAGER->Clean('admin_notify_list_' . LANGUAGE_ID);
    }
}
