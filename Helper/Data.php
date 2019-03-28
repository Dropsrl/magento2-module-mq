<?php
/**
 * Author: Simone Monterubbiano <s.monterubbiano@drop.it>
 * Date: 27/03/2019
 * File name: Data.php
 * Project: manuelritz
 */
namespace Rcason\Mq\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper{
    // Sender Email
    const GENERAL_SENDER_EMAIL = 'trans_email/ident_support/email';
    const GENERAL_SENDER_NAME = 'trans_email/ident_support/name';
    const EMAIL_LOG_ENABLED = 'jobqueue/general/log_enabled';
    const EMAIL_LOG_RECIPIENT = 'jobqueue/general/log_email';
    const MAX_RETRIES = 'jobqueue/general/max_retries';

    /**
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field, ScopeInterface::SCOPE_STORE, $storeId
        );
    }

    // Email log
    public function getLogEnabled(){
        return $this->getConfigValue(self::EMAIL_LOG_ENABLED);
    }
    public function getLogRecipientEmail(){
        return $this->getConfigValue(self::EMAIL_LOG_RECIPIENT);
    }

    // Sender log email
    public function getGeneralSenderEmail(){
        return $this->getConfigValue(self::GENERAL_SENDER_EMAIL);
    }
    public function getGeneralSenderName(){
        return $this->getConfigValue(self::GENERAL_SENDER_NAME);
    }

    // Max retries
    public function getMaxRetries(){
        return $this->getConfigValue(self::MAX_RETRIES);
    }
}