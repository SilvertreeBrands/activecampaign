<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Plugin\Sales;

class OrderGridPlugin
{
    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $subject
     * @return null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeLoad(
        \Magento\Sales\Model\ResourceModel\Order\Grid\Collection $subject
    ) {
        if (!$subject->isLoaded()) {
            $primaryKey = $subject->getResource()->getIdFieldName();
            $tableName = $subject->getResource()->getTable(
                \ActiveCampaign\Integration\Model\ResourceModel\Sync::TABLE_NAME
            );

            $subject->getSelect()->joinLeft(
                $tableName,
                sprintf(
                    '%1$s.entity_type = \'%2$s\' AND %1$s.mage_entity_id = main_table.%3$s',
                    $tableName,
                    \ActiveCampaign\Integration\Model\Source\AcEntityType::ECOM_ORDER,
                    $primaryKey
                ),
                [
                    'ac_sync_status' => $tableName . '.status'
                ]
            );
        }

        return null;
    }
}
