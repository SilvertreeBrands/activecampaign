<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\Sync\Processor;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EcomCustomer extends AbstractProcessor
{
    /**
     * @var \ActiveCampaign\Api\Models\EcomCustomerFactory
     */
    protected $ecomCustomerFactory;

    /**
     * @var \ActiveCampaign\Api\EcomCustomers
     */
    protected $ecomCustomersApi;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Construct
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \ActiveCampaign\Integration\Helper\Api $apiHelper
     * @param \ActiveCampaign\Integration\Api\SyncRepositoryInterface $syncRepository
     * @param \ActiveCampaign\Integration\Api\Data\SyncInterfaceFactory $syncModelFactory
     * @param \ActiveCampaign\Api\Models\EcomCustomerFactory $ecomCustomerFactory
     * @param \ActiveCampaign\Api\EcomCustomers $ecomCustomersApi
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
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
        \ActiveCampaign\Api\Models\EcomCustomerFactory $ecomCustomerFactory,
        \ActiveCampaign\Api\EcomCustomers $ecomCustomersApi,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->ecomCustomerFactory = $ecomCustomerFactory;
        $this->ecomCustomersApi = $ecomCustomersApi;
        $this->customerRepository = $customerRepository;

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
     * @throws \Exception
     */
    public function execute(\ActiveCampaign\Integration\Api\Data\SyncInterface $sync): void
    {
        $connectionId = $this->apiHelper->getConnectionId($sync->getStoreId());

        if (!$connectionId
            || !$sync->getMageEntityId()
            || $sync->getMageEntityType() !== \ActiveCampaign\Integration\Model\Source\MageEntityType::CUSTOMER
            || $sync->getAcEntityType() !== \ActiveCampaign\Integration\Model\Source\AcEntityType::ECOM_CUSTOMER
        ) {
            return;
        }

        try {
            // @Todo cater for deletion
            $customer = $this->customerRepository->getById($sync->getMageEntityId());

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

            if ($sync->getAcEntityId()) {
                $acEntityId = $this->updateEcomCustomer($ecomCustomer, $sync->getAcEntityId());
            } else {
                $acEntityId = $this->createEcomCustomer($ecomCustomer);
            }

            $sync
                ->setAcEntityId($acEntityId)
                ->setStatus(\ActiveCampaign\Integration\Model\Source\SyncStatus::COMPLETE)
            ;

            $this->syncRepository->save($sync);
        } catch (\Exception $e) {
            $sync
                ->setStatus(\ActiveCampaign\Integration\Model\Source\SyncStatus::FAILED)
            ;

            $this->syncRepository->save($sync);

            throw $e;
        }
    }

    /**
     * Update ecom customer
     *
     * @param \ActiveCampaign\Api\Models\EcomCustomer $ecomCustomer
     * @param int $acEntityId
     *
     * @return int
     * @throws \ActiveCampaign\Gateway\ResultException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateEcomCustomer(
        \ActiveCampaign\Api\Models\EcomCustomer $ecomCustomer,
        int $acEntityId
    ) {
        $response = $this->ecomCustomersApi->update(
            $acEntityId,
            $ecomCustomer
        );

        if ($response->status === 404) {
            return $this->createEcomCustomer($ecomCustomer);
        }

        if (empty($response->result['ecomCustomer']['id'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to retrieve ecomCustomer ID from result')
            );
        }

        return (int)$response->result['ecomCustomer']['id'];
    }

    /**
     * Create ecom customer
     *
     * First checks if the ecom customer exists, else it will create.
     *
     * @param \ActiveCampaign\Api\Models\EcomCustomer $ecomCustomer
     *
     * @return int
     * @throws \ActiveCampaign\Gateway\ResultException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createEcomCustomer(
        \ActiveCampaign\Api\Models\EcomCustomer $ecomCustomer
    ) {
        // Search for ecom customer
        $searchResponse = $this->ecomCustomersApi->list([
            'filters[email]'        => urlencode($ecomCustomer->getEmail()),
            'filters[externalid]'   => $ecomCustomer->getExternalId(),
            'filters[connectionid]' => $ecomCustomer->getConnectionId()
        ]);

        if (!empty($searchResponse->result['ecomCustomers'][0]['id'])) {
            return (int)$searchResponse->result['ecomCustomers'][0]['id'];
        }

        // Create ecom customer
        $response = $this->ecomCustomersApi->create($ecomCustomer);

        if (empty($response->result['ecomCustomer']['id'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to retrieve ecomCustomer ID from result')
            );
        }

        return (int)$response->result['ecomCustomer']['id'];
    }
}
