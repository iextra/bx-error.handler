<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

global $APPLICATION;

$menu = [];
$RIGHT = $APPLICATION->getGroupRight('rdn.error');

if ($RIGHT >= 'R') {
    $menuItem = [
        'parent_menu' => 'global_menu_services',
        'items_id' => 'rdn_error_log',
        'icon' => 'advertising_menu_icon', // 'sender_ads_menu_icon'
        'page_icon' => 'advertising_menu_icon',
        'sort' => 50,
        'text' => Loc::getMessage('RDN_ERROR__MENU'),
        'title' => Loc::getMessage('RDN_ERROR__MENU'),
        'items' => [
            [
                'parent_menu' => 'rdn_error.log',
                'sort' => 100,
                'icon' => 'iblock_menu_icon_types',
                'text' => Loc::getMessage('RDN_ERROR__MENU_LOG'),
                'title' => Loc::getMessage('RDN_ERROR__MENU_LOG'),
                'url' => 'rdn_error_log.php',
            ],
            [
                'parent_menu' => 'rdn_error.doc',
                'sort' => 200,
                'icon' => 'b24connector_menu_icon',
                'text' => Loc::getMessage('RDN_ERROR__MENU_DOC'),
                'title' => Loc::getMessage('RDN_ERROR__MENU_DOC'),
                'url' => 'rdn_error_doc.php',
            ],
        ],
    ];

    if ($RIGHT == 'W') {
        $menuItem['items'][] = [
            'parent_menu' => 'rdn_error.settings',
            'sort' => 300,
            'icon' => 'sys_menu_icon',
            'text' => Loc::getMessage('RDN_ERROR__MENU_SETTINGS'),
            'title' => Loc::getMessage('RDN_ERROR__MENU_SETTINGS'),
            'url' => 'rdn_error_settings.php',
        ];
    }

    $menu[] = $menuItem;
}

return $menu ?: false;
