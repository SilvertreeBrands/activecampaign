<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    private const MODULE_CONFIG_PATH = 'active_campaign/general';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Backend\App\Config
     */
    private $backendConfig;

    /**
     * @var array
     */
    private array $isArea = [];

    /**
     * @var \ActiveCampaign\Integration\Logger\Logger
     */
    protected $logger;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \ActiveCampaign\Integration\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \ActiveCampaign\Integration\Logger\Logger $logger
    ) {
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
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

        if ($scopeCode === null && !$this->isArea()) {
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
     * @param int|null $storeId
     *
     * @return \Magento\Store\Api\Data\StoreInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStore(int $storeId = null): \Magento\Store\Api\Data\StoreInterface
    {
        return $this->storeManager->getStore($storeId);
    }
}
