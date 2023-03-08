<?php

namespace RDN\Error\Logger;

interface MessageStatusInterface
{
    /**
     * Must return whether the message is new
     *
     * @return bool
     */
    public function isNewMessage(): bool;
}
