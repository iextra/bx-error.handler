<?php

namespace RDN\Error\Logger;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use RDN\Error\Decorator\StringableArray;
use RDN\Error\Entities\Internals\LogTable;
use RDN\Error\Entities\Log;
use RuntimeException;
use Stringable;

class DbLogger extends BaseLogger
{
    private bool $isExistMessage = false;

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public function log($level, string|Stringable $message, array $context = [], ?string $tag = null): void
    {
        $context = new StringableArray($context);

        $checksum = self::calculateChecksum([
            'MESSAGE' => $message,
            'LEVEL' => $level,
            'CONTEXT' => (string)$context
        ]);

        $row = $this->getRowByChecksum($checksum);
        $this->isExistMessage = isset($row);

        if ($row) {
            $savingResult = $row
                ->setRetryCount(($row->getRetryCount() + 1))
                ->save();
        } else {
            $savingResult = LogTable::createObject()
                ->setMessage($message)
                ->setLevel($level)
                ->setContext($context)
                ->setChecksum($checksum)
                ->setTag($tag)
                ->save();
        }

        if (!$savingResult->isSuccess()) {
            throw new RuntimeException(implode(', ', $savingResult->getErrorMessages()));
        }
    }

    public function isNewMessage(): bool
    {
        return !$this->isExistMessage;
    }

    /**
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getRowByChecksum(string $checksum): ?Log
    {
        static $cache = [];

        if (!empty($row = $cache[$checksum])) {
            return $row;
        }

        return $cache[$checksum] = LogTable::query()
            ->setSelect(['ID', 'RETRY_COUNT'])
            ->where('CHECKSUM', $checksum)
            ->setLimit(1)
            ->exec()
            ->fetchObject();
    }

    protected static function calculateChecksum(array $fields): string
    {
        return md5(implode(':', $fields));
    }
}
