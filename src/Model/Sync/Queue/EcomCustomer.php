<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\Sync\Queue;

class EcomCustomer extends AbstractQueue
{
    /**
     * @inheirtdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function iteratorCallback(array $args): void
    {
        try {
            if (empty($args['row']['entity_id'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Unable to retrieve customer.')
                );
            }

            if (empty($args['row']['store_id'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Unable to retrieve customer store ID.')
                );
            }

            $this->execute(
                (int)$args['row']['entity_id'],
                (int)$args['row']['store_id']
            );
        } catch (\Exception $e) {
            // Log exception and continue walk
            $this->helper->logger->critical($e);

            throw $e;
        }
    }

    /**
     * @inheirtdoc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(
        int $entityId,
        int $storeId,
        bool $remove = false
    ): void {
        try {
            $sync = $this->syncRepository->getByMageEntity(
                $entityId,
                \ActiveCampaign\Integration\Model\Source\MageEntityType::CUSTOMER,
                \ActiveCampaign\Integration\Model\Source\AcEntityType::ECOM_CUSTOMER,
                $storeId
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            if ($remove) {
                throw $e;
            }

            $sync = $this->syncModelFactory->create();
        }

        $sync
            ->setStoreId($storeId)
            ->setMageEntityId($entityId)
            ->setMageEntityType(\ActiveCampaign\Integration\Model\Source\MageEntityType::CUSTOMER)
            ->setAcEntityType(\ActiveCampaign\Integration\Model\Source\AcEntityType::ECOM_CUSTOMER)
            ->setStatus(\ActiveCampaign\Integration\Model\Source\SyncStatus::PENDING)
            ->setRemoved($remove)
        ;

        $this->syncRepository->save($sync);
    }
}
