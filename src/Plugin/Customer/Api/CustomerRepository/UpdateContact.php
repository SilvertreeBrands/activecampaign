<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Plugin\Customer\Api\CustomerRepository;

class UpdateContact
{
    /**
     * @var \ActiveCampaign\Integration\Helper\Customer
     */
    private $acCustomerHelper;

    /**
     * @var \ActiveCampaign\Integration\Model\Customer
     */
    private $acCustomer;

    /**
     * Construct
     *
     * @param \ActiveCampaign\Integration\Helper\Customer $acCustomerHelper
     * @param \ActiveCampaign\Integration\Model\Customer $acCustomer
     */
    public function __construct(
        \ActiveCampaign\Integration\Helper\Customer $acCustomerHelper,
        \ActiveCampaign\Integration\Model\Customer $acCustomer
    ) {
        $this->acCustomerHelper = $acCustomerHelper;
        $this->acCustomer = $acCustomer;
    }

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
        try {
            if ($customer->getId()
                && $this->acCustomerHelper->isActive($customer->getStoreId())
            ) {
                $original = $subject->getById($customer->getId());

                if ($original->getEmail() !== $customer->getEmail()) {
                    $customer->setCustomAttribute(
                        \ActiveCampaign\Integration\Model\CustomerAttribute::AC_SYNC_STATUS,
                        \ActiveCampaign\Integration\Model\Source\SyncStatus::STATUS_UPDATE
                    );
                }
            }
        } catch (\Exception $e) {
            $this->acCustomerHelper->logger->critical($e);
        }
    }

    /**
     * Before delete by ID
     *
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $subject
     * @param int $customerId
     *
     * @return void
     */
    public function beforeDeleteById(
        \Magento\Customer\Api\CustomerRepositoryInterface $subject,
        int $customerId
    ): void {
        try {
            $customer = $subject->getById($customerId);

            if ($this->acCustomerHelper->isActive($customer->getStoreId())) {
                $this->acCustomer->deleteContact($customer);
            }
        } catch (\Exception $e) {
            $this->acCustomerHelper->logger->critical($e);
        }
    }
}
