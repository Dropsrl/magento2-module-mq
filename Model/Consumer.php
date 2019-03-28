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
     * @var Email
     */
    private $email;
    /**
     * @var \Rcason\Mq\Helper\Data
     */
    private $helper;

    /**
     * Consumer constructor.
     * @param Config\Config $queueConfig
     * @param \Rcason\Mq\Api\MessageEncoderInterface $messageEncoder
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Rcason\Mq\Api\Data\MessageEnvelopeInterfaceFactory $messageEnvelopeFactory
     * @param Email $email
     * @param \Rcason\Mq\Helper\Data $helper
     */
    public function __construct(
            \Rcason\Mq\Model\Config\Config $queueConfig,
            \Rcason\Mq\Api\MessageEncoderInterface $messageEncoder,
            \Psr\Log\LoggerInterface $logger,
            \Rcason\Mq\Api\Data\MessageEnvelopeInterfaceFactory $messageEnvelopeFactory,
            \Rcason\Mq\Model\Email $email,
            \Rcason\Mq\Helper\Data $helper
    ) {
        $this->queueConfig = $queueConfig;
        $this->messageEnvelopeFactory = $messageEnvelopeFactory;
        $this->messageEncoder = $messageEncoder;    
        $this->logger = $logger;
        $this->email = $email;
        $this->helper = $helper;
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
            } catch (\Exception $ex) {
                // Send error log email
                $this->sendErrorEmail($ex->getMessage());

                // Set Error on job
                $broker->reject($message, $ex->getMessage());
            }
        }
    }

    private function sendErrorEmail($msg){
        if(!$this->helper->getLogEnabled()){
            return;
        }

        $tos = explode(";",$this->helper->getLogRecipientEmail());
        if(!count($tos)){
            return;
        }

        try{
            $this->email->send(
                $tos,
                "rcason_log_error_email",
                ['title' => "Jobqueue error", 'message' => $msg]);
        } catch (\Exception $e){
            throw  new \Exception($e->getMessage());
        }
    }
}
