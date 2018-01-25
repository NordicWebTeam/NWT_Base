<?php
/**
 * @category   NWT
 * @package    NWT_Base
 * @author     Nordic Web Team <info@nordicwebteam.se>
 * @copyright  Copyright (c) 2017 Nordic Web Team (http://www.nordicwebteam.se)
 * @license    NWT Commercial License (NWTCL 1.0)
 */

namespace NWT\Base\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use NWT\Base\Model\Tracker\Api;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /** @var WriterInterface $configWriter */
    protected $api;

    /**
     * UpgradeSchema constructor.
     * @param Api $api
     */
    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->api->createLog();
        }

        $installer->endSetup();
    }
}