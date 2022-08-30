<?php
declare(strict_types=1);

namespace ActiveCampaign\Api\Models;

class EcomCustomer extends \ActiveCampaign\Api\Models\AbstractModel
{
    /**
     * @var string
     */
    protected $connectionid;

    /**
     * @var string
     */
    protected $externalid;

    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $acceptsMarketing;

    /**
     * Set connection ID
     *
     * @param string $connectionId
     *
     * @return EcomCustomer
     */
    public function setConnectionId(string $connectionId): EcomCustomer
    {
        $this->connectionid = $connectionId;
        return $this;
    }

    /**
     * Set external ID
     *
     * @param string $externalId
     *
     * @return EcomCustomer
     */
    public function setExternalId(string $externalId): EcomCustomer
    {
        $this->externalid = $externalId;
        return $this;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return EcomCustomer
     */
    public function setEmail(string $email): EcomCustomer
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Set accepts marketing
     *
     * @param string $acceptsMarketing
     *
     * @return EcomCustomer
     */
    public function setAcceptsMarketing(string $acceptsMarketing): EcomCustomer
    {
        $this->acceptsMarketing = $acceptsMarketing;
        return $this;
    }
}
