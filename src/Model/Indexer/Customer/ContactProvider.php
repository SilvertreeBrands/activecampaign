<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Model\Indexer\Customer;

class ContactProvider implements \Magento\Framework\Indexer\FieldsetInterface
{
    /**
     * @inheirtdoc
     */
    public function addDynamicData(array $data)
    {
        if (!empty($data['name'])
            && isset($data['references']['customer'])
        ) {
            $data['references']['customer']['to'] = sprintf(
                '%1$s AND %2$s.mage_entity_type = \'%3$s\' AND %2$s.ac_entity_type = \'%4$s\'',
                $data['references']['customer']['to'],
                $data['name'],
                \ActiveCampaign\Integration\Model\Source\MageEntityType::CUSTOMER,
                \ActiveCampaign\Integration\Model\Source\AcEntityType::CONTACT
            );
        }

        return $data;
    }
}
