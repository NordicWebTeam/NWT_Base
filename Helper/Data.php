<?php
/**
 *
 * @category    NWT
 * @package     NWT_Base
 * @copyright   Copyright (c) 2017 Nordic Web Team ( http://nordicwebteam.se/ )
 * @license     NWT Commercial License (NWTCL 1.0)
 *
 */

namespace NWT\Base\Helper;

/**
 * NWT module base helper
 *
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const XML_NWT_CONFIG_PATH = 'nwtbase/settings/';


    /**
     * @param $path
     * @param null $store
     * @return mixed
     */
    public function getStoreConfig($path, $store = null) {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }


    public function getStoreConfigFlag($path,$store = null) {
        return $this->scopeConfig->isSetFlag(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }    


    /**
     * Public retrieve url, magento sux...expose _getUrl
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route, $params = []) {
        return $this->_getUrl($route, $params);
    }


    //expose loggger methods, @see  Psr\Log\LoggerInterface

    // System is unusable.
    public function logEmergency($message, array $context = array()) { return $this->_logger->emergency($message,$context); }

    // Action must be taken immediately.* Example: Entire website down, database unavailable, etc. This should  trigger the SMS alerts and wake you up.
    public function logAlert($message, array $context = array()) { return $this->_logger->alert($message,$context); }

    //Critical conditions.Example: Application component unavailable, unexpected exception.
    public function logCritical($message, array $context = array()) { return $this->_logger->critical($message,$context); }

    //Runtime errors that do not require immediate action but should typically  be logged and monitored.
    public function logError($message, array $context = array()) { return $this->_logger->error($message,$context); }

    //Exceptional occurrences that are not errors.Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
    public function logWarning($message, array $context = array()) { return $this->_logger->warning($message,$context); }

    //Normal but significant events.
    public function logNotice($message, array $context = array()) { return $this->_logger->notice($message,$context); }

    // Interesting events, Example: User logs in, SQL logs.
    public function logInfo($message, array $context = array()) { return $this->_logger->info($message,$context); }

    //Detailed debug information.
    public function logDebug($message, array $context = array()) { return $this->_logger->debug($message,$context); }



}
