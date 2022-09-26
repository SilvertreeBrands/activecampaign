<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Plugin;

class CustomerRepositoryPlugin
{
    /**
     * @var \ActiveCampaign\Integration\Helper\Customer
     */
    private $customerHelper;

    /**
     * @var \ActiveCampaign\Integration\Api\SyncRepositoryInterface
     */
    protected $syncRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Construct
     *
     * @param \ActiveCampaign\Integration\Helper\Customer $customerHelper
     * @param \ActiveCampaign\Integration\Api\SyncRepositoryInterface $syncRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \ActiveCampaign\Integration\Helper\Customer $customerHelper,
        \ActiveCampaign\Integration\Api\SyncRepositoryInterface $syncRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->customerHelper = $customerHelper;
        $this->syncRepository = $syncRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * After get list
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $subject
     * @param \Magento\Customer\Api\Data\CustomerSearchResultsInterface $searchResults
     *
     * @return \Magento\Customer\Api\Data\CustomerSearchResultsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        \Magento\Customer\Api\CustomerRepositoryInterface $subject,
        \Magento\Customer\Api\Data\CustomerSearchResultsInterface $searchResults
    ) {
        $items = [];

        foreach ($searchResults->getItems() as $entity) {
            $syncCollection = $this->syncRepository->getList(
                $this->searchCriteriaBuilder
                    ->addFilter('store_id', $entity->getStoreId())
                    ->addFilter('mage_entity_type', \ActiveCampaign\Integration\Model\Source\MageEntityType::CUSTOMER)
                    ->addFilter('mage_entity_id', $entity->getId())
                    ->create()
            )->getItems();

            $extensionAttributes = $entity->getExtensionAttributes();
            $extensionAttributes->setAcSyncs($syncCollection);
            $entity->setExtensionAttributes($extensionAttributes);

            $items[] = $entity;
        }

        $searchResults->setItems($items);

        return $searchResults;
    }
}
