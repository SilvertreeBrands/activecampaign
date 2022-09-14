<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\Sync\Queue;

abstract class AbstractQueue extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \ActiveCampaign\Integration\Helper\Data
     */
    protected $helper;

    /**
     * @var \ActiveCampaign\Integration\Api\SyncRepositoryInterface
     */
    protected $syncRepository;

    /**
     * @var \ActiveCampaign\Integration\Api\Data\SyncInterfaceFactory
     */
    protected $syncModelFactory;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \ActiveCampaign\Integration\Helper\Data $helper
     * @param \ActiveCampaign\Integration\Api\SyncRepositoryInterface $syncRepository
     * @param \ActiveCampaign\Integration\Api\Data\SyncInterfaceFactory $syncModelFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \ActiveCampaign\Integration\Helper\Data $helper,
        \ActiveCampaign\Integration\Api\SyncRepositoryInterface $syncRepository,
        \ActiveCampaign\Integration\Api\Data\SyncInterfaceFactory $syncModelFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->syncRepository = $syncRepository;
        $this->syncModelFactory = $syncModelFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Iterator callback
     *
     * Sync items traversed by the resource iterator.
     *
     * @param array $args
     *
     * @return void
     */
    abstract public function iteratorCallback(array $args): void;

    /**
     * Execute
     *
     * @param int $entityId
     * @param int $storeId
     * @param bool $remove
     *
     * @return void
     */
    abstract public function execute(int $entityId, int $storeId, bool $remove = false): void;
}
