<?php
declare(strict_types=1);

namespace ActiveCampaign\Integration\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class AddCustomerAttributes implements DataPatchInterface, PatchRevertableInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute
     */
    protected $attributeResource;

    /**
     * @var \ActiveCampaign\Integration\Model\CustomerAttribute
     */
    protected $customerAttribute;

    /**
     * Construct
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory
     * @param \Magento\Customer\Model\ResourceModel\Attribute $attributeResource
     * @param \ActiveCampaign\Integration\Model\CustomerAttribute $customerAttribute
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        \Magento\Customer\Setup\CustomerSetupFactory $customerSetupFactory,
        \Magento\Customer\Model\ResourceModel\Attribute $attributeResource,
        \ActiveCampaign\Integration\Model\CustomerAttribute $customerAttribute,
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->customerAttribute = $customerAttribute;
        $this->attributeResource = $attributeResource;
    }

    /**
     * @inheritdoc
     * @throws \Magento\Framework\Exception\LocalizedException|\Zend_Validate_Exception
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributes = $this->customerAttribute->getAttributes();

        $installAttributes = [
            \ActiveCampaign\Integration\Model\CustomerAttribute::AC_CONTACT_ID,
            \ActiveCampaign\Integration\Model\CustomerAttribute::AC_CUSTOMER_ID,
            \ActiveCampaign\Integration\Model\CustomerAttribute::AC_SYNC_STATUS
        ];

        foreach ($installAttributes as $attributeCode) {
            if (!empty($attributes[$attributeCode]['attribute'])) {
                $customerSetup->addAttribute(
                    \Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                    $attributeCode,
                    $attributes[$attributeCode]['attribute']
                );

                $attribute = $customerSetup->getEavConfig()->getAttribute(
                    \Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                    $attributeCode
                );

                if (!empty($attributes[$attributeCode]['forms'])) {
                    $attribute->setData('used_in_forms', $attributes[$attributeCode]['forms']);
                }

                $this->attributeResource->save($attribute);
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $removeAttributes = [
            \ActiveCampaign\Integration\Model\CustomerAttribute::AC_CONTACT_ID,
            \ActiveCampaign\Integration\Model\CustomerAttribute::AC_CUSTOMER_ID,
            \ActiveCampaign\Integration\Model\CustomerAttribute::AC_SYNC_STATUS
        ];

        foreach ($removeAttributes as $removeAttribute) {
            $customerSetup->removeAttribute(\Magento\Customer\Model\Customer::ENTITY, $removeAttribute);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
