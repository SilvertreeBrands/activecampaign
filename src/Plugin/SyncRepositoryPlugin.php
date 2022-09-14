<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Plugin;

class SyncRepositoryPlugin
{
    /**
     * @var \ActiveCampaign\Integration\Helper\Customer
     */
    private $customerHelper;

    /**
     * @var \ActiveCampaign\Integration\Model\Sync\Queue\EcomCustomer
     */
    private $syncQueueEcomCustomer;

    /**
     * Construct
     *
     * @param \ActiveCampaign\Integration\Helper\Customer $customerHelper
     * @param \ActiveCampaign\Integration\Model\Sync\Queue\EcomCustomer $syncQueueEcomCustomer
     */
    public function __construct(
        \ActiveCampaign\Integration\Helper\Customer $customerHelper,
        \ActiveCampaign\Integration\Model\Sync\Queue\EcomCustomer $syncQueueEcomCustomer
    ) {
        $this->customerHelper = $customerHelper;
        $this->syncQueueEcomCustomer = $syncQueueEcomCustomer;
    }

    /**
     * After save
     *
     * @param \ActiveCampaign\Integration\Api\SyncRepositoryInterface $subject
     * @param \ActiveCampaign\Integration\Api\Data\SyncInterface $sync
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        \ActiveCampaign\Integration\Api\SyncRepositoryInterface $subject,
        \ActiveCampaign\Integration\Api\Data\SyncInterface $sync
    ): void {
        try {
            if ($sync->getId()
                && $sync->getAcEntityId()
                && $sync->getAcEntityType() === \ActiveCampaign\Integration\Model\Source\AcEntityType::CONTACT
                && $this->customerHelper->isActive($sync->getStoreId())
            ) {
                $this->syncQueueEcomCustomer->execute($sync->getMageEntityId(), $sync->getStoreId());
            }
        } catch (\Exception $e) {
            $this->customerHelper->logger->critical($e);
        }
    }
}
