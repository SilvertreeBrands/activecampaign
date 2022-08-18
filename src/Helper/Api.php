<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Helper;

class Api extends \ActiveCampaign\Integration\Helper\Data
{
    private const CONFIG_STATUS = 'status';
    private const CONFIG_DEBUG = 'debug';
    private const CONFIG_API_URL = 'api_url';
    private const CONFIG_API_KEY = 'api_key';
    private const CONFIG_CONNECTION_ID = 'connection_id';

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
            self::CONFIG_STATUS,
            $store
        );
    }

    /**
     * Is debug active
     *
     * @param \Magento\Store\Api\Data\StoreInterface|int|string|null $store
     *
     * @return bool
     */
    public function isDebugActive(\Magento\Store\Api\Data\StoreInterface|int|string $store = null): bool
    {
        return $this->isStoreConfigFlag(
            self::CONFIG_DEBUG,
            $store
        );
    }

    /**
     * Get API URL
     *
     * @param \Magento\Store\Api\Data\StoreInterface|int|string|null $store
     *
     * @return string|null
     */
    public function getApiUrl(\Magento\Store\Api\Data\StoreInterface|int|string $store = null): ?string
    {
        return $this->getStoreConfig(
            self::CONFIG_API_URL,
            $store
        );
    }

    /**
     * Get API key
     *
     * @param \Magento\Store\Api\Data\StoreInterface|int|string|null $store
     *
     * @return string|null
     */
    public function getApiKey(\Magento\Store\Api\Data\StoreInterface|int|string $store = null): ?string
    {
        return $this->getStoreConfig(
            self::CONFIG_API_KEY,
            $store
        );
    }

    /**
     * Get connection ID
     *
     * @param \Magento\Store\Api\Data\StoreInterface|int|string|null $store
     *
     * @return string|null
     */
    public function getConnectionId(\Magento\Store\Api\Data\StoreInterface|int|string $store = null): ?string
    {
        return $this->getStoreConfig(
            self::CONFIG_CONNECTION_ID,
            $store
        );
    }

    /**
     * Get gateway client
     *
     * @param \Magento\Store\Api\Data\StoreInterface|int|string|null $store
     *
     * @return \ActiveCampaign\Gateway\Client
     */
    public function getGatewayClient(\Magento\Store\Api\Data\StoreInterface|int|string $store = null)
    {
        return new \ActiveCampaign\Gateway\Client(
            $this->getApiKey($store),
            $this->getApiUrl($store),
            $this->logger,
            $this->isDebugActive($store)
        );
    }
}
