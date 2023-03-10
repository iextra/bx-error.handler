<?php

namespace RDN\Error;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Data\Cache;

class Settings
{
    public const LOGGER_TYPE__FILE = 'FILE_LOGGER';
    public const LOGGER_TYPE__DB = 'DB_LOGGER';
    public const ADMIN_NOTICE = 'ADMIN_NOTICE';
    public const TELEGRAM_NOTICE = 'TELEGRAM_NOTICE';
    public const EMAIL_NOTICE = 'EMAIL_NOTICE';
    public const BACKTRACE_WITH_ARGS = 'BACKTRACE_WITH_ARGS';
    public const UPDATE_RETRY_COUNT = 'UPDATE_RETRY_COUNT';

    protected const MODULE_ID = 'rdn.error';
    protected const SETTINGS_KEY = 'settings';

    protected array $isEnabled = [
        self::UPDATE_RETRY_COUNT => 'Y'
    ];
    protected array $levelsNotSending = [];
    protected string $adminSectionAlertMessage;
    protected string $loggerType;
    protected ?string $recipientEmail;
    protected ?string $tgToken;
    protected ?string $tgChatId;

    public function __construct()
    {
        $data = $this->load();

        $this->isEnabled = $data['IS_ENABLED'] ?? [];
        $this->loggerType = $data['LOGGER_TYPE'] ?? self::LOGGER_TYPE__DB;
        $this->recipientEmail = $data['RECIPIENT_EMAIL'] ?? null;
        $this->levelsNotSending = $data['LEVELS_NOT_SENDING'] ?? [];
        $this->tgToken = $data['TG_TOKEN'] ?? null;
        $this->tgChatId = $data['TG_CHAT_ID'] ?? null;
        $this->adminSectionAlertMessage = $data['ADMIN_SECTION_ALERT_MESSAGE']
            ?: 'Обнаружены ошибки в работе сайта. <a href="/bitrix/admin/rdn_error_log.php">Посмотреть</a>';
    }

    public function getServerName(): ?string
    {
        if (isset($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }

        if (!empty($serverName = Option::get('main', 'server_name'))) {
            return $serverName;
        }

        return null;
    }

    public function getTgChatId(): ?string
    {
        return $this->tgChatId;
    }

    public function getTgToken(): ?string
    {
        return $this->tgToken;
    }

    public function getRecipientEmail(): ?string
    {
        return $this->recipientEmail;
    }

    public function getLoggerType(): string
    {
        return $this->loggerType;
    }

    public function getAdminSectionAlertMessage(): string
    {
        return html_entity_decode($this->adminSectionAlertMessage);
    }

    public function getLevelsNotSending(): array
    {
        return $this->levelsNotSending;
    }

    public function isEnabled(string $name): bool
    {
        if (array_key_exists($name, $this->isEnabled)) {
            return (bool)$this->isEnabled[$name];
        }

        return false;
    }

    public function setEnabled(string $name, bool $isEnabled): self
    {
        $this->isEnabled[$name] = $isEnabled;
        return $this;
    }

    public function setLevelsNotSending(array $levels): self
    {
        $this->levelsNotSending = $levels;
        return $this;
    }

    public function setAdminSectionAlertMessage(string $message): self
    {
        $this->adminSectionAlertMessage = $message;
        return $this;
    }

    public function setLoggerType(string $type): self
    {
        $this->loggerType = $type;
        return $this;
    }

    public function setRecipientEmail(string $email): self
    {
        $this->recipientEmail = $email;
        return $this;
    }

    public function setTgToken(string $token): self
    {
        $this->tgToken = $token;
        return $this;
    }

    public function setTgChatId(string $chatId): self
    {
        $this->tgChatId = $chatId;
        return $this;
    }

    public function save(): void
    {
        $data = [
            'IS_ENABLED' => $this->isEnabled,
            'LOGGER_TYPE' => $this->loggerType,
            'LEVELS_NOT_SENDING' => $this->levelsNotSending,
            'ADMIN_SECTION_ALERT_MESSAGE' => $this->adminSectionAlertMessage,
            'TG_TOKEN' => $this->tgToken,
            'TG_CHAT_ID' => $this->tgChatId,
            'RECIPIENT_EMAIL' => $this->recipientEmail,
        ];

        try {
            Option::set(self::MODULE_ID, self::SETTINGS_KEY, json_encode($data));

            $cache = Cache::createInstance();
            $cache->clean($this->getCacheId(), $this->getCacheDir());

        } catch (ArgumentOutOfRangeException $e) {}
    }

    protected function load(): array
    {
        $cache = Cache::createInstance();

        if ($cache->initCache(86400, $this->getCacheId(), $this->getCacheDir())) {
            $settings = $cache->getVars()['data'];
        } else {
            $settings = !empty($json = Option::get(self::MODULE_ID, self::SETTINGS_KEY))
                ? json_decode($json, true)
                : [];

            if ($cache->startDataCache()) {
                $cache->endDataCache(['data' => $settings]);
            }
        }

        return $settings;
    }

    protected function getCacheId(): string
    {
        return md5(static::class . 'cache');
    }

    protected function getCacheDir(): string
    {
        return 'settings';
    }
}