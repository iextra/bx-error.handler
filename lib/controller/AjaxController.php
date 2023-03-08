<?php

namespace RDN\Error\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Engine\ActionFilter;
use RDN\Error\Entities\Internals\LogTable;
use Exception;

class AjaxController extends Controller
{
    public function configureActions(): array
    {
        return [
            'getDetailData' => [
                'prefilters' => [
                    new ActionFilter\HttpMethod([
                        ActionFilter\HttpMethod::METHOD_POST
                    ]),
                    new ActionFilter\Authentication(),
                    new ActionFilter\Csrf(),
                ],
            ],
        ];
    }

    public function getDetailDataAction($id): ?array
    {
        try {
            $item = LogTable::query()
                ->setSelect(['*'])
                ->where('ID', $id)
                ->setLimit(1)
                ->exec()
                ->fetch();

            if (!empty($item['DATE_CREATE'])) {
                $item['DATE_CREATE'] = FormatDateFromDB($item['DATE_CREATE'], 'DD.MM.YYYY HH:MI:SS');
            }

            if (!empty($item['DATE_UPDATE'])) {
                $item['DATE_UPDATE'] = FormatDateFromDB($item['DATE_UPDATE'], 'DD.MM.YYYY HH:MI:SS');
            }

            return ['html' => $this->renderDetailDataTable($item)];
        }
        catch (Exception $e) {
            $this->addError(new Error($e->getMessage()));
        }

        return null;
    }

    protected function renderDetailDataTable(array $row): string
    {
        ob_start();
        ?>
            <table class="error-detail-table border-1" border="1">
                <tr>
                    <td>ID</td>
                    <td><?= $row['ID'] ?></td>
                </tr>
                <tr>
                    <td>Уровень</td>
                    <td><?= $row['LEVEL'] ?></td>
                </tr>
                <tr>
                    <td>Собщение</td>
                    <td><?= $row['MESSAGE'] ?></td>
                </tr>
                <tr>
                    <td>Тег</td>
                    <td><?= $row['TAG'] ?></td>
                </tr>
                <tr>
                    <td>Дата создания</td>
                    <td><?= $row['DATE_CREATE'] ?></td>
                </tr>
                <tr>
                    <td>Дата обновления</td>
                    <td><?= $row['DATE_UPDATE'] ?></td>
                </tr>
                <tr>
                    <td>Кол-во повторов</td>
                    <td><?= $row['RETRY_COUNT'] ?></td>
                </tr>
                <tr>
                    <td>Контекст</td>
                    <td>
                        <div class="error-dump">
                            <a href="#">Показать</a><br>
                            <pre><br><?= $row['CONTEXT'] ?></pre>
                        </div>
                    </td>
                </tr>
            </table>
        <?php
        return ob_get_contents();
    }
}
