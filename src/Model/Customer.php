<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Customer extends \Magento\Framework\Model\AbstractModel implements \ActiveCampaign\Integration\Model\SyncInterface
{
    /**
     * @var \ActiveCampaign\Integration\Helper\Api
     */
    protected $apiHelper;

    /**
     * @var \ActiveCampaign\Api\Models\ContactFactory
     */
    protected $contactFactory;

    /**
     * @var \ActiveCampaign\Api\Models\EcomCustomerFactory
     */
    protected $ecomCustomerFactory;

    /**
     * @var \ActiveCampaign\Api\Contacts
     */
    protected $contactsApi;

    /**
     * @var \ActiveCampaign\Api\EcomCustomers
     */
    protected $ecomCustomersApi;

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
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \ActiveCampaign\Integration\Helper\Api $apiHelper
     * @param \ActiveCampaign\Api\Models\ContactFactory $contactFactory
     * @param \ActiveCampaign\Api\Models\EcomCustomerFactory $ecomCustomerFactory
     * @param \ActiveCampaign\Api\Contacts $contactsApi
     * @param \ActiveCampaign\Api\EcomCustomers $ecomCustomersApi
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
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
        \ActiveCampaign\Api\Models\ContactFactory $contactFactory,
        \ActiveCampaign\Api\Models\EcomCustomerFactory $ecomCustomerFactory,
        \ActiveCampaign\Api\Contacts $contactsApi,
        \ActiveCampaign\Api\EcomCustomers $ecomCustomersApi,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->apiHelper = $apiHelper;
        $this->contactFactory = $contactFactory;
        $this->ecomCustomerFactory = $ecomCustomerFactory;
        $this->contactsApi = $contactsApi;
        $this->ecomCustomersApi = $ecomCustomersApi;
        $this->customerRepository = $customerRepository;
        $this->addressRepository = $addressRepository;
        $this->eavAttribute = $eavAttribute;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Get AC contact ID
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return int|null
     */
    public function getAcContactId(\Magento\Customer\Api\Data\CustomerInterface $customer): ?int
    {
        if ($customer->getCustomAttribute(\ActiveCampaign\Integration\Model\CustomerAttribute::AC_CONTACT_ID)) {
            return (int)$customer->getCustomAttribute(
                \ActiveCampaign\Integration\Model\CustomerAttribute::AC_CONTACT_ID
            );
        }

        return null;
    }

    /**
     * Get AC customer ID
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return int|null
     */
    public function getAcCustomerId(\Magento\Customer\Api\Data\CustomerInterface $customer): ?int
    {
        if ($customer->getCustomAttribute(\ActiveCampaign\Integration\Model\CustomerAttribute::AC_CUSTOMER_ID)) {
            return (int)$customer->getCustomAttribute(
                \ActiveCampaign\Integration\Model\CustomerAttribute::AC_CUSTOMER_ID
            );
        }

        return null;
    }

    /**
     * Get AC sync status
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return int
     */
    public function getAcSyncStatus(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        return (int)$customer->getCustomAttribute(
            \ActiveCampaign\Integration\Model\CustomerAttribute::AC_SYNC_STATUS
        );
    }

    /**
     * Iterator callback
     *
     * Sync contacts traversed by the resource iterator.
     *
     * @param array $args
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function iteratorCallback(array $args): void
    {
        try {
            if (empty($args['row']['entity_id'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unable to retrieve customer.'));
            }

            $customer = $this->customerRepository->getById((int)$args['row']['entity_id']);

            $this->syncContactFromCustomer($customer);
        } catch (\Exception $e) {
            // Log exception and continue walk
            $this->apiHelper->logger->critical($e);

            throw $e;
        }
    }

    public function sync()
    {

    }

    /**
     * Sync contact from customer object
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function syncContactFromCustomer(
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        bool $save = false
    ): void {
        $exception = null;

        try {
            $contact = $this->contactFactory->create();

            $contact
                ->setEmail($customer->getEmail())
                ->setFirstName($customer->getFirstname())
                ->setLastName($customer->getLastname())
                ->setPhone($this->getTelephone((int)$customer->getDefaultBilling()))
                ->setFieldValues($this->getFieldValues($customer))
            ;

            // Sync contact
            $contactResponse = $this->contactsApi->setConfig(
                $this->apiHelper->getApiKey($customer->getStoreId()),
                $this->apiHelper->getApiUrl($customer->getStoreId()),
                $this->apiHelper->isDebugActive($customer->getStoreId())
            )->sync($contact);

            if (empty($contactResponse->result['contact']['id'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Unable to retrieve contact ID from result')
                );
            }

            $acContactId = (int)$contactResponse->result['contact']['id'];

            // Sync ecom customer
            $acCustomerId = $this->syncEcomCustomer($customer);

            // Update custom attributes for customer
            $customer->setCustomAttribute(
                \ActiveCampaign\Integration\Model\CustomerAttribute::AC_CONTACT_ID,
                $acContactId
            );

            $customer->setCustomAttribute(
                \ActiveCampaign\Integration\Model\CustomerAttribute::AC_CUSTOMER_ID,
                $acCustomerId
            );

            $customer->setCustomAttribute(
                \ActiveCampaign\Integration\Model\CustomerAttribute::AC_SYNC_STATUS,
                \ActiveCampaign\Integration\Model\Source\SyncStatus::STATUS_COMPLETE
            );
        } catch (\Exception $exception) {
            $customer->setCustomAttribute(
                \ActiveCampaign\Integration\Model\CustomerAttribute::AC_SYNC_STATUS,
                \ActiveCampaign\Integration\Model\Source\SyncStatus::STATUS_FAILED
            );
        }

        $customer->setData('ignore_validation_flag', true);

        $this->customerRepository->save($customer);

        if ($exception instanceof \Exception) {
            throw $exception;
        }
    }

    /**
     * Sync contact from order object
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function syncContactFromOrder(
        \Magento\Sales\Api\Data\OrderInterface $order
    ): int {
        try {
            $customer = $this->customerRepository->getById($order->getCustomerId());

            return $this->syncContactFromCustomer($customer);
        } catch (\Magento\Framework\Exception\NoSuchEntityException) {
            $customer = null;
        }

        $contact = $this->contactFactory->create();

        $contact
            ->setEmail($order->getBillingAddress()->getEmail())
            ->setFirstName($order->getBillingAddress()->getFirstname())
            ->setLastName($order->getBillingAddress()->getLastname())
            ->setPhone($order->getBillingAddress()->getTelephone())
        ;

        return $this->syncContact($contact, $order->getStoreId());
    }

    /**
     * Sync ecom customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return int|null
     * @throws \ActiveCampaign\Gateway\ResultException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function syncEcomCustomer(
        \Magento\Customer\Api\Data\CustomerInterface $customer
    ): ?int {
        $connectionId = $this->apiHelper->getConnectionId($customer->getStoreId());

        if ($connectionId) {
            /** @var \ActiveCampaign\Api\Models\EcomCustomer $ecomCustomer */
            $ecomCustomer = $this->ecomCustomerFactory->create();
            $ecomCustomer
                ->setConnectionId($connectionId)
                ->setExternalId((string)$customer->getId())
                ->setEmail($customer->getEmail())
            ;

            $this->ecomCustomersApi->setConfig(
                $this->apiHelper->getApiKey($customer->getStoreId()),
                $this->apiHelper->getApiUrl($customer->getStoreId()),
                $this->apiHelper->isDebugActive($customer->getStoreId())
            );

            $acCustomerId = $this->getAcCustomerId($customer);

            if ($acCustomerId) {
                $ecomCustomerResponse = $this->ecomCustomersApi->update($acCustomerId, $ecomCustomer);
            } else {
                $ecomCustomerResponse = $this->ecomCustomersApi->create($ecomCustomer);
            }

            if (empty($ecomCustomerResponse->result['ecomCustomer']['id'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Unable to retrieve ecomCustomer ID from result')
                );
            }

            return (int)$ecomCustomerResponse->result['ecomCustomer']['id'];
        }

        return null;
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
