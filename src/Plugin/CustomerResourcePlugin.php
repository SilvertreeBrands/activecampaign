<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Plugin;

class CustomerResourcePlugin
{
    /**
     * @var \ActiveCampaign\Integration\Helper\Customer
     */
    private $customerHelper;

    /**
     * @var \ActiveCampaign\Integration\Model\Sync\Queue\Contact
     */
    private $syncQueueContact;

    /**
     * Construct
     *
     * @param \ActiveCampaign\Integration\Helper\Customer $customerHelper
     * @param \ActiveCampaign\Integration\Model\Sync\Queue\Contact $syncQueueContact
     */
    public function __construct(
        \ActiveCampaign\Integration\Helper\Customer $customerHelper,
        \ActiveCampaign\Integration\Model\Sync\Queue\Contact $syncQueueContact
    ) {
        $this->customerHelper = $customerHelper;
        $this->syncQueueContact = $syncQueueContact;
    }

    /**
     * Around save
     *
     * Queue contact sync if object is new or email was updated.
     *
     * @param \Magento\Customer\Model\ResourceModel\Customer $subject
     * @param callable $proceed
     * @param \Magento\Customer\Model\Customer $customer
     *
     * @return \Magento\Customer\Model\ResourceModel\Customer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        \Magento\Customer\Model\ResourceModel\Customer $subject,
        callable $proceed,
        \Magento\Customer\Model\Customer $customer
    ) {
        $syncRequired = $customer->isObjectNew() || $customer->dataHasChangedFor('email');

        $result = $proceed($customer);

        if ($syncRequired) {
            try {
                $this->syncQueueContact->execute((int)$customer->getId(), (int)$customer->getStoreId());
            } catch (\Exception $e) {
                $this->customerHelper->logger->critical($e);
            }
        }

        return $result;
    }

    /**
     * Before delete
     *
     * @param \Magento\Customer\Model\ResourceModel\Customer $subject
     * @param \Magento\Customer\Model\Customer $customer
     *
     * @return null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDelete(
        \Magento\Customer\Model\ResourceModel\Customer $subject,
        \Magento\Customer\Model\Customer $customer
    ) {
        try {
            if ($customer->getId()
                && $this->customerHelper->isActive($customer->getStoreId())
            ) {
                $this->syncQueueContact->execute(
                    $customer->getId(),
                    $customer->getStoreId(),
                    true
                );
            }
        } catch (\Exception $e) {
            $this->customerHelper->logger->critical($e);
        }

        return null;
    }
}
