<?php

namespace Rcason\Mq\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Rcason\Mq\Api\Config\ConfigInterface as QueueConfig;
use Rcason\Mq\Api\PublisherInterface;
use Rcason\Mq\Api\MessageEncoderInterface;
use Magento\Framework\App\State;

class StartConsumerCommand extends Command
{
    const COMMAND_CONSUMERS_START = 'ce_mq:consumers:start';
    const ARGUMENT_QUEUE_NAME = 'queue';
    const OPTION_POLL_INTERVAL = 'interval';
    const OPTION_MESSAGE_LIMIT = 'limit';

    /**
     * @var QueueConfig
     */
    private $queueConfig;

    /**
     * @var MessageEncoderInterface
     */
    private $messageEncoder;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;
    
    protected $consumer;

    /**
     * @param State $state
     * @param QueueConfig $queueConfig
     * @param MessageEncoderInterface $messageEncoder
     * @param string|null $name
     */
    public function __construct(
        State $state,
        QueueConfig $queueConfig,
        MessageEncoderInterface $messageEncoder,
        \Rcason\Mq\Model\Consumer $consumer
    ) {
        $this->state = $state;
        $this->queueConfig = $queueConfig;
        $this->messageEncoder = $messageEncoder;
        $this->consumer = $consumer;

        parent::__construct(null);
    }

    /**
     * Custom method to process queue
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processQueue(){
        try {
            // this tosses an error if the areacode is not set.
            $this->state->getAreaCode();
        } catch (\Exception $e) {
            $this->state->setAreaCode('adminhtml');
        }

        $queueNames = $this->queueConfig->getQueueNames();
        if(count($queueNames) == 0) {
            $output->writeln('No configured queue.');
            return;
        }

        foreach($queueNames as $queueName) {
            // Prepare consumer and broker
            $broker = $this->queueConfig->getQueueBrokerInstance($queueName);

            // Get next message in queue
            $messages = $broker->peek($queueName);

            if(count($messages)) {
                foreach($messages as $message) {
                    try {
                        $result = $this->consumer->process($queueName, $message);
                    } catch (Exception $ex) {
                        $broker->reject($message);
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // this tosses an error if the areacode is not set.
            $this->state->getAreaCode();
        } catch (\Exception $e) {
            $this->state->setAreaCode('adminhtml');
        }

        // Load and verify input arguments
        $queueNames = explode(',', $input->getArgument(self::ARGUMENT_QUEUE_NAME));
        $interval = $input->getOption(self::OPTION_POLL_INTERVAL);
        $limit = $input->getOption(self::OPTION_MESSAGE_LIMIT);

        if(empty($input->getArgument(self::ARGUMENT_QUEUE_NAME))) {
            $queueNames = $this->queueConfig->getQueueNames();
            if(count($queueNames) == 0) {
                $output->writeln('No configured queue.');
                return;
            }
        }

        foreach($queueNames as $queueName) {
            // Prepare consumer and broker
            $broker = $this->queueConfig->getQueueBrokerInstance($queueName);

            // Get next message in queue
            $messages = $broker->peek($queueName);

            if(count($messages)) {
                foreach($messages as $message) {
                    try {
                        $result = $this->consumer->process($queueName, $message);
                        $output->writeln($result);
                    } catch (Exception $ex) {
                        $broker->reject($message);
                        $output->writeln('Error processing message: ' . $ex->getMessage());
                    }
                }
            }
        }

    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName(self::COMMAND_CONSUMERS_START);
        $this->setDescription('Start queue consumer');

        $this->addArgument(
            self::ARGUMENT_QUEUE_NAME,
            null,
            'The queue name. Multiple queues separated by comma.'
        );
        $this->addOption(
            self::OPTION_POLL_INTERVAL,
            null,
            InputOption::VALUE_REQUIRED,
            'Polling interval in ms (default is 200).',
            200
        );
        $this->addOption(
            self::OPTION_MESSAGE_LIMIT,
            null,
            InputOption::VALUE_REQUIRED,
            'Maximum number of messages to process (default is 0, unlimited).',
            0
        );

        parent::configure();
    }
}
