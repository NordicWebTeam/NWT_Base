<?php

/**
 * @category   NWT
 * @package    NWT_Base
 * @author     Nordic Web Team <info@nordicwebteam.se>
 * @copyright  Copyright (c) 2017 Nordic Web Team (http://www.nordicwebteam.se)
 * @license    NWT Commercial License (NWTCL 1.0)
 */

namespace NWT\Base\Model\Tracker;

use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Api
{
    const NWT_REST_ENDPOINT = 'https://tracker.nordicwebteam.se/api/v1/';
    const CONFIG_DATA_PROJECT_ID = 'nwt_base/tracker/setup_id';

    /** @var WriterInterface $configWriter */
    protected $configWriter;

    /** @var \Zend\Http\Client $client */
    protected $client;

    protected $collect;

    protected $_logger;

    /** @var \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory */
    protected $httpClientFactory;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig,
        Collect $collect
    ) {
        $this->httpClientFactory = $httpClientFactory;
        $this->client = $this->_getClient();
        $this->_logger = $logger;
        $this->collect = $collect;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
    }

    private function _getClient($controller = "project_logs")
    {
        if (!isset($this->client))
        {
            $client = $this->httpClientFactory->create();
            $client->setHeaders(['Content-Type: application/json']);
            $client->setMethod(\Zend_Http_Client::POST);
            $client->setUri(self::NWT_REST_ENDPOINT . $controller);

            $this->client = $client;
        }

        return $this->client;
    }

    /**
     * @return mixed
     */
    public function createLog()
    {
        $client = $this->_getClient();
        $setupId = $this->scopeConfig->getValue(self::CONFIG_DATA_PROJECT_ID);
        $client->setRawData(json_encode(
            ['project_log' => array_merge(
                $this->collect->getAllInfo(),
                ['setup_id' => $setupId]
            )]
        ));

        try {
            $request = $client->request();
            $requestBody = $request->getBody();

            if ($request->getStatus() === 201) {
                $body = json_decode($requestBody);
                if (empty($setupId)) {
                    $this->configWriter->save(self::CONFIG_DATA_PROJECT_ID, $body->id);
                }
                return $body->id;
            }
            else {
                $this->_logger->error("Tracker api create Log error. Request body: ". $requestBody);
            }
        }
        catch(\Exception $e)
        {
            $this->_logger->error("Exception while creating NWT Api tracker id. ". $e->getMessage());
        }
    }
}
