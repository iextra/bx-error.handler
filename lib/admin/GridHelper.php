<?php

namespace RDN\Error\Admin;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Grid\Options;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Request;
use Bitrix\Main\SystemException;
use Bitrix\Main\UI\Filter\DateType;
use Bitrix\Main\UI\PageNavigation;
use Bitrix\Main\UI\Filter;
use RDN\Error\Entities\Internals\LogTable;
use Psr\Log\LogLevel;

class GridHelper
{
    protected string $gridId;
    protected Options $options;
    protected PageNavigation $pageNavigation;
    protected Snippet $snippet;

    public function __construct(string $gridId)
    {
        $this->gridId = trim($gridId);

        $this->options = new Options($this->gridId);
        $this->pageNavigation = new PageNavigation($this->gridId);
        $this->snippet = new Snippet();

        $this->pageNavigation->allowAllRecords(false)
            ->setPageSize($this->options->GetNavParams()['nPageSize'])
            ->initFromUri();
    }

    public function getGridId(): string
    {
        return $this->gridId;
    }

    public function getSort(array $default = []): array
    {
        return $this->options->getSorting(['sort' => $default])['sort'];
    }

    public function getPageNavigation(): PageNavigation
    {
        return $this->pageNavigation;
    }

    public function getExcludeDateTypes(): array
    {
        return [
            DateType::YESTERDAY,
            DateType::CURRENT_DAY,
            DateType::CURRENT_WEEK,
            DateType::CURRENT_MONTH,
            DateType::CURRENT_QUARTER,
            DateType::TOMORROW,
            DateType::PREV_DAYS,
            DateType::NEXT_DAYS,
            DateType::MONTH,
            DateType::QUARTER,
            DateType::YEAR,
            DateType::LAST_WEEK,
            DateType::LAST_MONTH,
            DateType::NEXT_WEEK,
            DateType::NEXT_MONTH,
            DateType::LAST_7_DAYS,
            DateType::LAST_30_DAYS,
            DateType::LAST_60_DAYS,
            DateType::LAST_90_DAYS
        ];
    }

    public function getFilter(array $filterFields): array
    {
        $filterOption = new Filter\Options($this->gridId);
        $filterData = $filterOption->getFilter();

        $filter = [];
        $filterFieldCodes = array_column($filterFields, 'id');
        foreach ($filterData as $code => $value) {
            if (in_array($code, $filterFieldCodes)) {
                $filter[$code] = $value;
            }
        }

        return $filter;
    }

    public function getSnippet(): Snippet
    {
        return $this->snippet;
    }

    /**
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws ArgumentException
     */
    public function getTagList(): array
    {
        $rows = LogTable::query()
            ->setSelect(['TAG'])
            ->setDistinct()
            ->setCacheTtl(60)
            ->exec()
            ->fetchAll();

        $result = [];

        foreach ($rows as $row) {
            if (!empty($tag = $row['TAG'])) {
                $result[$tag] = $tag;
            }
        }

        return $result;
    }

    public function getLogLevelList(): array
    {
        return [
            LogLevel::CRITICAL => LogLevel::CRITICAL,
            LogLevel::ERROR => LogLevel::ERROR,
            LogLevel::WARNING => LogLevel::WARNING,
            //LogLevel::EMERGENCY => LogLevel::EMERGENCY,
            //LogLevel::NOTICE => LogLevel::NOTICE,
            //LogLevel::ALERT => LogLevel::ALERT,
            //LogLevel::INFO => LogLevel::INFO,
            //LogLevel::DEBUG => LogLevel::DEBUG,
        ];
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function requestHandler(Request $request): void
    {
        if ($request->getPost('action_button_' . $this->getGridId()) == 'delete') {
            if ($request->getPost('action_all_rows_' . $this->getGridId()) == 'Y') {
                $filter = [];
            } else {
                $ids = array_map(function ($id) {
                    return (int)$id;
                }, $request->getPost('ID'));
                $filter['ID'] = $ids;
            }

            $collection = LogTable::query()
                ->setSelect(['ID'])
                ->setFilter($filter)
                ->exec()
                ->fetchCollection();

            foreach ($collection as $item) {
                $item->delete();
            }
        }
    }

    public static function wrapLogLevel(string $message): string
    {
        return match ($message) {
            LogLevel::CRITICAL => '<span class="error-level-critical">' . $message . '</span>',
            LogLevel::ERROR => '<span class="error-level-error">' . $message . '</span>',
            LogLevel::WARNING => '<span class="error-level-warning">' . $message . '</span>',
            LogLevel::ALERT => '<span class="error-level-alert">' . $message . '</span>',
            LogLevel::NOTICE => '<span class="error-level-notice">' . $message . '</span>',
            LogLevel::INFO => '<span class="error-level-info">' . $message . '</span>',
            LogLevel::DEBUG => '<span class="error-level-debug">' . $message . '</span>',
            default => $message
        };
    }
}
