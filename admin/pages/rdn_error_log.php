<?php

global $USER;
global $APPLICATION;
global $DB;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use RDN\Error\Admin\GridHelper;
use RDN\Error\Entities\Internals\LogTable;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/prolog.php';

if (!$USER->CanDoOperation('edit_php')) {
    $APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

try {
    $moduleId = 'rdn.error';
    Loader::includeModule($moduleId);
} catch (Exception $e) {}

$request = Context::getCurrent()->getRequest();
$gridHelper = new GridHelper('rdn_error_log');

if ($request->isAjaxRequest()) {
    $gridHelper->requestHandler($request);
}

$APPLICATION->SetTitle('Лог ошибок');
$APPLICATION->SetAdditionalCSS("/local/modules/{$moduleId}/admin/assets/css/style.css");
Asset::getInstance()->addJs("/local/modules/{$moduleId}/admin/assets/js/script.js");
CJSCore::Init(['jquery']);

require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/prolog_admin_after.php';

$filterFields = [
    [
        'id' => 'LEVEL',
        'name' => 'Уровень ошибки',
        'type' => 'list',
        'items' => $gridHelper->getLogLevelList(),
        'params' => ['multiple' => 'Y'],
    ],
    [
        'id' => 'TAG',
        'name' => 'Тег',
        'type' => 'list',
        'items' => $gridHelper->getTagList(),
        'params' => ['multiple' => 'Y'],
    ],
    [
        'id' => 'MESSAGE',
        'name' => 'Сообщение',
    ],
    [
        'id' => 'DATE_UPDATE',
        'name' => 'Дата обновление',
        'type' => 'date',
        'exclude' => $gridHelper->getExcludeDateTypes()
    ],
    [
        'id' => 'DATE_CREATE',
        'name' => 'Дата создания',
        'type' => 'date',
        'exclude' => $gridHelper->getExcludeDateTypes()
    ],
];

$APPLICATION->IncludeComponent(
    'bitrix:main.ui.filter',
    '',
    [
        'FILTER_ID' => $gridHelper->getGridId(),
        'GRID_ID' => $gridHelper->getGridId(),
        'FILTER' => $filterFields,
        'ENABLE_LIVE_SEARCH' => true,
        'ENABLE_LABEL' => true
    ]
);

$filter = $gridHelper->getFilter($filterFields);
$pageNavigation = $gridHelper->getPageNavigation();

$query = LogTable::query()
    ->setSelect(['ID', 'MESSAGE', 'DATE_UPDATE', 'LEVEL', 'RETRY_COUNT', 'TAG'])
    ->setFilter($filter)
    ->setLimit($pageNavigation->getLimit())
    ->setOffset($pageNavigation->getOffset())
    ->setOrder($gridHelper->getSort(['ID' => 'DESC']))
    ->countTotal(true)
    ->exec();

$totalCount = $query->getCount();
$pageNavigation->setRecordCount($totalCount);

$rows = [];
foreach ($query->fetchAll() as $row) {
    $row['LEVEL'] = GridHelper::wrapLogLevel($row['LEVEL']);

    $rows[] = [
        'data' => $row,
        'actions' => [
            [
                'text'    => 'Посмотреть',
                'onclick' => "RdnErrorLog.showDetail({$row['ID']});"
            ]
        ]
    ];
}

$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    [
        'GRID_ID' => $gridHelper->getGridId(),
        'COLUMNS' => [
            [
                'id' => 'ID',
                'name' => '',
                'sort' => 'ID',
                'width' => 50,
                'align' => 'center',
                'default' => true
            ],
            [
                'id' => 'LEVEL',
                'name' => 'Уровень',
                'sort' => false,
                'width' => 150,
                'align' => 'left',
                'default' => true
            ],
            [
                'id' => 'MESSAGE',
                'name' => 'Сообщение',
                'sort' => false,
                'width' => 500,
                'default' => true
            ],
            [
                'id' => 'DATE_UPDATE',
                'name' => 'Дата обновления',
                'sort' => 'DATE_UPDATE',
                'width' => 180,
                'default' => true
            ],
            [
                'id' => 'RETRY_COUNT',
                'name' => 'Повторы',
                'sort' => 'RETRY_COUNT',
                'width' => 100,
                'align' => 'center',
                'default' => true
            ],
            [
                'id' => 'TAG',
                'name' => 'Тег',
                'sort' => false,
                'width' => 150,
                'default' => true
            ],
        ],
        'ROWS' => $rows,
        'SHOW_ROW_CHECKBOXES' => true,
        'NAV_OBJECT' => $pageNavigation,
        'AJAX_MODE' => 'Y',
        'AJAX_ID' => CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
        'PAGE_SIZES' => [
            ['NAME' => '1', 'VALUE' => '1'],
            ['NAME' => '10', 'VALUE' => '10'],
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
        ],
        'AJAX_OPTION_JUMP'          => 'N',
        'SHOW_CHECK_ALL_CHECKBOXES' => true,
        'SHOW_ROW_ACTIONS_MENU'     => true,
        'SHOW_GRID_SETTINGS_MENU'   => true,
        'SHOW_NAVIGATION_PANEL'     => true,
        'SHOW_PAGINATION'           => true,
        'SHOW_SELECTED_COUNTER'     => false,
        'SHOW_TOTAL_COUNTER'        => true,
        'SHOW_PAGESIZE'             => true,
        'SHOW_ACTION_PANEL'         => true,
        'ACTION_PANEL'              => [
            'GROUPS' => [
                'TYPE' => [
                    'ITEMS' => [
                        $gridHelper->getSnippet()->getRemoveButton(),
                        $gridHelper->getSnippet()->getForAllCheckbox(),
                    ],
                ]
            ],
        ],
        'ALLOW_COLUMNS_SORT'        => true,
        'ALLOW_COLUMNS_RESIZE'      => true,
        'ALLOW_HORIZONTAL_SCROLL'   => true,
        'ALLOW_SORT'                => true,
        'ALLOW_PIN_HEADER'          => true,
        'AJAX_OPTION_HISTORY'       => 'N',
        'TOTAL_ROWS_COUNT'          => $totalCount
    ]
);

require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_admin.php';
