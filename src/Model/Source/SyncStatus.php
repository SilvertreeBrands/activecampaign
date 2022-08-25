<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\Source;

class SyncStatus extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**#@+
     * Statuses
     */
    public const STATUS_NOT_COMPLETE = 0;
    public const STATUS_COMPLETE = 1;
    public const STATUS_FAILED = 2;

    /**
     * @inheritdoc
     */
    public function getAllOptions(
        $withEmpty = true,
        $defaultValues = false
    ): ?array {
        if (!$this->_options) {
            $this->_options = [
                [
                    'value' => self::STATUS_NOT_COMPLETE,
                    'label' => 'Not Synced'
                ],
                [
                    'value' => self::STATUS_COMPLETE,
                    'label' => 'Synced'
                ],
                [
                    'value' => self::STATUS_FAILED,
                    'label' => 'Sync Failed'
                ]
            ];

            if ($withEmpty) {
                array_unshift($this->_options, [
                    'value' => '',
                    'label' => __('--- Select Sync Status ---')
                ]);
            }
        }

        return $this->_options;
    }
}
