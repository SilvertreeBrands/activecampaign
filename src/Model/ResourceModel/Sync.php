<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\ResourceModel;

class Sync extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb
{
    /**
     * Table name
     *
     * The main table name for this resource.
     *
     * @var string
     */
    public const TABLE_NAME = 'activecampaign_sync';

    /**
     * @inheritdoc
     *
     * @noinspection PhpMethodNamingConventionInspection
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, \ActiveCampaign\Integration\Api\Data\SyncInterface::ID);
    }

    /**
     * Load by Magento entity
     *
     * @param \ActiveCampaign\Integration\Model\Sync $model
     * @param int $mageEntityId
     * @param string $mageEntityType
     * @param string $acEntityType
     * @param int $storeId
     *
     * @return Sync
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByMageEntity(
        \ActiveCampaign\Integration\Model\Sync $model,
        int $mageEntityId,
        string $mageEntityType,
        string $acEntityType,
        int $storeId
    ): Sync {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            ['acs' => $this->getMainTable()],
            ['*']
        )->where(
            sprintf('acs.%1$s=?', \ActiveCampaign\Integration\Api\Data\SyncInterface::MAGE_ENTITY_ID),
            $mageEntityId
        )->where(
            sprintf('acs.%1$s=?', \ActiveCampaign\Integration\Api\Data\SyncInterface::MAGE_ENTITY_TYPE),
            $mageEntityType
        )->where(
            sprintf('acs.%1$s=?', \ActiveCampaign\Integration\Api\Data\SyncInterface::AC_ENTITY_TYPE),
            $acEntityType
        )->where(
            sprintf('acs.%1$s=?', \ActiveCampaign\Integration\Api\Data\SyncInterface::STORE_ID),
            $storeId
        )->limit(
            1
        );

        $data = $connection->fetchRow($select);

        if ($data) {
            $model->setData($data);
        }

        $this->_afterLoad($model);

        return $this;
    }
}
