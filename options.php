<?php

/**
 * @global CUser $USER
 * @global CMail $APPLICATION
 */

if (
    ! defined('ADMIN_SECTION')
    || ! $USER->IsAdmin()
) {
    return;
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;

Loc::loadMessages(__FILE__);

$module_id = 'rdn.error';
$ADV_RIGHT = $APPLICATION->GetGroupRight($module_id);
$request = Context::getCurrent()->getRequest();

$arTabs = [
    [
        'DIV' => 'access',
        'TAB' => Loc::getMessage('RDN_ERROR__TAB_ACCESS'),
        'TITLE' => ''
    ],
];

$tabControl = new CAdminTabControl('tabControl', $arTabs);

if (
    $request->isPost()
    && $ADV_RIGHT >= 'W'
    && check_bitrix_sessid()
) {
    $Update = true;
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php';
    $APPLICATION->RestartBuffer();

    LocalRedirect($APPLICATION->GetCurPage() . '?mid=' . urlencode($module_id) . '&lang=' . urlencode(LANGUAGE_ID) . '&' . $tabControl->ActiveTabParam());
}

$tabControl->Begin();
$action = $APPLICATION->GetCurPage() . '?mid=' . $module_id . '&lang=' . LANGUAGE_ID;
?>
<form action="<?= $action ?>" method="POST">
    <?php
    $tabControl->BeginNextTab();

    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/admin/group_rights.php';

    $tabControl->End();
    $tabControl->Buttons([
        'btnSave' => true,
        'btnSCancel' => true,
        'btnApply' => false,
        'back_url' => ' '
    ]);
    ?>
</form>
