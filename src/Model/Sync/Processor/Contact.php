<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\Sync\Processor;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Contact extends AbstractProcessor
{
    /**
     * @var \ActiveCampaign\Api\Models\ContactFactory
     */
    protected $contactFactory;

    /**
     * @var \ActiveCampaign\Api\Contacts
     */
    protected $contactsApi;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \ActiveCampaign\Integration\Helper\Api $apiHelper
     * @param \ActiveCampaign\Integration\Api\SyncRepositoryInterface $syncRepository
     * @param \ActiveCampaign\Integration\Api\Data\SyncInterfaceFactory $syncModelFactory
     * @param \ActiveCampaign\Api\Models\ContactFactory $contactFactory
     * @param \ActiveCampaign\Api\Contacts $contactsApi
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \ActiveCampaign\Integration\Helper\Api $apiHelper,
        \ActiveCampaign\Integration\Api\SyncRepositoryInterface $syncRepository,
        \ActiveCampaign\Integration\Api\Data\SyncInterfaceFactory $syncModelFactory,
        \ActiveCampaign\Api\Models\ContactFactory $contactFactory,
        \ActiveCampaign\Api\Contacts $contactsApi,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->contactFactory = $contactFactory;
        $this->contactsApi = $contactsApi;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->eavAttribute = $eavAttribute;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

        parent::__construct(
            $context,
            $registry,
            $apiHelper,
            $syncRepository,
            $syncModelFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * @inheirtdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function iteratorCallback(array $args): void
    {
        try {
            if (empty($args['row'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unable to fetch sync data.'));
            }

            $sync = $this->syncModelFactory->create();
            $sync->setData($args['row']);

            $this->execute($sync);
        } catch (\Exception $e) {
            // Log exception and continue walk
            $this->apiHelper->logger->critical($e);

            throw $e;
        }
    }

    /**
     * @inheirtdoc
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Exception
     */
    public function execute(\ActiveCampaign\Integration\Api\Data\SyncInterface $sync): void
    {
        if (!$sync->getMageEntityId()
            || $sync->getMageEntityType() !== \ActiveCampaign\Integration\Model\Source\MageEntityType::CUSTOMER
            || $sync->getAcEntityType() !== \ActiveCampaign\Integration\Model\Source\AcEntityType::CONTACT
        ) {
            return;
        }

        try {
            if ($sync->isRemoved()) {
                // Delete contact
                $this->deleteContact($sync->getAcEntityId(), $sync->getStoreId());

                // Fetch and remove ecomCustomer and contact syncs
                $syncCollection = $this->syncRepository->getList(
                    $this->searchCriteriaBuilder
                        ->addFilter('store_id', $sync->getStoreId())
                        ->addFilter('mage_entity_type', $sync->getMageEntityType())
                        ->addFilter('mage_entity_id', $sync->getMageEntityId())
                        ->create()
                )->getItems();

                foreach ($syncCollection as $sync) {
                    $this->syncRepository->delete($sync);
                }
            } else {
                // Add or update contact
                $contactId = $this->syncContact($sync->getMageEntityId(), $sync->getAcEntityId());

                $sync
                    ->setAcEntityId($contactId)
                    ->setStatus(\ActiveCampaign\Integration\Model\Source\SyncStatus::COMPLETE)
                ;

                $this->syncRepository->save($sync);
            }
        } catch (\Exception $e) {
            $sync
                ->setStatus(\ActiveCampaign\Integration\Model\Source\SyncStatus::FAILED)
            ;

            $this->syncRepository->save($sync);

            throw $e;
        }
    }

    /**
     * Sync contact
     *
     * @param int $customerId
     * @param int $contactId
     *
     * @return int
     * @throws \ActiveCampaign\Gateway\ResultException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function syncContact(int $customerId, int $contactId): int
    {
        $customer = $this->customerRepository->getById($customerId);

        $contact = $this->contactFactory->create();

        $contact
            ->setEmail($customer->getEmail())
            ->setFirstName($customer->getFirstname())
            ->setLastName($customer->getLastname())
            ->setPhone($this->getTelephone((int)$customer->getDefaultBilling()))
            ->setFieldValues($this->getFieldValues($customer))
        ;

        $this->contactsApi->setConfig(
            $this->apiHelper->getApiKey($customer->getStoreId()),
            $this->apiHelper->getApiUrl($customer->getStoreId()),
            $this->apiHelper->isDebugActive($customer->getStoreId())
        );

        if ($contactId) {
            $contactResponse = $this->contactsApi->update(
                $contactId,
                $contact
            );
        } else {
            $contactResponse = $this->contactsApi->sync($contact);
        }

        if (empty($contactResponse->result['contact']['id'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to retrieve contact ID from result')
            );
        }

        return (int)$contactResponse->result['contact']['id'];
    }

    /**
     * Delete contact
     *
     * @param int $contactId
     * @param int $storeId
     *
     * @return void
     * @throws \ActiveCampaign\Gateway\ResultException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function deleteContact(int $contactId, int $storeId): void
    {
        if (!$contactId) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to delete contact. Invalid contact ID provided.')
            );
        }

        $this->contactsApi->setConfig(
            $this->apiHelper->getApiKey($storeId),
            $this->apiHelper->getApiUrl($storeId),
            $this->apiHelper->isDebugActive($storeId)
        )->delete($contactId);
    }

    /**
     * Get telephone
     *
     * @param int|null $billingAddressId
     *
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getTelephone(int $billingAddressId = null)
    {
        if ($billingAddressId) {
            $address = $this->addressRepository->getById($billingAddressId);

            return (string)$address->getTelephone();
        }

        return (string)null;
    }

    /**
     * Get field values
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return array
     * @todo The original module uses the attribute ID for the field which is wrong. According to AC docs, the custom
     *       fields should be created in AC and then mapped. We will need to map these fields to Magento attribute codes
     *       manually via system config. For now, we will return an empty array.
     */
    private function getFieldValues(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $fieldValues = [];

        /*foreach ($customer->getCustomAttributes() as $attribute) {
            $fieldValues[] = [
                'field' => $this->eavAttribute
                    ->getIdByCode(\Magento\Customer\Model\Customer::ENTITY, $attribute->getAttributeCode()),
                'value' => $attribute->getValue()
            ];
        }*/

        return $fieldValues;
    }
}
