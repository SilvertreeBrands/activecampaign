<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Plugin\Sales;

class OrderRepositoryPlugin
{
    /**
     * @var \ActiveCampaign\Integration\Model\ExtensionAttributes\OrderHandler
     */
    private $extensionAttributesHandler;

    /**
     * @var \ActiveCampaign\Integration\Logger\Logger
     */
    public $logger;

    /**
     * Construct
     *
     * @param \ActiveCampaign\Integration\Model\ExtensionAttributes\OrderHandler $extensionAttributesHandler
     * @param \ActiveCampaign\Integration\Logger\Logger $logger
     */
    public function __construct(
        \ActiveCampaign\Integration\Model\ExtensionAttributes\OrderHandler $extensionAttributesHandler,
        \ActiveCampaign\Integration\Logger\Logger $logger
    ) {
        $this->extensionAttributesHandler = $extensionAttributesHandler;
        $this->logger = $logger;
    }

    /**
     * After get
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $order
    ): \Magento\Sales\Api\Data\OrderInterface {
        try {
            $this->extensionAttributesHandler->load($order);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $order;
    }

    /**
     * After get list
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Framework\Api\SearchResultsInterface $searchResult
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Framework\Api\SearchResultsInterface $searchResult
    ): \Magento\Framework\Api\SearchResultsInterface {
        try {
            foreach ($searchResult->getItems() as $order) {
                $this->extensionAttributesHandler->load($order);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $searchResult;
    }

    /**
     * After save
     *
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $order
    ): \Magento\Sales\Api\Data\OrderInterface
    {
        try {
            $this->extensionAttributesHandler->load($order);

            $acSync = $order->getExtensionAttributes()->getAcSync();

            $acSync
                ->setEntityType(\ActiveCampaign\Integration\Model\Source\AcEntityType::ECOM_ORDER)
                ->setMageEntityId((int)$order->getId())
                ->setStatus(\ActiveCampaign\Integration\Model\Source\SyncStatus::STATUS_PENDING)
            ;

            $this->extensionAttributesHandler->save($order);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $order;
    }
}
