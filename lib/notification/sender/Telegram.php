<?php

namespace RDN\Error\Notification\Sender;

use RDN\Error\Settings;

class Telegram
{
    protected Settings $settings;

    public function __construct(Settings $settings)
    {
        $this->settings = $settings;
    }

    public function send(string $level, string $message, ?string $tag = ''): void
    {
        if (
            ! empty($token = $this->settings->getTgToken())
            && ! empty($chatId = $this->settings->getTgChatId())
        ) {
            $getQuery = [
                'chat_id' => $chatId,
                'text' => $this->createMessage($level, $message, $tag),
                'parse_mode' => 'html'
            ];

            if (! empty($url = $this->getViewPageLink())) {
                $getQuery['reply_markup'] = json_encode([
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'Посмотреть',
                                'url' => $url
                            ],
                        ]
                    ],
                    'one_time_keyboard' => true,
                    'resize_keyboard' => true,
                ]);
            }

            $curl = curl_init("https://api.telegram.org/bot{$token}/sendMessage?" . http_build_query($getQuery));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, false);

            curl_exec($curl);
            curl_close($curl);
        }
    }

    protected function createMessage(string $level, string $message, ?string $tag = ''): string
    {
        $result = "<b>Уровень</b>: <code>{$level}</code>\n";
        $result .= $tag ? "<b>Тег</b>: <code>{$tag}</code>\n\n" : "\n";
        $result .= "<b>Сообщение</b>:\n<code>{$message}</code>\n";

        return $result;
    }

    protected function getViewPageLink(): ?string
    {
        return !empty($serverName = $this->settings->getServerName())
            ? 'https://' . $serverName . '/bitrix/admin/rdn_error_log.php'
            : null;
    }
}
