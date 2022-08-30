<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Plugin\Customer\Api\CustomerRepository;

class CheckForUpdates
{
    /**
     * Before save
     *
     * Check if customer has updates, then sync to ActiveCampaign.
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $subject
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @param string|null $passwordHash
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(
        \Magento\Customer\Api\CustomerRepositoryInterface $subject,
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        string $passwordHash = null
    ): void {
        if ($customer->getId()) {
            $original = $subject->getById($customer->getId());

            if ($original->getEmail() !== $customer->getEmail()) {
                // perform sync
            }
        }
    }
}
