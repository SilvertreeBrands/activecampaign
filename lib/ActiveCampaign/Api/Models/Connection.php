<?php
declare(strict_types=1);

namespace ActiveCampaign\Api\Models;

class Connection extends \ActiveCampaign\Api\Models\AbstractModel
{
    /**
     * @var string
     */
    private $service;

    /**
     * @var string
     */
    private $externalid;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $logoUrl;

    /**
     * @var string
     */
    private $linkUrl;

    /**
     * @var int
     */
    private $status;

    /**
     * @var int
     */
    private $syncStatus;

    /**
     * Set service
     *
     * @param string $service
     *
     * @return Connection
     */
    public function setService(string $service): Connection
    {
        $this->service = $service;
        return $this;
    }

    /**
     * Set external ID
     *
     * @param string $externalId
     *
     * @return Connection
     */
    public function setExternalId(string $externalId): Connection
    {
        $this->externalid = $externalId;
        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Connection
     */
    public function setName(string $name): Connection
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set logo URL
     *
     * @param string $logoUrl
     *
     * @return Connection
     */
    public function setLogoUrl(string $logoUrl): Connection
    {
        $this->logoUrl = $logoUrl;
        return $this;
    }

    /**
     * Set link URL
     *
     * @param string $linkUrl
     *
     * @return Connection
     */
    public function setLinkUrl(string $linkUrl): Connection
    {
        $this->linkUrl = $linkUrl;
        return $this;
    }

    /**
     * Set status
     *
     * @param int $status
     *
     * @return Connection
     */
    public function setStatus(int $status): Connection
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Set sync status
     *
     * @param int $syncStatus
     *
     * @return Connection
     */
    public function setSyncStatus(int $syncStatus): Connection
    {
        $this->syncStatus = $syncStatus;
        return $this;
    }
}
