<?php
declare(strict_types=1);

namespace ActiveCampaign\Api\Models;

class EcomOrder extends \ActiveCampaign\Api\Models\AbstractModel
{
    /**
     * @var string
     */
    private $externalid;

    /**
     * @var string
     */
    private $externalcheckoutid;

    /**
     * @var int
     */
    private $source;

    /**
     * @var string
     */
    private $email;

    /**
     * @var EcomOrderProducts[]
     */
    private $orderProducts;

    /**
     * @var int
     */
    private $totalPrice;

    /**
     * @var int
     */
    private $shippingAmount;

    /**
     * @var int
     */
    private $taxAmount;

    /**
     * @var int
     */
    private $discountAmount;

    /**
     * @var string
     */
    private $currency;

    /**
     * @var int
     */
    private $connectionid;

    /**
     * @var int
     */
    private $customerid;

    /**
     * @var string
     */
    private $orderUrl;

    /**
     * @var string
     */
    private $externalCreatedDate;

    /**
     * @var string
     */
    private $externalUpdatedDate;

    /**
     * @var string
     */
    private $abandonedDate;

    /**
     * @var string
     */
    private $shippingMethod;

    /**
     * @var string
     */
    private $orderNumber;

    /**
     * @var EcomOrderDiscounts[]
     */
    private $orderDiscounts;

    /**
     * Set external ID
     *
     * @param string $externalId
     *
     * @return EcomOrder
     */
    public function setExternalId(string $externalId): EcomOrder
    {
        $this->externalid = $externalId;
        return $this;
    }

    /**
     * Set external checkout ID
     *
     * @param string $externalCheckoutId
     *
     * @return EcomOrder
     */
    public function setExternalCheckoutId(string $externalCheckoutId): EcomOrder
    {
        $this->externalcheckoutid = $externalCheckoutId;
        return $this;
    }

    /**
     * Set source
     *
     * @param int $source
     *
     * @return EcomOrder
     */
    public function setSource(int $source): EcomOrder
    {
        $this->source = $source;
        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return EcomOrder
     */
    public function setEmail(string $email): EcomOrder
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Set order products
     *
     * @param EcomOrderProducts[] $orderProducts
     *
     * @return EcomOrder
     */
    public function setOrderProducts(array $orderProducts): EcomOrder
    {
        $this->orderProducts = $orderProducts;
        return $this;
    }

    /**
     * Set total price
     *
     * @param int $totalPrice
     *
     * @return EcomOrder
     */
    public function setTotalPrice(int $totalPrice): EcomOrder
    {
        $this->totalPrice = $totalPrice;
        return $this;
    }

    /**
     * Set shipping amount
     *
     * @param int $shippingAmount
     *
     * @return EcomOrder
     */
    public function setShippingAmount(int $shippingAmount): EcomOrder
    {
        $this->shippingAmount = $shippingAmount;
        return $this;
    }

    /**
     * Set tax amount
     *
     * @param int $taxAmount
     *
     * @return EcomOrder
     */
    public function setTaxAmount(int $taxAmount): EcomOrder
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    /**
     * Set discount amount
     *
     * @param int $discountAmount
     *
     * @return EcomOrder
     */
    public function setDiscountAmount(int $discountAmount): EcomOrder
    {
        $this->discountAmount = $discountAmount;
        return $this;
    }

    /**
     * Set currency
     *
     * @param string $currency
     *
     * @return EcomOrder
     */
    public function setCurrency(string $currency): EcomOrder
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Set connection ID
     *
     * @param int $connectionId
     *
     * @return EcomOrder
     */
    public function setConnectionId(int $connectionId): EcomOrder
    {
        $this->connectionid = $connectionId;
        return $this;
    }

    /**
     * Set customer ID
     *
     * @param int $customerId
     *
     * @return EcomOrder
     */
    public function setCustomerId(int $customerId): EcomOrder
    {
        $this->customerid = $customerId;
        return $this;
    }

    /**
     * Set order URL
     *
     * @param string $orderUrl
     *
     * @return EcomOrder
     */
    public function setOrderUrl(string $orderUrl): EcomOrder
    {
        $this->orderUrl = $orderUrl;
        return $this;
    }

    /**
     * Set external created date
     *
     * @param string $externalCreatedDate
     *
     * @return EcomOrder
     */
    public function setExternalCreatedDate(string $externalCreatedDate): EcomOrder
    {
        $this->externalCreatedDate = $externalCreatedDate;
        return $this;
    }

    /**
     * Set external updated date
     *
     * @param string $externalUpdatedDate
     *
     * @return EcomOrder
     */
    public function setExternalUpdatedDate(string $externalUpdatedDate): EcomOrder
    {
        $this->externalUpdatedDate = $externalUpdatedDate;
        return $this;
    }

    /**
     * Set abandoned date
     *
     * @param string $abandonedDate
     *
     * @return EcomOrder
     */
    public function setAbandonedDate(string $abandonedDate): EcomOrder
    {
        $this->abandonedDate = $abandonedDate;
        return $this;
    }

    /**
     * Set shipping method
     *
     * @param string $shippingMethod
     *
     * @return EcomOrder
     */
    public function setShippingMethod(string $shippingMethod): EcomOrder
    {
        $this->shippingMethod = $shippingMethod;
        return $this;
    }

    /**
     * Set order number
     *
     * @param string $orderNumber
     *
     * @return EcomOrder
     */
    public function setOrderNumber(string $orderNumber): EcomOrder
    {
        $this->orderNumber = $orderNumber;
        return $this;
    }

    /**
     * Set order discounts
     *
     * @param EcomOrderDiscounts[] $orderDiscounts
     *
     * @return EcomOrder
     */
    public function setOrderDiscounts(array $orderDiscounts): EcomOrder
    {
        $this->orderDiscounts = $orderDiscounts;
        return $this;
    }
}
