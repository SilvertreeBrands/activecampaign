<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Api\Data;

interface SyncInterface
{
    public const ID = 'sync_id';
    public const STORE_ID = 'store_id';
    public const MAGE_ENTITY_ID = 'mage_entity_id';
    public const MAGE_ENTITY_TYPE = 'mage_entity_type';
    public const AC_ENTITY_ID = 'ac_entity_id';
    public const AC_ENTITY_TYPE = 'ac_entity_type';
    public const REMOVED = 'removed';
    public const STATUS = 'status';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId(): int;

    /**
     * Set store ID
     *
     * @param int $storeId
     *
     * @return SyncInterface
     */
    public function setStoreId(int $storeId);

    /**
     * Get Magento entity ID
     *
     * @return int
     */
    public function getMageEntityId(): int;

    /**
     * Set Magento entity ID
     *
     * @param int $entityId
     *
     * @return SyncInterface
     */
    public function setMageEntityId(int $entityId);

    /**
     * Get Magento entity type
     *
     * @return string
     */
    public function getMageEntityType(): string;

    /**
     * Set Magento entity type
     *
     * @param string $entityType
     *
     * @return SyncInterface
     */
    public function setMageEntityType(string $entityType);

    /**
     * Get ActiveCampaign entity ID
     *
     * @return int
     */
    public function getAcEntityId(): int;

    /**
     * Set ActiveCampaign entity ID
     *
     * @param int $entityId
     *
     * @return SyncInterface
     */
    public function setAcEntityId(int $entityId);

    /**
     * Get ActiveCampaign entity type
     *
     * @return string
     */
    public function getAcEntityType(): string;

    /**
     * Set ActiveCampaign entity type
     *
     * @param string $entityType
     *
     * @return SyncInterface
     */
    public function setAcEntityType(string $entityType);

    /**
     * Is removed
     *
     * @return bool
     */
    public function isRemoved(): bool;

    /**
     * Set removed
     *
     * @param bool $bool
     *
     * @return SyncInterface
     */
    public function setRemoved(bool $bool);

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Set status
     *
     * @param int $status
     *
     * @return SyncInterface
     */
    public function setStatus(int $status);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt(): string;

    /**
     * Set created at
     *
     * @param string $date
     *
     * @return SyncInterface
     */
    public function setCreatedAt(string $date);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt(): string;

    /**
     * Set updated at
     *
     * @param string $date
     *
     * @return SyncInterface
     */
    public function setUpdatedAt(string $date);
}
