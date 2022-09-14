<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Plugin;

class CustomerRepositoryPlugin
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
     * After save
     *
     * Check if customer has updates, then sync to ActiveCampaign.
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $subject
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface $customer
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \Magento\Customer\Api\CustomerRepositoryInterface $subject,
        \Magento\Customer\Api\Data\CustomerInterface $customer,
    ) {
        try {
            if ($customer->getId()
                && $this->customerHelper->isActive($customer->getStoreId())
            ) {
                $original = $subject->getById($customer->getId());

                if ($original->getEmail() !== $customer->getEmail()) {
                    $this->syncQueueContact->execute($customer->getId(), $customer->getStoreId());
                }
            }
        } catch (\Exception $e) {
            $this->customerHelper->logger->critical($e);
        }

        return $customer;
    }

    /**
     * Before delete by ID
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $subject
     * @param int $customerId
     *
     * @return null
     */
    public function beforeDeleteById(
        \Magento\Customer\Api\CustomerRepositoryInterface $subject,
        int $customerId
    ) {
        try {
            $customer = $subject->getById($customerId);

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
