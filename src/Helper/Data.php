<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected const MODULE_CONFIG_PATH = 'activecampaign_integration/general';

    public const COLUMN_AC_SYNC_ID = 'ac_sync_id';
    public const COLUMN_AC_SYNC_STATUS = 'ac_sync_status';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Magento\Backend\App\Config
     */
    private $backendConfig;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    private $cacheFrontendPool;

    /**
     * @var \Magento\Theme\Block\Html\Header\Logo
     */
    private $logo;

    /**
     * @var array
     */
    private array $isArea = [];

    /**
     * @var \ActiveCampaign\Integration\Logger\Logger
     */
    public $logger;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Theme\Block\Html\Header\Logo $logo
     * @param \ActiveCampaign\Integration\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Theme\Block\Html\Header\Logo $logo,
        \ActiveCampaign\Integration\Logger\Logger $logger
    ) {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->configWriter = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->logo = $logo;
        $this->logger = $logger;

        parent::__construct($context);
    }

    /**
     * Get store config
     *
     * @param string $field
     * @param \Magento\Store\Api\Data\StoreInterface|int|string|null $scopeCode
     * @param string $scopeType
     * @return mixed
     */
    public function getStoreConfig(
        string $field = '',
        \Magento\Store\Api\Data\StoreInterface|int|string $scopeCode = null,
        string $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ): mixed {
        $path = static::MODULE_CONFIG_PATH . '/' .  $field;

        if (($scopeCode === null || $scopeCode === 0) && !$this->isArea()) {
            if (!$this->backendConfig) {
                $this->backendConfig = $this->objectManager->get(\Magento\Backend\App\ConfigInterface::class);
            }

            return $this->backendConfig->getValue($path);
        }

        return $this->scopeConfig->getValue($path, $scopeType, $scopeCode);
    }

    /**
     * Is store config flag
     *
     * @param string $field
     * @param \Magento\Store\Api\Data\StoreInterface|int|string|null $scopeCode
     * @param string $scopeType
     *
     * @return bool
     */
    public function isStoreConfigFlag(
        string $field = '',
        \Magento\Store\Api\Data\StoreInterface|int|string $scopeCode = null,
        string $scopeType = \Magento\Store\Model\ScopeInterface::SCOPE_STORE
    ): bool {
        $path = static::MODULE_CONFIG_PATH . '/' .  $field;

        if ($scopeCode === null && !$this->isArea()) {
            if (!$this->backendConfig) {
                $this->backendConfig = $this->objectManager
                    ->get(\Magento\Backend\App\ConfigInterface::class);
            }

            return $this->backendConfig->isSetFlag($path);
        }

        return $this->scopeConfig->isSetFlag($path, $scopeType, $scopeCode);
    }

    /**
     * Set store config
     *
     * @param string $field
     * @param string $value
     * @param int $scopeId
     *
     * @return \ActiveCampaign\Integration\Helper\Data
     */
    public function setStoreConfig(
        string $field = '',
        string $value = '',
        int $scopeId = 0
    ) {
        $field = static::MODULE_CONFIG_PATH . '/' .  $field;

        $scope = ($scopeId)
            ? \Magento\Store\Model\ScopeInterface::SCOPE_STORES
            : \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

        $this->configWriter->save($field, $value, $scope, $scopeId);

        return $this;
    }

    /**
     * Delete store config
     *
     * @param string $field
     * @param int $scopeId
     *
     * @return \ActiveCampaign\Integration\Helper\Data
     */
    public function deleteStoreConfig(
        string $field = '',
        int $scopeId = 0
    ) {
        $field = static::MODULE_CONFIG_PATH . '/' .  $field;

        $scope = ($scopeId)
            ? \Magento\Store\Model\ScopeInterface::SCOPE_STORES
            : \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

        $this->configWriter->delete($field, $scope, $scopeId);

        return $this;
    }

    /**
     * Set store crontab config
     *
     * @param string $expression
     * @param string $expressionPath
     * @param string $modelPath
     * @param int $scopeId
     *
     * @return \ActiveCampaign\Integration\Helper\Data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setStoreCrontabConfig(
        string $expression,
        string $expressionPath,
        string $modelPath,
        int $scopeId = 0
    ) {
        $scope = ($scopeId)
            ? \Magento\Store\Model\ScopeInterface::SCOPE_STORES
            : \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

        if (!$expression) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid cron expression specified.')
            );
        }

        if (!$expressionPath) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid cron expression path specified.')
            );
        }

        if (!$modelPath) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid cron model path specified.')
            );
        }

        $this->configWriter->save($expressionPath, $expression, $scope, $scopeId);
        $this->configWriter->save($modelPath, '', $scope, $scopeId);

        return $this;
    }

    /**
     * Is area
     *
     * @param string $area
     *
     * @return bool
     */
    public function isArea(string $area = \Magento\Framework\App\Area::AREA_FRONTEND): bool
    {
        if (!isset($this->isArea[$area])) {
            /** @var \Magento\Framework\App\State $state */
            $state = $this->objectManager->get(\Magento\Framework\App\State::class);

            try {
                $this->isArea[$area] = ($state->getAreaCode() == $area);
            } catch (\Exception) {
                $this->isArea[$area] = false;
            }
        }

        return $this->isArea[$area];
    }

    /**
     * Get store
     *
     * @param \Magento\Store\Api\Data\StoreInterface|int|string|null $store
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore(
        \Magento\Store\Api\Data\StoreInterface|int|string $store = null
    ): \Magento\Store\Api\Data\StoreInterface {
        return $this->storeManager->getStore($store);
    }

    /**
     * Clear cache
     *
     * @param array $types
     *
     * @return void
     */
    public function clearCache(array $types = [])
    {
        if (empty($types)) {
            $types = array_keys($this->cacheTypeList->getTypes());
        }

        foreach ($types as $type) {
            $this->cacheTypeList->cleanType($type);
        }

        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

    /**
     * Get store logo URL
     *
     * @param \Magento\Store\Api\Data\StoreInterface|int|string|null $store
     *
     * @return string
     */
    public function getStoreLogoUrl(\Magento\Store\Api\Data\StoreInterface|int|string $store = null): string
    {
        $folderName = \Magento\Config\Model\Config\Backend\Image\Logo::UPLOAD_DIR;
        $storeLogoPath = $this->scopeConfig->getValue(
            'design/header/logo_src',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $store->getId()
        );
        $path = $folderName . '/' . $storeLogoPath;
        $logoUrl = $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . $path;

        if ($storeLogoPath !== null) {
            $url = $logoUrl;
        } else {
            $url = $this->logo->getLogoSrc();
        }

        return $url;
    }
}
