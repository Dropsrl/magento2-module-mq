<?php

namespace Rcason\Mq\Api;

use Rcason\Mq\Api\Data\MessageEnvelopeInterface;

interface BrokerInterface
{
    /**
     * Add message to queue
     * 
     * @return void
     */
    public function enqueue(MessageEnvelopeInterface $message);
    
    /**
     * Get next message in the queue
     * 
     * @return \Rcason\Mq\Api\Data\MessageEnvelopeInterface|null
     */
    public function peek($queueName);
    
    /**
     * Mark message as processed
     * 
     * @return void
     */
    public function acknowledge(MessageEnvelopeInterface $message, $result);
    
    /**
     * Reject message
     * 
     * @return void
     */
    public function reject(MessageEnvelopeInterface $message, $result);
}
