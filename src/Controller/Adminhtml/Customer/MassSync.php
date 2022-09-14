<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Controller\Adminhtml\Customer;

class MassSync extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'ActiveCampaign_Integration::customer_mass_sync';

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    private $filter;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \ActiveCampaign\Integration\Model\ResourceIterator
     */
    private $resourceIterator;

    /**
     * @var \ActiveCampaign\Integration\Model\Sync\Queue\Contact
     */
    private $syncQueue;

    /**
     * @var \ActiveCampaign\Integration\Helper\Customer
     */
    private $customerHelper;

    /**
     * Construct
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory
     * @param \ActiveCampaign\Integration\Model\ResourceIterator $resourceIterator
     * @param \ActiveCampaign\Integration\Model\Sync\Queue\Contact $syncQueue
     * @param \ActiveCampaign\Integration\Helper\Customer $customerHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        \ActiveCampaign\Integration\Model\ResourceIterator $resourceIterator,
        \ActiveCampaign\Integration\Model\Sync\Queue\Contact $syncQueue,
        \ActiveCampaign\Integration\Helper\Customer $customerHelper
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->resourceIterator = $resourceIterator;
        $this->syncQueue = $syncQueue;
        $this->customerHelper = $customerHelper;

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
        $resultRedirect->setPath('customer/index');

        if (!$this->customerHelper->isActive()) {
            $this->messageManager->addNoticeMessage(
                __('Customer syncing to ActiveCampaign is disabled.')
            );

            return $resultRedirect;
        }

        try {
            $iterator = $this->resourceIterator->walk(
                $collection->getSelect(),
                [[$this->syncQueue, 'iteratorCallback']]
            );

            $this->messageManager->addSuccessMessage(__(
                '%1 of %2 customers were scheduled to be synced to ActiveCampaign.',
                $iterator->processedCount - $iterator->errorCount,
                $iterator->processedCount
            ));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }

        return $resultRedirect;
    }
}
