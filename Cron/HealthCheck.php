<?php
/**
 * @category   NWT
 * @package    NWT_Base
 * @author     Nordic Web Team <info@nordicwebteam.se>
 * @copyright  Copyright (c) 2017 Nordic Web Team (http://www.nordicwebteam.se)
 * @license    NWT Commercial License (NWTCL 1.0)
 */

namespace NWT\Base\Cron;

use Magento\Framework\App\Config\ScopeConfigInterface;

class HealthCheck
{
    protected $collect;

    protected $api;

    protected $_logger;

    protected $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        \NWT\Base\Model\Tracker\Collect $collect,
        \Psr\Log\LoggerInterface $logger,
        \NWT\Base\Model\Tracker\Api $api
    ) {
        $this->_logger = $logger;
        $this->api = $api;
        $this->scopeConfig = $scopeConfig;
        $this->collect = $collect;
    }

    public function execute()
    {
        $this->api->createLog();
    }
}