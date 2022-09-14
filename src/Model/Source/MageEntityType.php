<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\Source;

class MageEntityType extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**#@+
     * Entity types
     */
    public const CUSTOMER = 'Customer';
    public const ORDER = 'Order';

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
                    'value' => self::CUSTOMER,
                    'label' => self::CUSTOMER
                ],
                [
                    'value' => self::ORDER,
                    'label' => self::ORDER
                ]
            ];

            if ($withEmpty) {
                array_unshift($this->_options, [
                    'value' => '',
                    'label' => __('--- Select Magento Entity Type ---')
                ]);
            }
        }

        return $this->_options;
    }
}
