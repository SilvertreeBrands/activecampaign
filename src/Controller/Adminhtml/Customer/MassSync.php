<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Controller\Adminhtml\Customer;

use Magento\Customer\Controller\Adminhtml\Index\AbstractMassAction;

class MassSync extends AbstractMassAction implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    /**
     * @var \ActiveCampaign\Integration\Model\ResourceIterator
     */
    protected $resourceIterator;

    /**
     * @var \ActiveCampaign\Integration\Model\Customer
     */
    protected $acCustomer;

    /**
     * @var \ActiveCampaign\Integration\Helper\Customer
     */
    protected $customerHelper;

    /**
     * Construct
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory
     * @param \ActiveCampaign\Integration\Model\ResourceIterator $resourceIterator
     * @param \ActiveCampaign\Integration\Model\Customer $acCustomer
     * @param \ActiveCampaign\Integration\Helper\Customer $customerHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        \ActiveCampaign\Integration\Model\ResourceIterator $resourceIterator,
        \ActiveCampaign\Integration\Model\Customer $acCustomer,
        \ActiveCampaign\Integration\Helper\Customer $customerHelper
    ) {
        $this->resourceIterator = $resourceIterator;
        $this->acCustomer = $acCustomer;
        $this->customerHelper = $customerHelper;

        parent::__construct($context, $filter, $collectionFactory);
    }

    /**
     * @inheritdoc
     */
    protected function massAction(
        \Magento\Eav\Model\Entity\Collection\AbstractCollection $collection
    ) {
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
                [[$this->acCustomer, 'iteratorCallback']]
            );

            $this->messageManager->addSuccessMessage(__(
                '%1 of %2 customers were synced to ActiveCampaign.',
                $iterator->processedCount - $iterator->errorCount,
                $iterator->processedCount
            ));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }

        return $resultRedirect;
    }
}
