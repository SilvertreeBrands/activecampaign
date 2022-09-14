<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Controller\Adminhtml\Order;

class MassSync extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'ActiveCampaign_Integration::order_mass_sync';

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    private $filter;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \ActiveCampaign\Integration\Model\ResourceIterator
     */
    private $resourceIterator;

    /**
     * @var \ActiveCampaign\Integration\Model\Sync\Prepare\Order
     */
    private $syncPrepareOrder;

    /**
     * @var \ActiveCampaign\Integration\Helper\Order
     */
    private $orderHelper;

    /**
     * Construct
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \ActiveCampaign\Integration\Model\ResourceIterator $resourceIterator
     * @param \ActiveCampaign\Integration\Model\Sync\Prepare\Order $syncPrepareOrder
     * @param \ActiveCampaign\Integration\Helper\Order $orderHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \ActiveCampaign\Integration\Model\ResourceIterator $resourceIterator,
        \ActiveCampaign\Integration\Model\Sync\Prepare\Order $syncPrepareOrder,
        \ActiveCampaign\Integration\Helper\Order $orderHelper
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->resourceIterator = $resourceIterator;
        $this->syncPrepareOrder = $syncPrepareOrder;
        $this->orderHelper = $orderHelper;

        parent::__construct($context);
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('sales/order');

        if ($this->orderHelper->isActive()) {
            $this->messageManager->addNoticeMessage(
                __('Order syncing to ActiveCampaign is disabled.')
            );

            return $resultRedirect;
        }

        try {
            $iterator = $this->resourceIterator->walk(
                $collection->getSelect(),
                [[$this->syncPrepareOrder, 'iteratorCallback']]
            );

            $this->messageManager->addSuccessMessage(__(
                '%1 of %2 orders were scheduled to be synced to ActiveCampaign.',
                $iterator->processedCount - $iterator->errorCount,
                $iterator->processedCount
            ));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }

        return $resultRedirect;
    }
}
