<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Helper;

class Api extends \ActiveCampaign\Integration\Helper\Data
{
    public const CONFIG_ACTIVE = 'active';
    public const CONFIG_DEBUG = 'debug';
    public const CONFIG_API_URL = 'api_url';
    public const CONFIG_API_KEY = 'api_key';
    public const CONFIG_CONNECTION_NAME = 'connection_name';
    public const CONFIG_CONNECTION_ID = 'connection_id';

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
     * Get connection name
     *
     * @param \Magento\Store\Api\Data\StoreInterface|int|string|null $store
     *
     * @return string
     */
    public function getConnectionName(\Magento\Store\Api\Data\StoreInterface|int|string $store = null): string
    {
        $name = $this->getStoreConfig(
            self::CONFIG_CONNECTION_NAME,
            $store
        );

        if (!$name) {
            try {
                $storeName = $this->getStore($store)->getName();
            } catch (\Exception) {
                $storeName = '';
            }

            return sprintf('Magento2 - %s', $storeName);
        }

        return $name;
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
     * Get service name
     *
     * @param string $connectionName
     *
     * @return string|null
     */
    public function getServiceName(string $connectionName): ?string
    {
        $name = str_replace(':', '', $connectionName);
        $name = str_replace(' - ', '-', $name);
        $name = str_replace('  ', ' ', $name);
        return strtolower(str_replace(' ', '-', $name));
    }

    /**
     * Get connected stores
     *
     * @return array
     */
    public function getConnectedStores(): array
    {
        $result = [];

        foreach ($this->storeManager->getStores() as $store) {
            $result[$store->getId()]['name'] = $store->getName();
            $result[$store->getId()]['status'] = $this->getConnectionId($store);
        }

        return $result;
    }
}
