<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Controller\Adminhtml\System\Config;

class Disconnect extends \Magento\Backend\App\Action
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
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \ActiveCampaign\Integration\Helper\Api $apiHelper,
        \ActiveCampaign\Api\Connection $connectionApi
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->storeManager = $storeManager;
        $this->apiHelper = $apiHelper;
        $this->connectionApi = $connectionApi;
        $this->urlBuilder = $context->getUrl();

        parent::__construct($context);
    }

    /**
     * Execute
     *
     * Delete ecommerce connection
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $storeId = $this->getStoreId();
        $resultJson = $this->resultJsonFactory->create();

        try {
            $connectionId = (int)$this->apiHelper->getConnectionId($storeId);
            $params = $this->getRequest()->getParams();

            if (!$connectionId) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Unable to disconnect. Invalid connection ID')
                );
            }

            if (empty($params['api_url'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid API URL specified.'));
            }

            if (empty($params['api_key'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid API key specified.'));
            }

            // Delete connection
            $this->connectionApi->setConfig(
                $params['api_key'],
                $params['api_url'],
                isset($params['debug']) && (bool)$params['debug']
            )->delete($connectionId);

            try {
                $this->apiHelper->deleteStoreConfig(
                    \ActiveCampaign\Integration\Helper\Api::CONFIG_CONNECTION_ID,
                    $storeId
                );
                $this->apiHelper->clearCache([
                    \Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER,
                    \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER
                ]);
            } catch (\Exception $e) {
                // Allow failure
                $this->apiHelper->logger->critical($e);
            }
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
            'ajax_url'      => $this->urlBuilder->getUrl('activecampaign/system_config/connect'),
            'success_text'  => __('Disconnect'),
            'connection'    => true
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
