<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use ActiveCampaign\Integration\Api\Data\SyncInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Sync extends AbstractExtensibleModel implements SyncInterface, IdentityInterface
{
    public const CACHE_TAG = 'activecampaign_sync';

    /**
     * @inheritdoc
     * @var string|array|bool
     */
    protected $_cacheTag = self::CACHE_TAG;

    /**
     * @inheritdoc
     * @var string
     */
    protected $_eventPrefix = self::CACHE_TAG;

    /**
     * @inheritdoc
     * @var string
     */
    protected $_idFieldName = self::ID;

    /**
     * @inheritdoc
     *
     * @noinspection PhpMethodNamingConventionInspection
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(\ActiveCampaign\Integration\Model\ResourceModel\Sync::class);
        parent::_construct();
    }

    /**
     * @inheritdoc
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritdoc
     */
    public function getStoreId(): int
    {
        return (int)$this->getData(self::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId(int $storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * @inheritdoc
     */
    public function getMageEntityId(): int
    {
        return (int)$this->getData(self::MAGE_ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setMageEntityId(int $entityId)
    {
        return $this->setData(self::MAGE_ENTITY_ID, $entityId);
    }

    /**
     * @inheritdoc
     */
    public function getMageEntityType(): string
    {
        return $this->getData(self::MAGE_ENTITY_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setMageEntityType(string $entityType)
    {
        return $this->setData(self::MAGE_ENTITY_TYPE, $entityType);
    }

    /**
     * @inheritdoc
     */
    public function getAcEntityId(): int
    {
        return (int)$this->getData(self::AC_ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setAcEntityId(int $entityId)
    {
        return $this->setData(self::AC_ENTITY_ID, $entityId);
    }

    /**
     * @inheritdoc
     */
    public function getAcEntityType(): string
    {
        return $this->getData(self::AC_ENTITY_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setAcEntityType(string $entityType)
    {
        return $this->setData(self::AC_ENTITY_TYPE, $entityType);
    }

    /**
     * @inheritdoc
     */
    public function isRemoved(): bool
    {
        return (bool)$this->getData(self::REMOVED);
    }

    /**
     * @inheritdoc
     */
    public function setRemoved(bool $bool)
    {
        return $this->setData(self::REMOVED, $bool);
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): int
    {
        return (int)$this->getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus(int $status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt(): string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt(string $date)
    {
        return $this->setData(self::CREATED_AT, $date);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt(): string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt(string $date)
    {
        return $this->setData(self::UPDATED_AT, $date);
    }
}
