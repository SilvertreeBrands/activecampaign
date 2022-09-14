<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Cron;

class SyncContact
{
    /**
     * @var \ActiveCampaign\Integration\Helper\Customer
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
     * @var \ActiveCampaign\Integration\Model\Sync\Processor\Contact
     */
    protected $processor;

    /**
     * Construct
     *
     * @param \ActiveCampaign\Integration\Helper\Customer $helper
     * @param \ActiveCampaign\Integration\Model\ResourceModel\Sync\CollectionFactory $collectionFactory
     * @param \ActiveCampaign\Integration\Model\ResourceIterator $resourceIterator
     * @param \ActiveCampaign\Integration\Model\Sync\Processor\Contact $processor
     *
     */
    public function __construct(
        \ActiveCampaign\Integration\Helper\Customer $helper,
        \ActiveCampaign\Integration\Model\ResourceModel\Sync\CollectionFactory $collectionFactory,
        \ActiveCampaign\Integration\Model\ResourceIterator $resourceIterator,
        \ActiveCampaign\Integration\Model\Sync\Processor\Contact $processor
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
                        \ActiveCampaign\Integration\Api\Data\SyncInterface::MAGE_ENTITY_TYPE,
                        ['eq' => \ActiveCampaign\Integration\Model\Source\MageEntityType::CUSTOMER]
                    )
                    ->addFieldToFilter(
                        \ActiveCampaign\Integration\Api\Data\SyncInterface::AC_ENTITY_TYPE,
                        ['eq' => \ActiveCampaign\Integration\Model\Source\AcEntityType::CONTACT]
                    )
                    ->addFieldToFilter(
                        \ActiveCampaign\Integration\Api\Data\SyncInterface::STATUS,
                        ['eq' => \ActiveCampaign\Integration\Model\Source\SyncStatus::PENDING]
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
