<?php
/**
 * Author: Simone Monterubbiano <s.monterubbiano@drop.it>
 * Date: 27/03/2019
 * File name: Email.php
 * Project: renatocason/module-mq
 */

namespace Rcason\Mq\Model;

class Email{

    protected $storeManager;
    protected $transportBuilder;
    protected $inlineTranslation;
    protected $logger;
    /**
     * @var \Rcason\Mq\Helper\Data
     */
    private $helper;

    /**
     * Email constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Rcason\Mq\Helper\Data $helper
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Rcason\Mq\Helper\Data $helper
    ) {
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->helper = $helper;
    }

    public function send($to,$templateName,$templateVars){
        $templateOptions = array(
            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $this->storeManager->getStore()->getId()
        );

        $from = [
            'email' => $this->helper->getGeneralSenderEmail(),
            'name' => $this->helper->getGeneralSenderName()
        ];

        $this->inlineTranslation->suspend();

        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($templateName)
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($templateVars)
                ->setFrom($from)
                ->addTo($to)
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            return true;
        } catch (\Exception $e) {
            throw  new \Exception($e->getMessage());
        }
    }
}