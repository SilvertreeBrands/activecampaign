<?php
declare(strict_types=1);

namespace ActiveCampaign\Api\Models;

class EcomOrderProducts extends \ActiveCampaign\Api\Models\AbstractModel
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $price;

    /**
     * @var int
     */
    protected $quantity;

    /**
     * @var string
     */
    protected $externalid;

    /**
     * @var string
     */
    protected $category;

    /**
     * @var string
     */
    protected $sku;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $imageUrl;

    /**
     * @var string
     */
    protected $productUrl;

    /**
     * Set name
     *
     * @param string $name
     *
     * @return EcomOrderProducts
     */
    public function setName(string $name): EcomOrderProducts
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set price
     *
     * @param int $price
     *
     * @return EcomOrderProducts
     */
    public function setPrice(int $price): EcomOrderProducts
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Set quantity
     *
     * @param int $quantity
     *
     * @return EcomOrderProducts
     */
    public function setQuantity(int $quantity): EcomOrderProducts
    {
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Set external ID
     *
     * @param string $externalId
     *
     * @return EcomOrderProducts
     */
    public function setExternalId(string $externalId): EcomOrderProducts
    {
        $this->externalid = $externalId;
        return $this;
    }

    /**
     * Set category
     *
     * @param string $category
     *
     * @return EcomOrderProducts
     */
    public function setCategory(string $category): EcomOrderProducts
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Set SKU
     *
     * @param string $sku
     *
     * @return EcomOrderProducts
     */
    public function setSku(string $sku): EcomOrderProducts
    {
        $this->sku = $sku;
        return $this;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return EcomOrderProducts
     */
    public function setDescription(string $description): EcomOrderProducts
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Set image URL
     *
     * @param string $imageUrl
     *
     * @return EcomOrderProducts
     */
    public function setImageUrl(string $imageUrl): EcomOrderProducts
    {
        $this->imageUrl = $imageUrl;
        return $this;
    }

    /**
     * Set product URL
     *
     * @param string $productUrl
     *
     * @return EcomOrderProducts
     */
    public function setProductUrl(string $productUrl): EcomOrderProducts
    {
        $this->productUrl = $productUrl;
        return $this;
    }
}
