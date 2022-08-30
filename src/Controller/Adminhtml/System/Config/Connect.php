<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Controller\Adminhtml\System\Config;

class Connect extends \Magento\Backend\App\Action
{
    public const ADMIN_RESOURCE = 'ActiveCampaign_Integration::activecampaign_config';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \ActiveCampaign\Integration\Helper\Api
     */
    private $apiHelper;

    /**
     * @var \ActiveCampaign\Api\Connection
     */
    private $connectionApi;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * Construct
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \ActiveCampaign\Integration\Helper\Api $apiHelper
     * @param \ActiveCampaign\Api\Connection $connectionApi
     * @param \ActiveCampaign\Integration\Model\Customer $acCustomer
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \ActiveCampaign\Integration\Cron\SyncCustomer $cronSyncCustomer
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \ActiveCampaign\Integration\Helper\Api $apiHelper,
        \ActiveCampaign\Api\Connection $connectionApi,
        \ActiveCampaign\Integration\Model\Customer $acCustomer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \ActiveCampaign\Integration\Cron\SyncCustomer $cronSyncCustomer
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->apiHelper = $apiHelper;
        $this->connectionApi = $connectionApi;
        $this->urlBuilder = $context->getUrl();
        $this->acCustomer = $acCustomer;
        $this->customerRepository = $customerRepository;
        $this->cronSyncCustomer = $cronSyncCustomer;

        parent::__construct($context);
    }

    /**
     * Execute
     *
     * Add ecommerce connection
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $this->cronSyncCustomer->execute();

        $customer = $this->customerRepository->getById(2438657);
        $t = $this->acCustomer->syncContactFromCustomer($customer);
        throw new \Magento\Framework\Exception\LocalizedException(__('Testing'));
        $storeId = $this->getStoreId();
        $resultJson = $this->resultJsonFactory->create();

        try {
            $this->updateStoreApiConfig($storeId);

            $params = $this->getRequest()->getParams();

            if (empty($params['api_url'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid API URL specified.'));
            }

            if (empty($params['api_key'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid API key specified.'));
            }

            if (empty($params['connection_name'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid API connection name specified.')
                );
            }

            if ($storeId === 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('A connection can only be attempted within the store view scope.')
                );
            }

            $this->setStoreConnectionId(
                $params['api_key'],
                $params['api_url'],
                $params['connection_name'],
                $this->apiHelper->getStore($storeId)
            );
        } catch (\ActiveCampaign\Gateway\ResultException $re) {
            return $resultJson->setData([
                'success'       => false,
                'errorMessage'  => __($re->getMessage())
            ]);
        } catch(\Exception $e) {
            return $resultJson->setData([
                'success'       => false,
                'errorMessage'  => $e->getMessage()
            ]);
        }

        return $resultJson->setData([
            'success'       => true,
            'ajax_url'      => $this->urlBuilder->getUrl('activecampaign/system_config/disconnect'),
            'success_text'  => __('Connect'),
            'connection'    => false
        ]);
    }

    /**
     * Set store connection ID
     *
     * @param string $apiKey
     * @param string $apiUrl
     * @param string $connectionName
     * @param \Magento\Store\Api\Data\StoreInterface $store
     *
     * @return void
     * @throws \ActiveCampaign\Gateway\ResultException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function setStoreConnectionId(
        string $apiKey,
        string $apiUrl,
        string $connectionName,
        \Magento\Store\Api\Data\StoreInterface $store
    ) {
        // Set API config
        $this->connectionApi->setConfig(
            $apiKey,
            $apiUrl,
            isset($params['debug']) && (bool)$params['debug']
        );

        $connectionId = $this->getConnectionId($connectionName, $store);

        if (!$connectionId) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to create connection ID'));
        }

        $this->apiHelper->setStoreConfig(
            \ActiveCampaign\Integration\Helper\Api::CONFIG_CONNECTION_ID,
            $connectionId,
            (int)$store->getId()
        );

        $this->apiHelper->clearCache([
            \Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER,
            \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
        ]);
    }

    /**
     * Get connection ID
     *
     * @param string $connectionName
     * @param \Magento\Store\Api\Data\StoreInterface $store
     *
     * @return string
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    protected function getConnectionId(
        string $connectionName,
        \Magento\Store\Api\Data\StoreInterface $store
    ): string {
        $serviceName = $this->apiHelper->getServiceName($connectionName);

        // Fetch connection by service name
        $listResponse = $this->connectionApi->list([
            'filters[service]' => $serviceName
        ]);

        // Create connection if it does not exist
        if (empty($listResponse->result['connections'][0]['id'])) {
            $connectionModel = new \ActiveCampaign\Api\Models\Connection();

            $connectionModel
                ->setService($serviceName)
                ->setExternalId($store->getCode())
                ->setName($connectionName)
                ->setLogoUrl($this->apiHelper->getStoreLogoUrl($store))
                ->setLinkUrl($store->getBaseUrl())
            ;

            $createResponse = $this->connectionApi->create($connectionModel);

            if (!empty($createResponse->result['connection']['id'])) {
                return $createResponse->result['connection']['id'];
            }
        } else {
            return $listResponse->result['connections'][0]['id'];
        }

        return '';
    }

    /**
     * Update store API config
     *
     * @param int $storeId
     *
     * @return void
     */
    protected function updateStoreApiConfig(int $storeId)
    {
        $params = $this->getRequest()->getParams();

        if (isset($params[\ActiveCampaign\Integration\Helper\Api::CONFIG_ACTIVE])) {
            $this->apiHelper->setStoreConfig(
                \ActiveCampaign\Integration\Helper\Api::CONFIG_ACTIVE,
                $params[\ActiveCampaign\Integration\Helper\Api::CONFIG_ACTIVE],
                $storeId
            );
        }

        if (isset($params[\ActiveCampaign\Integration\Helper\Api::CONFIG_DEBUG])) {
            $this->apiHelper->setStoreConfig(
                \ActiveCampaign\Integration\Helper\Api::CONFIG_DEBUG,
                $params[\ActiveCampaign\Integration\Helper\Api::CONFIG_DEBUG],
                $storeId
            );
        }

        if (!empty($params[\ActiveCampaign\Integration\Helper\Api::CONFIG_API_URL])) {
            $this->apiHelper->setStoreConfig(
                \ActiveCampaign\Integration\Helper\Api::CONFIG_API_URL,
                $params[\ActiveCampaign\Integration\Helper\Api::CONFIG_API_URL],
                $storeId
            );
        }

        if (!empty($params[\ActiveCampaign\Integration\Helper\Api::CONFIG_API_KEY])) {
            $this->apiHelper->setStoreConfig(
                \ActiveCampaign\Integration\Helper\Api::CONFIG_API_KEY,
                $params[\ActiveCampaign\Integration\Helper\Api::CONFIG_API_KEY],
                $storeId
            );
        }

        if (!empty($params[\ActiveCampaign\Integration\Helper\Api::CONFIG_CONNECTION_NAME])) {
            $this->apiHelper->setStoreConfig(
                \ActiveCampaign\Integration\Helper\Api::CONFIG_CONNECTION_NAME,
                $params[\ActiveCampaign\Integration\Helper\Api::CONFIG_CONNECTION_NAME],
                $storeId
            );
        }

        $this->apiHelper->clearCache([
            \Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER,
            \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
        ]);
    }

    /**
     * Get store ID
     *
     * @return int
     */
    protected function getStoreId()
    {
        return (int)$this->getRequest()->getParam('store') ?? (int)$this->storeManager->getDefaultStoreView()->getId();
    }
}
