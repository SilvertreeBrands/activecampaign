<?php
declare(strict_types=1);

namespace ActiveCampaign\Core\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const ACTIVE_CAMPAIGN_GENERAL_STATUS = 'active_campaign/general/status';
    public const ACTIVE_CAMPAIGN_GENERAL_API_URL = 'active_campaign/general/api_url';
    public const ACTIVE_CAMPAIGN_GENERAL_API_KEY = 'active_campaign/general/api_key';
    public const ACTIVE_CAMPAIGN_GENERAL_CONNECTION_ID = 'active_campaign/general/connection_id';

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    private $configInterface;

    /**
     * Construct
     *
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configInterface
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configInterface,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->storeRepository = $storeRepository;
        $this->configInterface = $configInterface;
    }


    /**
     * Converts to cents the price amount
     *
     * @param float|null $price
     *
     * @return int
     */
    public function priceToCents(?float $price = 0.0): int
    {
        return (int) (round($price, 2) * 100);
    }

    /**
     * Check connections
     *
     * @param array $allConnections
     *
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkConnections(array $allConnections): bool
    {
        if ($allConnections['success']) {
            $activeConnectionIds = [];

            foreach ($allConnections['data']['connections'] as $connection) {
                $store = $this->storeRepository->get($connection['externalid']);
                $connectionId = $this->getConnectionId($store->getId());
                $activeConnectionIds[] = $connection['id'];

                if ($connectionId != $connection['id']) {
                    $this->saveConfig(
                        self::ACTIVE_CAMPAIGN_GENERAL_CONNECTION_ID,
                        $connection['id'],
                        $store->getId()
                    );
                }
            }

            $stores = $this->storeRepository->getList();

            foreach ($stores as $store) {
                if ($store->getId()) {
                    $connectionId = $this->getConnectionId($store->getId());
                    if (($connectionId) && (!in_array($connectionId, $activeConnectionIds))) {
                        $this->configInterface->deleteConfig(
                            self::ACTIVE_CAMPAIGN_GENERAL_CONNECTION_ID,
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
                            $store->getId()
                        );
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Save config
     *
     * @param string $path
     * @param string $value
     * @param int $scopeId
     *
     * @return void
     */
    public function saveConfig(
        string $path,
        string $value,
        int $scopeId
    ) {
        $scope = ($scopeId)
            ? \Magento\Store\Model\ScopeInterface::SCOPE_STORES
            : \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        $this->configInterface->saveConfig($path, $value, $scope, $scopeId);
    }
}
