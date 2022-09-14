<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\ResourceModel\Sync;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @inheritdoc
     * @var string
     */
    protected $_idFieldName = \ActiveCampaign\Integration\Api\Data\SyncInterface::ID;

    /**
     * @inheritdoc
     * @var string
     */
    protected $_eventPrefix = 'activecampaign_sync_collection';

    /**
     * @inheritdoc
     * @var string
     */
    protected $_eventObject = 'sync_collection';

    /**
     * @inheritdoc
     *
     * @noinspection PhpMethodNamingConventionInspection
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     */
    protected function _construct()
    {
        $this->_init(
            \ActiveCampaign\Integration\Model\Sync::class,
            \ActiveCampaign\Integration\Model\ResourceModel\Sync::class
        );
        parent::_construct();
    }
}
