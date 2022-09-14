<?php
declare(strict_types=1);

namespace ActiveCampaign\Order\Model\OrderData;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderDataSend extends \Magento\Framework\Model\AbstractModel
{
    public const URL_ENDPOINT = 'ecomOrders';
    public const METHOD = 'POST';
    public const UPDATE_METHOD = 'PUT';
    public const GET_METHOD = 'GET';
    public const AC_SYNC_STATUS = 'ac_sync_status';
    public const CONTACT_ENDPOINT = 'contact/sync';
    public const ECOM_CUSTOMER_ENDPOINT = 'ecomOrders';
    public const ECOM_CUSTOMERLIST_ENDPOINT = 'ecomCustomers';

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterfaceFactory
     */
    protected $productRepositoryFactory;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;

    /**
     * @var \ActiveCampaign\Order\Helper\Data
     */
    private $activeCampaignOrderHelper;

    /**
     * @var \ActiveCampaign\Core\Helper\Data
     */
    private $activeCampaignHelper;

    /**
     * @var \ActiveCampaign\Core\Helper\Curl
     */
    protected $curl;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerModel;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $customerResource;

    /**
     * @var \ActiveCampaign\Integration\Helper\Api
     */
    private $apiHelper;

    /**
     * @var \ActiveCampaign\Api\Contacts
     */
    private $contacts;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
     * @param \ActiveCampaign\Order\Helper\Data $activeCampaignOrderHelper
     * @param \ActiveCampaign\Core\Helper\Data $activeCampaignHelper
     * @param \ActiveCampaign\Core\Helper\Curl $curl
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Customer\Model\ResourceModel\Customer $customerResource
     * @param \ActiveCampaign\Integration\Helper\Api $apiHelper
     * @param \ActiveCampaign\Api\Contacts $contacts
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \ActiveCampaign\Order\Helper\Data $activeCampaignOrderHelper,
        \ActiveCampaign\Core\Helper\Data $activeCampaignHelper,
        \ActiveCampaign\Core\Helper\Curl $curl,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \ActiveCampaign\Integration\Helper\Api $apiHelper,
        \ActiveCampaign\Api\Contacts $contacts,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->activeCampaignOrderHelper = $activeCampaignOrderHelper;
        $this->activeCampaignHelper = $activeCampaignHelper;
        $this->curl = $curl;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
        $this->customerModel = $customerModel;
        $this->addressRepository = $addressRepository;
        $this->eavAttribute = $eavAttribute;
        $this->customerResource = $customerResource;
        $this->apiHelper = $apiHelper;
        $this->contacts = $contacts;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Send order data
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function orderDataSend(\Magento\Sales\Api\Data\OrderInterface $order): array
    {
        if (!$this->activeCampaignOrderHelper->isOrderSyncEnabled()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('ActiveCampaign Order syncing is disabled.')
            );
        }

        $result = [];

        try {


            $connectionId = $this->activeCampaignHelper->getConnectionId($order->getStoreId());
            $customerId = $order->getCustomerId();
            $customerAcId = 0;

            if ($customerId) {
                $this->createAcContact($order);
                $customerEmail = $order->getCustomerEmail();
                $customerModel = $this->customerRepositoryInterface->getById($customerId);

                if ($customerModel->getAcCustomerId()) {
                    $customerAcId = $customerModel->getAcCustomerId();
                }
            } else {
                $customerEmail = $order->getBillingAddress()->getEmail();

                $customerModel = $this->customerRepositoryInterface->get(
                    $customerEmail,
                    $this->storeManager->getWebsite()->getWebsiteId()
                );

                if ($customerModel->getId()) {
                    $customerId = $customerModel->getId();
                } else {
                    $customerId = 0;
                }

                $this->createAcContact($order);

                $customerModel = $this->customerFactory->create();
                $this->customerResource->load($customerModel, $customerId);

                if ($customerModel->getAcCustomerId()) {
                    $customerAcId = $customerModel->getAcCustomerId();
                } elseif ($order->getAcTempCustomerId()) {
                    $customerAcId = $order->getAcTempCustomerId();
                } else {
                    $acCustomer = $this->curl->listAllCustomers(
                        self::GET_METHOD,
                        self::ECOM_CUSTOMER_ENDPOINT,
                        $customerEmail
                    );

                    foreach ($acCustomer['data']['ecomCustomers'] as $ac) {
                        if ($ac['connectionid'] == $connectionId) {
                            $customerAcId = $ac['id'];
                        }
                    }
                }
            }

            $items = [];

            foreach ($order->getAllVisibleItems() as $item) {
                $product = $this->productRepositoryFactory->create()
                    ->get($item->getSku());
                $imageUrl = $this->imageHelperFactory->create()
                    ->init($product, 'product_thumbnail_image')->getUrl();

                $items[] = [
                    'externalid'    => $item->getProductId(),
                    'name'          => $item->getName(),
                    'price'         => $this->activeCampaignHelper->priceToCents($item->getPrice()),
                    'quantity'      => $item->getQtyOrdered(),
                    'category'      => implode(', ', $product->getCategoryIds()),
                    'sku'           => $item->getSku(),
                    'description'   => $item->getDescription(),
                    'imageUrl'      => $imageUrl,
                    'productUrl'    => $product->getProductUrl()
                ];
            }

            $data = [
                'ecomOrder' => [
                    'externalid'            => $order->getId(),
                    'source'                => 1,
                    'email'                 => $customerEmail,
                    'orderProducts'         => $items,
                    'orderDiscounts'        => [
                        'discountAmount' => $this->activeCampaignHelper->priceToCents($order->getDiscountAmount())
                    ],
                    'externalCreatedDate'   => $order->getCreatedAt(),
                    'externalUpdatedDate'   => $order->getUpdatedAt(),
                    'shippingMethod'        => $order->getShippingMethod(),
                    'totalPrice'            => $this->activeCampaignHelper->priceToCents($order->getGrandTotal()),
                    'shippingAmount'        => $this->activeCampaignHelper->priceToCents($order->getShippingAmount()),
                    'taxAmount'             => $this->activeCampaignHelper->priceToCents($order->getTaxAmount()),
                    'discountAmount'        => $this->activeCampaignHelper->priceToCents($order->getDiscountAmount()),
                    'currency'              => $order->getOrderCurrencyCode(),
                    'orderNumber'           => $order->getIncrementId(),
                    'connectionid'          => $connectionId,
                    'customerid'            => $customerAcId
                ]
            ];

            if (!$order->getAcOrderSyncId()) {
                $result = $this->curl->genericRequest(
                    self::METHOD,
                    self::URL_ENDPOINT,
                    $data
                );

                if ($result['status'] == '422' || $result['status'] == '400') {
                    $ecomAlreadyExistOrderData = [];
                    $ecomAlreadyExistOrderResult = $this->curl->genericRequest(
                        self::GET_METHOD,
                        self::URL_ENDPOINT,
                        $ecomAlreadyExistOrderData
                    );

                    $ecomOrders = $ecomAlreadyExistOrderResult['data']['ecomOrders'];

                    foreach ($ecomOrders as $ecomKey => $customers) {
                        $ecomOrderArray[$ecomOrders[$ecomKey]['email']] = $ecomOrders[$ecomKey]['id'];
                    }

                    $acOrderId = $ecomOrderArray[$customerEmail];
                } else {
                    $acOrderId = $result['data']['ecomOrders']['id'] ?? null;
                }
            } else {
                $acOrderId = $order->getAcOrderSyncId();
            }

            if ($acOrderId !== 0) {
                $syncStatus = \ActiveCampaign\AbandonedCart\Model\Config\CronConfig::SYNCED;
            } else {
                $syncStatus = \ActiveCampaign\AbandonedCart\Model\Config\CronConfig::FAIL_SYNCED;
            }

            /**
             * @todo Use repository save
             */
            $order->setData('ac_order_sync_status', $syncStatus)
                ->setData('ac_order_sync_id', $acOrderId)
                ->save();

            if (isset($result['success'])) {
                $result['success'] = __('Order data successfully synced!!');
            }
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['errorMessage'] = __($e->getMessage());
        }

        return $result;
    }

    /**
     * @param null $billingId
     * @return string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getTelephone($billingId = null)
    {
        if ($billingId) {
            $address = $this->addressRepository->getById($billingId);
            return $address->getTelephone();
        }
        return null;
    }

    /**
     * @param $customerId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getFieldValues($customerId)
    {
        $fieldValues = [];
        $customAttributes = $this->customerRepositoryInterface->getById($customerId);
        $customAttributes->getCustomAttributes();
        if (!empty($customAttributes)) {
            foreach ($customAttributes as $attribute) {
                $attributeId = $this->eavAttribute->getIdByCode(\Magento\Customer\Model\Customer::ENTITY, $attribute->getAttributeCode());
                $attributeValues['field'] = $attributeId;
                $attributeValues['value'] = $attribute->getValue();
                $fieldValues[] = $attributeValues;
            }
        }
        return $fieldValues;
    }

    /**
     * Create AC contact
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function createAcContact(
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $ecomOrderArray = [];
        $ecomCustomerId = 0;
        $syncStatus = \ActiveCampaign\AbandonedCart\Model\Config\CronConfig::NOT_SYNCED;

        try {
            $customer = $this->customerRepositoryInterface->getById($order->getCustomerId());
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $customer = null;
        }

        $customerId = $customer ? $customer->getId() : $order->getCustomerId();
        $customerEmail = $customer ? $customer->getEmail() : $order->getBillingAddress()->getEmail();

        $contact = [
            'contact' => [
                'email'     => $customerEmail,
                'firstName' => $customer ? $customer->getFirstname() : $order->getBillingAddress()->getFirstname(),
                'lastName'  => $customer ? $customer->getLastname() : $order->getBillingAddress()->getLastname(),
                'phone'     => $customer
                    ? $this->getTelephone($customer->getDefaultBilling())
                    : $order->getBillingAddress()->getTelephone(),
                'fieldValues'   => $customer ? $this->getFieldValues((int)$customer->getId()) : []
            ]
        ];

        try {
            // Contact sync
            $contactResult = $this->curl->genericRequest(
                self::METHOD,
                self::CONTACT_ENDPOINT,
                $contact
            );

            $contactId = $contactResult['data']['contact']['id'] ?? null;

            $connectionId = $this->activeCampaignHelper->getConnectionId($customer->getStoreId());

            // Only create existing customer (no guests)
            if ($contactId && $customerId) {
                if (!$customer->getAcCustomerId()) {
                    $ecomCustomer['connectionid'] = $connectionId;
                    $ecomCustomer['externalid'] = $customerId;
                    $ecomCustomer['email'] = $customerEmail;
                    $ecomCustomerData['ecomCustomer'] = $ecomCustomer;
                    $AcCustomer = $this->curl->listAllCustomers(
                        self::GET_METHOD,
                        self::ECOM_CUSTOMERLIST_ENDPOINT,
                        $customerEmail
                    );
                    if (ISSET($AcCustomer['data']['ecomCustomers'][0])) {
                        foreach ($AcCustomer['data']['ecomCustomers'] as $Ac) {
                            if ($Ac['connectionid'] == $connectionId) {
                                $ecomCustomerId = $Ac['id'];
                            }
                        }
                    }
                    if (!$ecomCustomerId) {
                        $ecomCustomerResult = $this->curl->genericRequest(
                            self::METHOD,
                            self::ECOM_CUSTOMERLIST_ENDPOINT,
                            $ecomCustomerData
                        );
                        $ecomCustomerId = isset($ecomCustomerResult['data']['ecomCustomer']['id']) ? $ecomCustomerResult['data']['ecomCustomer']['id'] : null;
                    }
                } else {
                    $ecomCustomerId = $customer->getAcCustomerId();
                }
            }

            if ($ecomCustomerId !=  0) {
                $syncStatus = \ActiveCampaign\AbandonedCart\Model\Config\CronConfig::SYNCED;
            } else {
                $syncStatus = \ActiveCampaign\AbandonedCart\Model\Config\CronConfig::FAIL_SYNCED;
            }

            if ($customerId) {
                $this->saveCustomerResult($customerId, $syncStatus, $contactId, $ecomCustomerId);
            } else {
                $this->saveCustomerResultQuote($order, $ecomCustomerId);
            }
        } catch (\Exception $e) {
            $this->logger->critical("MODULE Order " . $e);
        }
    }

    /**
     * @param $customerId
     * @param $syncStatus
     * @param $contactId
     * @param $ecomCustomerId
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function saveCustomerResult($customerId, $syncStatus, $contactId, $ecomCustomerId)
    {
        $customerModel = $this->customerFactory->create();
        if ($customerId) {
            $this->customerResource->load($customerModel, $customerId);
        }

        $customerModel->setAcSyncStatus($syncStatus);

        $customerModel->setAcContactId($contactId);
        $customerModel->setAcCustomerId($ecomCustomerId);

        $this->customerResource->save($customerModel);
    }

    /**
     * @param $quote
     * @param $ecomCustomerId
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function saveCustomerResultQuote($quote, $ecomCustomerId)
    {
        if ($ecomCustomerId) {
            $quote->setData("ac_temp_customer_id", $ecomCustomerId);
            $quote->save();
        }
    }
}
