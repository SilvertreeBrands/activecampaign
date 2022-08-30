<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Cron;

class SyncCustomer
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollectionFactory;

    /**
     * @var \ActiveCampaign\Integration\Model\ResourceIterator
     */
    protected $resourceIterator;

    /**
     * @var \ActiveCampaign\Integration\Model\Customer
     */
    protected $acCustomer;

    /**
     * @var \ActiveCampaign\Integration\Helper\Customer
     */
    protected $customerHelper;

    /**
     * Construct
     *
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory
     * @param \ActiveCampaign\Integration\Model\ResourceIterator $resourceIterator
     * @param \ActiveCampaign\Integration\Model\Customer $acCustomer
     * @param \ActiveCampaign\Integration\Helper\Customer $customerHelper
     */
    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \ActiveCampaign\Integration\Model\ResourceIterator $resourceIterator,
        \ActiveCampaign\Integration\Model\Customer $acCustomer,
        \ActiveCampaign\Integration\Helper\Customer $customerHelper
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->resourceIterator = $resourceIterator;
        $this->acCustomer = $acCustomer;
        $this->customerHelper = $customerHelper;
    }

    /**
     * Execute
     *
     * @return void
     */
    public function execute(): void
    {
        try {
            if ($this->customerHelper->isActive()) {
                $collection = $this->customerCollectionFactory->create()
                    ->addAttributeToFilter([
                        [
                            'attribute' => \ActiveCampaign\Integration\Model\CustomerAttribute::AC_SYNC_STATUS,
                            'null'      => true
                        ],
                        [
                            'attribute' => \ActiveCampaign\Integration\Model\CustomerAttribute::AC_SYNC_STATUS,
                            'in'        => [
                                \ActiveCampaign\Integration\Model\Source\SyncStatus::STATUS_PENDING,
                                \ActiveCampaign\Integration\Model\Source\SyncStatus::STATUS_UPDATE
                            ]
                        ]
                    ])
                    ->setPageSize($this->customerHelper->getSyncBatchSize());

                $collection->getSelect()->limit($this->customerHelper->getSyncBatchSize());

                $this->resourceIterator->walk(
                    $collection->getSelect(),
                    [[$this->acCustomer, 'iteratorCallback']]
                );
            }
        } catch (\Exception $e) {
            $this->customerHelper->logger->critical($e);
        }
    }
}
