<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\Source;

class AcEntityType extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**#@+
     * Entity types
     */
    public const CONTACT = 'Contact';
    public const ECOM_CUSTOMER = 'EcomCustomer';
    public const ECOM_ORDER = 'EcomOrder';

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
                    'value' => self::CONTACT,
                    'label' => self::CONTACT
                ],
                [
                    'value' => self::ECOM_CUSTOMER,
                    'label' => self::ECOM_CUSTOMER
                ],
                [
                    'value' => self::ECOM_ORDER,
                    'label' => self::ECOM_ORDER
                ]
            ];

            if ($withEmpty) {
                array_unshift($this->_options, [
                    'value' => '',
                    'label' => __('--- Select ActiveCampaign Entity Type ---')
                ]);
            }
        }

        return $this->_options;
    }
}
