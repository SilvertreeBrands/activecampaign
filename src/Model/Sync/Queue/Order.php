<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\Sync\Queue;

class Order extends AbstractQueue
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
                throw new \Magento\Framework\Exception\LocalizedException(__('Unable to retrieve order.'));
            }

            $this->prepare((int)$args['row']['entity_id']);
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
    public function prepare(
        int $entityId
    ): void {
        try {
            $sync = $this->syncRepository->getByMageEntity(
                \ActiveCampaign\Integration\Model\Source\AcEntityType::ECOM_ORDER,
                $entityId
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException) {
            $sync = $this->syncModelFactory->create();
        }

        $sync
            ->setStatus(\ActiveCampaign\Integration\Model\Source\SyncStatus::STATUS_PENDING)
            ->setEntityType(\ActiveCampaign\Integration\Model\Source\AcEntityType::ECOM_ORDER)
            ->setMageEntityId($entityId)
        ;

        $this->syncRepository->save($sync);
    }
}
