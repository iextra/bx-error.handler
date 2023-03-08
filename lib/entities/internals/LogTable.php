<?php

namespace RDN\Error\Entities\Internals;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\ORM\Event;
use Bitrix\Main\ORM\EventResult;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\Type\DateTime;
use RDN\Error\Entities;
use RDN\Error\Entities\LongtextField;

class LogTable extends DataManager
{
    public static function getTableName(): string
    {
        return 'rdn_error_log';
    }

    public static function getObjectClass(): string
    {
        return Entities\Log::class;
    }

    public static function getMap(): array
    {
        return [
            (new IntegerField('ID'))
                ->configurePrimary()
                ->configureAutocomplete(),

            (new StringField('LEVEL'))
                ->configureNullable(),

            (new TextField('MESSAGE'))
                ->configureRequired(),

            (new LongtextField('CONTEXT'))
                ->configureNullable(),

            (new StringField('TAG'))
                ->configureNullable(),

            (new StringField('CHECKSUM'))
                ->configureNullable(),

            (new IntegerField('RETRY_COUNT'))
                ->configureDefaultValue(0),

            (new DatetimeField('DATE_CREATE'))
                ->configureDefaultValue((new DateTime())),

            (new DatetimeField('DATE_UPDATE'))
                ->configureDefaultValue((new DateTime())),
        ];
    }

    public static function onBeforeUpdate(Event $event): EventResult
    {
        $result = new EventResult();

        $result->modifyFields([
            'DATE_UPDATE' => new DateTime()
        ]);

        return $result;
    }
}
