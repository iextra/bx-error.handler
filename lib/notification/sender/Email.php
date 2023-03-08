<?php

namespace RDN\Error\Notification\Sender;

use CEvent;
use RDN\Error\Settings;

class Email
{
    protected Settings $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function send(string $level, string $message, ?string $context = '', ?string $tag = ''): void
    {
        CEvent::Send(
            'RDN_ERROR_NOTICE',
            SITE_ID,
            [
                'EMAIL' => $this->settings->getRecipientEmail(),
                'LEVEL' => $level,
                'MESSAGE' => $message,
                'CONTEXT' => (string)$context,
                'TAG' => $tag,
                'DATE' => date('d.m.Y H:i:s')
            ]
        );
    }
}
