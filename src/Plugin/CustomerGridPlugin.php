<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Plugin;

class CustomerGridPlugin
{
    /**
     * Add filter to map for sorting and filtering
     *
     * @param \Magento\Customer\Model\ResourceModel\Grid\Collection $subject
     *
     * @return null
     */
    public function beforeGetSelect(
        \Magento\Customer\Model\ResourceModel\Grid\Collection $subject
    ) {
        /**
         * I don't like adding the filter maps here since this method gets called several times. The best place to add
         * the filter maps is after _initSelect method, however the method itself cannot be intercepted because it is
         * protected. I was hoping there was a better way to achieve this
         */
        $tableName = $subject->getResource()->getTable('activecampaign_sync');

        // Required for sorting and filtering
        $subject->addFilterToMap('main_table.ac_contact_id', sprintf('%1$s.ac_entity_id', $tableName));
        $subject->addFilterToMap('main_table.ac_contact_sync_status', sprintf('%1$s.status', $tableName));

        return null;
    }

    /**
     * Add ac sync columns
     *
     * @param \Magento\Customer\Model\ResourceModel\Grid\Collection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeLoad(
        \Magento\Customer\Model\ResourceModel\Grid\Collection $subject,
        bool $printQuery = false,
        bool $logQuery = false
    ) {
        $tableName = $subject->getResource()->getTable('activecampaign_sync');

        if (!$subject->isLoaded()) {
            $primaryKey = $subject->getResource()->getIdFieldName();

            $subject->getSelect()->joinLeft(
                $tableName,
                sprintf(
                    '%1$s.mage_entity_id = main_table.%2$s AND %1$s.ac_entity_type = \'Contact\'',
                    $tableName,
                    $primaryKey
                ),
                [
                    'ac_contact_id'             => sprintf('%1$s.ac_entity_id', $tableName),
                    'ac_contact_sync_status'    => sprintf('%1$s.status', $tableName),
                ]
            );
        }

        $subject->addFilterToMap('main_table.ac_contact_id', sprintf('%1$s.ac_entity_id', $tableName));
        $subject->addFilterToMap('main_table.ac_contact_sync_status', sprintf('%1$s.status', $tableName));


        return [$printQuery, $logQuery];
    }
}
