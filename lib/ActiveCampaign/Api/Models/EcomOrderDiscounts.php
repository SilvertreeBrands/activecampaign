<?php
declare(strict_types=1);

namespace ActiveCampaign\Api\Models;

class EcomOrderDiscounts extends \ActiveCampaign\Api\Models\AbstractModel
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var int
     */
    private $discountAmount;

    /**
     * Set name
     *
     * @param string $name
     *
     * @return EcomOrderDiscounts
     */
    public function setName(string $name): EcomOrderDiscounts
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return EcomOrderDiscounts
     */
    public function setType(string $type): EcomOrderDiscounts
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Set discount amount
     *
     * @param int $discountAmount
     *
     * @return EcomOrderDiscounts
     */
    public function setDiscountAmount(int $discountAmount): EcomOrderDiscounts
    {
        $this->discountAmount = $discountAmount;
        return $this;
    }
}
