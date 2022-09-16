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
    private $syncRepository;

    /**
     * @var \ActiveCampaign\Integration\Model\Sync\Queue\Contact
     */
    private $syncQueueContact;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    private $extensionFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Construct
     *
     * @param \ActiveCampaign\Integration\Helper\Customer $customerHelper
     * @param \ActiveCampaign\Integration\Api\SyncRepositoryInterface $syncRepository
     * @param \ActiveCampaign\Integration\Model\Sync\Queue\Contact $syncQueueContact
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \ActiveCampaign\Integration\Helper\Customer $customerHelper,
        \ActiveCampaign\Integration\Api\SyncRepositoryInterface $syncRepository,
        \ActiveCampaign\Integration\Model\Sync\Queue\Contact $syncQueueContact,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->customerHelper = $customerHelper;
        $this->syncRepository = $syncRepository;
        $this->syncQueueContact = $syncQueueContact;
        $this->extensionFactory = $extensionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Add extension attribute
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $subject
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Customer\Api\CustomerRepositoryInterface $subject,
        \Magento\Customer\Api\Data\CustomerInterface $customer
    ) {
        try {
            $extensionAttributes = $customer->getExtensionAttributes();

            if ($extensionAttributes === null || $extensionAttributes->getAcSyncs() === null) {
                $syncs = $this->getSyncs($customer);
                $this->addExtensionAttribute($customer, $syncs);
            }
        } catch (\Exception $e) {
            $this->customerHelper->logger->critical($e);
        }

        return $customer;
    }

    /**
     * Add extension attribute
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $subject
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetById(
        \Magento\Customer\Api\CustomerRepositoryInterface $subject,
        \Magento\Customer\Api\Data\CustomerInterface $customer
    ) {
        try {
            $extensionAttributes = $customer->getExtensionAttributes();

            if ($extensionAttributes === null || $extensionAttributes->getAcSyncs() === null) {
                $syncs = $this->getSyncs($customer);
                $this->addExtensionAttribute($customer, $syncs);
            }
        } catch (\Exception $e) {
            $this->customerHelper->logger->critical($e);
        }

        return $customer;
    }

    /**
     * Add subscription status to customer list
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $subject
     * @param \Magento\Framework\Api\SearchResults $searchResults
     *
     * @return \Magento\Framework\Api\SearchResults
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        \Magento\Customer\Api\CustomerRepositoryInterface $subject,
        \Magento\Framework\Api\SearchResults $searchResults
    ): \Magento\Framework\Api\SearchResults {
        try {
            /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
            foreach ($searchResults->getItems() as $customer) {
                $extensionAttributes = $customer->getExtensionAttributes();

                if ($extensionAttributes === null || $extensionAttributes->getAcSyncs() === null) {
                    $syncs = $this->getSyncs($customer);
                    $this->addExtensionAttribute($customer, $syncs);
                }
            }
        } catch (\Exception $e) {
            $this->customerHelper->logger->critical($e);
        }

        return $searchResults;
    }

    /**
     * After save
     *
     * Check if customer has updates, then sync to ActiveCampaign.
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $subject
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface $customer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Customer\Api\CustomerRepositoryInterface $subject,
        \Magento\Customer\Api\Data\CustomerInterface $customer
    ) {
        try {
            if ($customer->getId()
                && $this->customerHelper->isActive($customer->getStoreId())
            ) {
                $original = $subject->getById($customer->getId());

                if ($original->getEmail() !== $customer->getEmail()) {
                    $this->syncQueueContact->execute($customer->getId(), $customer->getStoreId());
                }
            }
        } catch (\Exception $e) {
            $this->customerHelper->logger->critical($e);
        }

        return $customer;
    }

    /**
     * Before delete by ID
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $subject
     * @param int $customerId
     *
     * @return null
     */
    public function beforeDeleteById(
        \Magento\Customer\Api\CustomerRepositoryInterface $subject,
        int $customerId
    ) {
        try {
            $customer = $subject->getById($customerId);

            if ($customer->getId()
                && $this->customerHelper->isActive($customer->getStoreId())
            ) {
                $this->syncQueueContact->execute(
                    $customer->getId(),
                    $customer->getStoreId(),
                    true
                );
            }
        } catch (\Exception $e) {
            $this->customerHelper->logger->critical($e);
        }

        return null;
    }

    /**
     * Get sync list by customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return \ActiveCampaign\Integration\Api\Data\SyncInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getSyncs(
        \Magento\Customer\Api\Data\CustomerInterface $customer
    ) {
        return $this->syncRepository->getList(
            $this->searchCriteriaBuilder
                ->addFilter('store_id', (int)$customer->getStoreId())
                ->addFilter('mage_entity_type', \ActiveCampaign\Integration\Model\Source\MageEntityType::CUSTOMER)
                ->addFilter('mage_entity_id', (int)$customer->getId())
                ->create()
        )->getItems();
    }

    /**
     * Add extension attribute
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param \ActiveCampaign\Integration\Api\Data\SyncInterface[] $syncs
     */
    private function addExtensionAttribute(
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        array $syncs
    ): void {
        $extensionAttributes = $customer->getExtensionAttributes();

        if ($extensionAttributes === null) {
            /** @var \Magento\Customer\Api\Data\CustomerExtensionInterface $extensionAttributes */
            $extensionAttributes = $this->extensionFactory->create(
                \Magento\Customer\Api\Data\CustomerInterface::class
            );

            $customer->setExtensionAttributes($extensionAttributes);
        }

        $extensionAttributes->setAcSyncs($syncs);
    }
}
