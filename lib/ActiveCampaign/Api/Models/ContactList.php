<?php
declare(strict_types=1);

namespace ActiveCampaign\Api\Models;

class ContactList extends \ActiveCampaign\Api\Models\AbstractModel
{
    /**
     * @var string
     */
    protected $list;

    /**
     * @var string
     */
    protected $contact;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var int
     */
    protected $sourceid;

    /**
     * Set list
     *
     * @param string $list
     *
     * @return ContactList
     */
    public function setList(string $list): ContactList
    {
        $this->list = $list;
        return $this;
    }

    /**
     * Set contact
     *
     * @param string $contact
     *
     * @return ContactList
     */
    public function setContact(string $contact): ContactList
    {
        $this->contact = $contact;
        return $this;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return ContactList
     */
    public function setStatus(string $status): ContactList
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Set source ID
     *
     * @param int $sourceId
     *
     * @return ContactList
     */
    public function setSourceId(int $sourceId): ContactList
    {
        $this->sourceid = $sourceId;
        return $this;
    }
}
