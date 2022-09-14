<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Helper;

class Order extends \ActiveCampaign\Integration\Helper\Data
{
    protected const MODULE_CONFIG_PATH = 'activecampaign_integration/order';

    public const CONFIG_ACTIVE = 'active';
    public const CONFIG_SYNC_BATCH_SIZE = 'sync_batch_size';
    public const CONFIG_CRON = 'cron';

    /**
     * Is active
     *
     * @param \Magento\Store\Api\Data\StoreInterface|int|string|null $store
     *
     * @return bool
     */
    public function isActive(\Magento\Store\Api\Data\StoreInterface|int|string $store = null): bool
    {
        return $this->isStoreConfigFlag(
            self::CONFIG_ACTIVE,
            $store
        );
    }

    /**
     * Get sync batch size
     *
     * @param \Magento\Store\Api\Data\StoreInterface|int|string|null $store
     *
     * @return int
     */
    public function getSyncBatchSize(\Magento\Store\Api\Data\StoreInterface|int|string $store = null): int
    {
        $value = (int)$this->getStoreConfig(
            self::CONFIG_SYNC_BATCH_SIZE,
            $store
        );

        if ($value > 0) {
            return $value;
        }

        return 100;
    }

    /**
     * Get cron
     *
     * @param \Magento\Store\Api\Data\StoreInterface|int|string|null $store
     *
     * @return string|null
     */
    public function getCron(\Magento\Store\Api\Data\StoreInterface|int|string $store = null): ?string
    {
        return $this->getStoreConfig(
            self::CONFIG_CRON,
            $store
        );
    }
}
