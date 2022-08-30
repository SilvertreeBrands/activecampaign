<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model;

class CustomerAttribute
{
    /**#@+
     * Non-scalar custom attribute setup as static customer entity columns
     */
    public const AC_CONTACT_ID = 'ac_contact_id';
    public const AC_CUSTOMER_ID = 'ac_customer_id';
    public const AC_SYNC_STATUS = 'ac_sync_status';

    /**
     * Get attributes
     *
     * @return array
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getAttributes(): array
    {
        return [
            self::AC_CONTACT_ID => [
                'attribute' => [
                    'type'                  => 'static',
                    'label'                 => 'ActiveCampaign Contact Id',
                    'input'                 => 'hidden',
                    'validate_rules'        => '',
                    'position'              => 1000,
                    'user_defined'          => false,
                    'required'              => false,
                    'system'                => false,
                    'visible'               => false,
                    'unique'                => 0,
                    'default'               => null,
                    'note'                  => '',
                    'is_used_in_grid'       => true,
                    'is_visible_in_grid'    => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true
                ],
                'forms'     => [
                    'adminhtml_customer'
                ]
            ],
            self::AC_CUSTOMER_ID => [
                'attribute' => [
                    'type'                  => 'static',
                    'label'                 => 'ActiveCampaign Customer Id',
                    'input'                 => 'hidden',
                    'validate_rules'        => '',
                    'position'              => 1001,
                    'user_defined'          => false,
                    'required'              => false,
                    'system'                => false,
                    'visible'               => false,
                    'unique'                => 0,
                    'default'               => null,
                    'note'                  => '',
                    'is_used_in_grid'       => true,
                    'is_visible_in_grid'    => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true
                ],
                'forms'     => [
                    'adminhtml_customer'
                ]
            ],
            self::AC_SYNC_STATUS => [
                'attribute' => [
                    'type'                  => 'static',
                    'label'                 => 'ActiveCampaign Sync Status',
                    'input'                 => 'hidden',
                    'validate_rules'        => '',
                    'position'              => 1002,
                    'user_defined'          => false,
                    'required'              => false,
                    'system'                => false,
                    'visible'               => false,
                    'unique'                => 0,
                    'default'               => null,
                    'note'                  => '',
                    'is_used_in_grid'       => true,
                    'is_visible_in_grid'    => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true
                ],
                'forms'     => [
                    'adminhtml_customer'
                ]
            ]
        ];
    }
}
