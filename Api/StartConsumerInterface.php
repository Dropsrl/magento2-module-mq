<?php

namespace Rcason\Mq\Api;

interface StartConsumerInterface
{
    /**
     * Remove message from queue
     * 
     * @param mixed $message The decoded message content
     */
    public function process($queueName, $message);
}
