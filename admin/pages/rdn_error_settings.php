<?php

global $USER;
global $APPLICATION;
global $DB;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Psr\Log\LogLevel;
use RDN\Error\Settings;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/prolog.php';
require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/prolog_admin_after.php';

if (! $USER->CanDoOperation('edit_php')) {
    $APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}

$moduleId = 'rdn.error';

if (! Loader::includeModule($moduleId)) {
    $APPLICATION->ThrowException("Module '{$moduleId}' not found.");
}

$settings = new Settings();
$request = Context::getCurrent()->getRequest();

if (
    $request->isPost()
    && check_bitrix_sessid()
) {
    $setters = [
        'ADMIN_NOTICE' => function ($value, Settings $settings) {
            $settings->setEnabled(Settings::ADMIN_NOTICE, (bool)$value);
        },
        'TELEGRAM_NOTICE' => function ($value, Settings $settings) {
            $settings->setEnabled(Settings::TELEGRAM_NOTICE, (bool)$value);
        },
        'EMAIL_NOTICE' => function ($value, Settings $settings) {
            $settings->setEnabled(Settings::EMAIL_NOTICE, (bool)$value);
        },
        'BACKTRACE_WITH_ARGS' => function ($value, Settings $settings) {
            $settings->setEnabled(Settings::BACKTRACE_WITH_ARGS, (bool)$value);
        },
        'NOT_UPDATE_RETRY_COUNT' => function ($value, Settings $settings) {
            $settings->setEnabled(Settings::NOT_UPDATE_RETRY_COUNT, (bool)$value);
        },
        'LEVELS_NOT_SENDING' => function ($values, Settings $settings) {
            $settings->setLevelsNotSending($values ?: []);
        },
        'TG_TOKEN' => [$settings, 'setTgToken'],
        'TG_CHAT_ID' => [$settings, 'setTgChatId'],
        'RECIPIENT_EMAIL' => [$settings, 'setRecipientEmail'],
    ];

    foreach ($setters as $key => $handler){
        call_user_func($handler, $request->getPost($key), $settings);
    }

    $settings->save();
}

$arTabs = [
    [
        'DIV' => 'tab_1',
        'TAB' => 'Настройки',
        'FIELDS' => [
            'ADMIN_NOTICE' => [
                'TYPE' => 'checkbox',
                'TITLE' => 'Показывать в админке',
                'VALUE' => $settings->isEnabled(Settings::ADMIN_NOTICE)
            ],
            'ADMIN_NOTICE_HR' => [
                'TYPE' => 'hr',
            ],

            'TELEGRAM_NOTICE' => [
                'TYPE' => 'checkbox',
                'TITLE' => 'Отправлять в телеграм',
                'VALUE' => $settings->isEnabled(Settings::TELEGRAM_NOTICE)
            ],
            'TG_TOKEN' => [
                'TYPE' => 'text',
                'TITLE' => '<b>Токен</b>:',
                'VALUE' => $settings->getTgToken()
            ],
            'TG_CHAT_ID' => [
                'TYPE' => 'text',
                'TITLE' => '<b>ID чата</b>:',
                'VALUE' => $settings->getTgChatId()
            ],
            'TELEGRAM_NOTICE_HR' => [
                'TYPE' => 'hr',
            ],

            'EMAIL_NOTICE' => [
                'TYPE' => 'checkbox',
                'TITLE' => 'Отправлять на EMAIL',
                'VALUE' => $settings->isEnabled(Settings::EMAIL_NOTICE)
            ],
            'RECIPIENT_EMAIL' => [
                'TYPE' => 'text',
                'TITLE' => '<b>Email получателей</b>:',
                'VALUE' => $settings->getRecipientEmail()
            ],

            'LEVELS_NOT_SENDING_HR' => [
                'TYPE' => 'hr',
            ],
            'LEVELS_NOT_SENDING' => [
                'TYPE' => 'select',
                'TITLE' => 'Не отправлять уведомления для уровней:',
                'MILTIPLE' => true,
                'VALUES' => [
                    LogLevel::CRITICAL => LogLevel::CRITICAL,
                    LogLevel::ERROR => LogLevel::ERROR,
                    LogLevel::WARNING => LogLevel::WARNING,
                ],
                'SELECTED_VALUES' => $settings->getLevelsNotSending()
            ],
        ]
    ],
    [
        'DIV' => 'tab_2',
        'TAB' => 'Доп. настройки',
        'FIELDS' => [
            'BACKTRACE_WITH_ARGS' => [
                'TYPE' => 'checkbox',
                'TITLE' => 'Сохранять аргументы в бектрейсе',
                'VALUE' => $settings->isEnabled(Settings::BACKTRACE_WITH_ARGS)
            ],
            'BACKTRACE_WITH_ARGS_HR' => [
                'TYPE' => 'hr',
            ],

            'NOT_UPDATE_RETRY_COUNT' => [
                'TYPE' => 'checkbox',
                'TITLE' => 'Не обновлять кол-во повторов',
                'VALUE' => $settings->isEnabled(Settings::NOT_UPDATE_RETRY_COUNT)
            ],
            'NOT_UPDATE_RETRY_COUNT_INFO' => [
                'TYPE' => 'info',
                'TITLE' => 'Экономит 2 запроса к БД на одной ошибке',
            ],
            'NOT_UPDATE_RETRY_COUNT_HR' => [
                'TYPE' => 'hr',
            ],
        ]
    ],
];

$tabControl = new CAdminTabControl("tabControl", $arTabs, true, true);

$APPLICATION->SetTitle('Уведомления об ошибках');
$APPLICATION->SetAdditionalCSS("/local/modules/{$moduleId}/admin/assets/css/style.css");
Asset::getInstance()->addJs("/local/modules/{$moduleId}/admin/assets/js/script.js");
CJSCore::Init(['jquery']);

require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/prolog_admin_after.php';
?>
    <form method="post" class="ex-container">
        <?php
        $tabControl->Begin();

        foreach ($arTabs as $arTab){
            $tabControl->BeginNextTab();

            $tabProperties = $arTab['FIELDS'];
            foreach ($tabProperties as $propId => $arProp) { ?>
                <tr class="<?= ($arProp['TYPE'] == 'hr') ? 'heading' : '' ?>">

                    <?php if ($arProp['TYPE'] == 'hr') { ?>
                        <td colspan="2">
                            <b><?=$arProp['TEXT']?></b>
                        </td>
                    <?php
                        continue;
                    } else { ?>
                        <td class="<?= ($arProp['TYPE'] == 'select') ? 'va-top' : '' ?>">
                            <?=$arProp['TITLE']?>
                        </td>
                    <?php } ?>

                    <td>
                        <div class="row mb-2">
                            <div class="col ta-c">
                                <?php if ($arProp['TYPE'] === 'text') { ?>

                                    <input type="text" name="<?= $propId ?>" value="<?= $arProp['VALUE'] ?>" size="50">

                                <?php } elseif ($arProp['TYPE'] === 'checkbox') {
                                    $switch = $arProp['VALUE'] ? 'switch-on' : '';
                                    ?>

                                    <input type="hidden" name="<?= $propId ?>" value="<?= (int)$arProp['VALUE'] ?>">
                                    <div class="switch-btn va-m <?= $switch ?>" data-input="<?= $propId ?>"></div>

                                <?php } elseif ($arProp['TYPE'] === 'textarea') { ?>

                                    <textarea name="<?= $propId ?>"
                                              cols="86"
                                              rows="8"
                                    ><?= $arProp['VALUE'] ?></textarea>

                                <?php } elseif ($arProp['TYPE'] === 'info') { ?>

                                    <div class="adm-info-message-wrap">
                                        <div class="adm-info-message">
                                            <?=htmlspecialchars_decode($arProp['TEXT'])?>
                                        </div>
                                    </div>

                                <?php } elseif ($arProp['TYPE'] === 'select') {

                                    $name = $propId . ($arProp['MILTIPLE'] ? '[]' : '');
                                    $multiple = $arProp['MILTIPLE'] ? 'multiple' : '';
                                    $size = $arProp['SIZE'] ?: 4;
                                    ?>

                                    <div class="adm-info-message-wrap">
                                        <select name="<?= $name ?>" <?= $multiple ?> size="<?= $size ?>">
                                            <?php foreach ($arProp['VALUES'] as $key => $value) {
                                                $selected = in_array($key, $arProp['SELECTED_VALUES']) ? 'selected' : '';
                                                ?>
                                                <option value="<?= $key ?>" <?=$selected?>><?= $value ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                <?php } ?>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php }
        }

        $tabControl->Buttons();
        $tabControl->ShowWarnings("post_form", '');
        ?>

        <input class="adm-btn-save" type="submit" name="save" value="Сохранить">
        <input type="button" onclick="location.reload()" value="Отменить" />

        <?php $tabControl->End(); ?>
        <?= bitrix_sessid_post(); ?>
    </form>
<?php
require_once $_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/include/epilog_admin.php';
