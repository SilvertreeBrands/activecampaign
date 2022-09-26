<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\Source;

class SyncStatus extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**#@+
     * Statuses
     */
    public const PENDING = 0;
    public const COMPLETE = 1;
    public const FAILED = 2;

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
                    'value' => null,
                    'label' => 'na'
                ],
                [
                    'value' => self::PENDING,
                    'label' => 'Pending'
                ],
                [
                    'value' => self::COMPLETE,
                    'label' => 'Complete'
                ],
                [
                    'value' => self::FAILED,
                    'label' => 'Failed'
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
