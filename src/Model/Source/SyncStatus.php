<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\Source;

class SyncStatus extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**#@+
     * Statuses
     */
    public const STATUS_PENDING = 0;
    public const STATUS_COMPLETE = 1;
    public const STATUS_FAILED = 2;
    public const STATUS_UPDATE = 3;

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
                    'value' => self::STATUS_PENDING,
                    'label' => 'Pending'
                ],
                [
                    'value' => self::STATUS_COMPLETE,
                    'label' => 'Complete'
                ],
                [
                    'value' => self::STATUS_FAILED,
                    'label' => 'Failed'
                ],
                [
                    'value' => self::STATUS_UPDATE,
                    'label' => 'Update Pending'
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
