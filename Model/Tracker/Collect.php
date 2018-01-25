<?php
/**
 * @category   NWT
 * @package    NWT_Base
 * @author     Nordic Web Team <info@nordicwebteam.se>
 * @copyright  Copyright (c) 2017 Nordic Web Team (http://www.nordicwebteam.se)
 * @license    NWT Commercial License (NWTCL 1.0)
 */

namespace NWT\Base\Model\Tracker;

use Magento\Framework\Component\ComponentRegistrar;

class Collect
{
    const STORE_EMAIL = 'trans_email/ident_general/email';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $_metadata;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \NWT\Base\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var array
     */
    protected $_additionalStoreInfoKeys = [
        "url" => \Magento\Store\Model\Store::XML_PATH_UNSECURE_BASE_URL,
        "email" => self::STORE_EMAIL,
        "locale" => \Magento\Config\Model\Config\Backend\Admin\Custom::XML_PATH_GENERAL_LOCALE_CODE
    ];

    protected $moduleManager;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    protected $storeManager;

    protected $storeInfo;

    /**
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \NWT\Base\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadataInterface,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\Manager $moduleManager,
        \NWT\Base\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\App\Language\Dictionary $dictionary,
        \Magento\Framework\App\Language\ConfigFactory $configFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Store\Model\Information $storeInfo
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->_metadata = $productMetadataInterface;
        $this->_moduleList = $moduleList;
        $this->_helper = $helper;
        $this->_date = $dateTime;
        $this->moduleManager = $moduleManager;
        $this->dictionary = $dictionary;
        $this->configFactory = $configFactory;
        $this->storeManager = $storeManager;
        $this->storeInfo = $storeInfo;
    }

    /**
     * @return string
     */
    public function getAllInfo()
    {
        return array_merge(
            $this->getStoreInfo(),
            ['urls' => $this->_getAllStoresBaseUrls()],
            ['modules' => $this->getNWTModulesInfo()],
            ['languages' => $this->getNWTLanguages()],
            ['host' => gethostbyname(gethostname())]
        );
    }

    /**
     * @return array
     */
    public function getStoreInfo() 
    {
        $storeInfo = [];
        $storeInfo['store'] = $this->storeInfo->getStoreInformationObject($this->storeManager->getStore())->getData();
        foreach ($this->_additionalStoreInfoKeys as $key => $value) {
            $storeInfo['store'][$key] = $this->_helper->getStoreConfig($value);
        }
        $storeInfo['magento_edition'] = $this->_metadata->getEdition();
        $storeInfo['magento_version'] = $this->_metadata->getVersion();
        $storeInfo['description'] = $this->_helper->getStoreConfig('design/head/default_description');

        return $storeInfo;
    }

    /**
     * Get module composer version
     *
     * @param $moduleName
     * @return \Magento\Framework\Phrase|string|void
     */
    public function getModuleVersion($moduleName)
    {
        $path = $this->_getModuleComponentRegistrarPath($moduleName);
        $directoryRead = $this->readFactory->create($path);

        if ($directoryRead->isExist('composer.json')) {
            $composerJsonData = $directoryRead->readFile('composer.json');
            $data = json_decode($composerJsonData);

            return !empty($data->version) ? $data->version : 'undefined';
        }

        return 'undefined';
    }

    /**
     * Get list of all non core Magento modules
     *
     * @return array
     */
    public function getNWTModulesInfo()
    {
        $allModules = $this->_moduleList->getAll();
        $list = [];
        foreach ($allModules as $module) {
            if (preg_match('#^NWT#', $module['name']) === 1) {
                $item = [
                    'name' => $module['name'],
                    'enabled' => (bool)$this->moduleManager->isEnabled($module['name']),
                    'is_output_enabled' => (bool)$this->moduleManager->isOutputEnabled($module['name']),
                    'setup_version' => $module['setup_version'],
                    'composer_version' => $this->getModuleVersion($module['name']),
                ];

                if ($module['name'] === 'NWT_KCO') {
                    $item['test_mode'] = (bool)$this->_helper->getStoreConfig('nwtkco/settings/test_mode');
                    $item['eid'] = $this->_helper->getStoreConfig('nwtkco/settings/eid');
                }
                else if ($module['name'] === 'NWT_Unifaun') {
                    $item['test_mode'] = (bool)$this->_helper->getStoreConfig('nwt_unifaun/connection/test_mode');
                    $item['eid'] = $this->_helper->getStoreConfig('nwt_unifaun/tracking/username');
                }
                else if ($module['name'] === 'NWT_Specter') {
                    $item['test_mode'] = (bool)$this->_helper->getStoreConfig('nwt_specter/general/enable');
                    $item['eid'] = $this->_helper->getStoreConfig('nwt_specter/general/specter_smbid');
                }

                $list[] = $item;
            }
        }

        return $list;
    }

    /**
     * @param $moduleName
     * @return null|string
     */
    private function _getModuleComponentRegistrarPath($moduleName)
    {
        static $path;
        if (!isset($path[$moduleName])) {
            $path[$moduleName] = $this->componentRegistrar->getPath(
                \Magento\Framework\Component\ComponentRegistrar::MODULE,
                $moduleName
            );
        }

        return $path[$moduleName];
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNWTLanguages()
    {
        $languages = [];
        $this->paths = $this->componentRegistrar->getPaths(ComponentRegistrar::LANGUAGE);
        foreach ($this->paths as $path) {
            $directoryRead = $this->readFactory->create($path);
            if ($directoryRead->isExist('language.xml')) {
                $xmlSource = $directoryRead->readFile('language.xml');
                try {
                    $languageConfig = $this->configFactory->create(['source' => $xmlSource]);

                    if ($languageConfig->getVendor() == "nwt" && $directoryRead->isExist('composer.json')) {
                        $composerJsonData = $directoryRead->readFile('composer.json');
                        $data = json_decode($composerJsonData);
                        $languages[] = [
                            'name' => $languageConfig->getCode(),
                            'composer_version' => !empty($data->version) ? $data->version : 'undefined'
                        ];
                    }
                }
                catch (\Magento\Framework\Config\Dom\ValidationException $e) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        new \Magento\Framework\Phrase(
                            "Invalid XML in file %1:\n%2",
                            [$path . '/language.xml', $e->getMessage()]
                        ),
                        $e
                    );
                }
            }
        }

        return $languages;
    }

    /**
     * @return array
     */
    private function _getAllStoresBaseUrls()
    {
        static $urls;
        if (!isset($urls)) {
            foreach ($this->storeManager->getStores() as $store) {
                $urls[] = [
                    'store_code' => $store->getCode(),
                    'store_base_url' => $store->getBaseUrl()
                ];
            }
        }

        return $urls;
    }
}