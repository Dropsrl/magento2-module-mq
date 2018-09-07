<?php

namespace Rcason\Mq\Model;

class Consumer implements \Rcason\Mq\Api\StartConsumerInterface
{
    
    /**
     * @var QueueConfig
     */
    private $queueConfig;    
    
    /**
     * @var MessageEncoderInterface
     */
    private $messageEncoder;   
    protected $messageEnvelopeFactory;
    protected $logger;

    /**
     */
    public function __construct(
            \Rcason\Mq\Model\Config\Config $queueConfig,
            \Rcason\Mq\Api\MessageEncoderInterface $messageEncoder,
            \Psr\Log\LoggerInterface $logger,
            \Rcason\Mq\Api\Data\MessageEnvelopeInterfaceFactory $messageEnvelopeFactory
    ) {
        $this->queueConfig = $queueConfig;
        $this->messageEnvelopeFactory = $messageEnvelopeFactory;
        $this->messageEncoder = $messageEncoder;    
        $this->logger = $logger;
    }
    
    /**
     * {@inheritdoc}
     */
    public function process($queueName, $queueMessage)
    {
        if($queueMessage) {
            $message = $this->messageEnvelopeFactory->create()
                ->setBrokerRef($queueMessage->getId())
                ->setContent($queueMessage->getMessageContent());

            $broker = $this->queueConfig->getQueueBrokerInstance($queueName);
            $consumer = $this->queueConfig->getQueueConsumerInstance($queueName);
            try {
                $result = $consumer->process(
                    $this->messageEncoder->decode($queueName, $message->getContent())
                );
                $broker->acknowledge($message, $result);
            } catch (Exception $ex) {
                $broker->reject($message, $ex->getMessage());
            }
        }
    }
    
}
