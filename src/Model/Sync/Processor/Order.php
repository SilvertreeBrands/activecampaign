<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\Sync\Processor;

class Order extends AbstractProcessor
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \ActiveCampaign\Integration\Helper\Api
     */
    protected $apiHelper;

    /**
     * @var \ActiveCampaign\Api\Models\EcomOrderFactory
     */
    protected $ecomOrderFactory;

    /**
     * @var \ActiveCampaign\Api\EcomOrders
     */
    protected $ecomOrdersApi;

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
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \ActiveCampaign\Integration\Helper\Api $apiHelper
     * @param \ActiveCampaign\Api\Models\EcomOrderFactory $ecomOrderFactory
     * @param \ActiveCampaign\Api\EcomOrders $ecomOrdersApi
     * @param \ActiveCampaign\Integration\Api\SyncRepositoryInterface $syncRepository
     * @param \ActiveCampaign\Integration\Api\Data\SyncInterfaceFactory $syncModelFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    /*public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \ActiveCampaign\Integration\Helper\Api $apiHelper,
        \ActiveCampaign\Api\Models\EcomOrderFactory $ecomOrderFactory,
        \ActiveCampaign\Api\EcomOrders $ecomOrdersApi,
        \ActiveCampaign\Integration\Api\SyncRepositoryInterface $syncRepository,
        \ActiveCampaign\Integration\Api\Data\SyncInterfaceFactory $syncModelFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        $this->customerRepository = $customerRepository;
        $this->apiHelper = $apiHelper;
        $this->ecomOrderFactory = $ecomOrderFactory;
        $this->ecomOrdersApi = $ecomOrdersApi;
        $this->syncRepository = $syncRepository;
        $this->syncModelFactory = $syncModelFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }*/

    /**
     * @inheirtdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function iteratorCallback(array $args): void
    {
        try {
            /*if (empty($args['row']['entity_id'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unable to retrieve order.'));
            }*/

            $t = 1;



            //$this->process((int)$args['row']['entity_id']);
        } catch (\Exception $e) {
            // Log exception and continue walk
            $this->apiHelper->logger->critical($e);

            throw $e;
        }
    }

    public function execute(\ActiveCampaign\Integration\Api\Data\SyncInterface $sync): void
    {

    }

    /**
     * @inheirtdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function process(
        int $entityId
    ): void {
        try {
            $sync = $this->syncRepository->getByMageEntity(
                \ActiveCampaign\Integration\Model\Source\AcEntityType::ECOM_ORDER,
                $entityId
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException) {
            $sync = $this->syncModelFactory->create();
        }

        $sync
            ->setStatus(\ActiveCampaign\Integration\Model\Source\SyncStatus::STATUS_PENDING)
            ->setEntityType(\ActiveCampaign\Integration\Model\Source\AcEntityType::ECOM_ORDER)
            ->setMageEntityId($entityId)
        ;

        $this->syncRepository->save($sync);
    }

    /**
     * Sync
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function sync(
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $connectionId = $this->apiHelper->getConnectionId($order->getStoreId());

        if (!$connectionId) {
            return $order;
        }



        if ($acSync->getId()
            && $acSync->getStatus() === \ActiveCampaign\Integration\Model\Source\SyncStatus::STATUS_COMPLETE
        ) {
            //@todo use entity exists exception that can be caught and a notice added separately
            throw new \Magento\Framework\Exception\LocalizedException(
                __('This order has already been synced to ActiveCampaign.')
            );
        }

        //@todo split in to separate function and return $acContactId or null
        $customerId = $order->getCustomerId();

        if ($customerId) {
            //@todo load customer and check if ac contact exists

            $customer = $this->acCustomer->syncContact(
                $this->customerRepository->getById($customerId)
            );
        } else {
            $acContactId = $this->acCustomer->syncContactFromOrder($order);
        }

        return $order;
    }
}
