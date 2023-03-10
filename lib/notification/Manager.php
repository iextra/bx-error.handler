<?php

namespace RDN\Error\Notification;

use RDN\Error\Decorator\StringableArray;
use RDN\Error\Notification\Sender\Email;
use RDN\Error\Notification\Sender\Telegram;
use RDN\Error\Settings;
use RDN\Error\Notification\Sender\AdminSection;
use Stringable;

class Manager
{
    protected Settings $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function send(string $level, string|Stringable $message, array $context = [], ?string $tag = null): void
    {
        if (in_array($level, $this->settings->getLevelsNotSending())) {
            return;
        }

        if ($this->settings->isEnabled(Settings::ADMIN_NOTICE)) {
            $adminSender = new AdminSection();
            $adminSender->alert($this->settings->getAdminSectionAlertMessage());
        }

        if ($this->settings->isEnabled(Settings::TELEGRAM_NOTICE)) {
            $tgSender = new Telegram($this->settings);
            $tgSender->send($level, $message, $tag);
        }

        if ($this->settings->isEnabled(Settings::EMAIL_NOTICE)) {
            $mailSender = new Email($this->settings);
            $mailSender->send($level, $message, new StringableArray($context), $tag);
        }
    }
}
