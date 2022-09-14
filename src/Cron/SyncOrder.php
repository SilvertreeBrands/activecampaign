<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Cron;

class SyncOrder
{
    /**
     * @var \ActiveCampaign\Integration\Helper\Order
     */
    protected $helper;

    /**
     * @var \ActiveCampaign\Integration\Model\ResourceModel\Sync\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \ActiveCampaign\Integration\Model\ResourceIterator
     */
    protected $resourceIterator;

    /**
     * @var \ActiveCampaign\Integration\Model\Sync\Processor\Order
     */
    protected $processor;

    /**
     * Construct
     *
     * @param \ActiveCampaign\Integration\Helper\Order $helper
     * @param \ActiveCampaign\Integration\Model\ResourceModel\Sync\CollectionFactory $collectionFactory
     * @param \ActiveCampaign\Integration\Model\ResourceIterator $resourceIterator
     * @param \ActiveCampaign\Integration\Model\Sync\Processor\Order $processor
     */
    public function __construct(
        \ActiveCampaign\Integration\Helper\Order $helper,
        \ActiveCampaign\Integration\Model\ResourceModel\Sync\CollectionFactory $collectionFactory,
        \ActiveCampaign\Integration\Model\ResourceIterator $resourceIterator,
        \ActiveCampaign\Integration\Model\Sync\Processor\Order $processor
    ) {
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->resourceIterator = $resourceIterator;
        $this->processor = $processor;
    }

    /**
     * Execute
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            if ($this->helper->isActive()) {
                $collection = $this->collectionFactory->create()
                    ->addFieldToFilter(
                        \ActiveCampaign\Integration\Api\Data\SyncInterface::ENTITY_TYPE,
                        ['eq' => \ActiveCampaign\Integration\Model\Source\AcEntityType::ECOM_ORDER]
                    )
                    ->addFieldToFilter(
                        \ActiveCampaign\Integration\Api\Data\SyncInterface::STATUS,
                        ['eq' => \ActiveCampaign\Integration\Model\Source\SyncStatus::STATUS_PENDING]
                    )
                    ->setPageSize($this->helper->getSyncBatchSize())
                ;

                $collection->getSelect()->limit($this->helper->getSyncBatchSize());

                $this->resourceIterator->walk(
                    $collection->getSelect(),
                    [[$this->processor, 'iteratorCallback']]
                );
            }
        } catch (\Exception $e) {
            $this->helper->logger->critical($e);
        }
    }
}
