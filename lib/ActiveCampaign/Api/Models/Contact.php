<?php
declare(strict_types=1);

namespace ActiveCampaign\Api\Models;

class Contact extends \ActiveCampaign\Api\Models\AbstractModel
{
    /**
     * @var string
     */
    protected $email;

    /**
     * @var string
     */
    protected $firstName;

    /**
     * @var string
     */
    protected $lastName;

    /**
     * @var string
     */
    protected $phone;

    /**
     * @var array
     */
    protected $fieldValues;

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Contact
     */
    public function setEmail(string $email): Contact
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Set first name
     *
     * @param string $firstName
     *
     * @return Contact
     */
    public function setFirstName(string $firstName): Contact
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Set last name
     *
     * @param string $lastName
     *
     * @return Contact
     */
    public function setLastName(string $lastName): Contact
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Contact
     */
    public function setPhone(string $phone): Contact
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * Set field values
     *
     * @param array $fieldValues
     *
     * @return Contact
     */
    public function setFieldValues(array $fieldValues): Contact
    {
        $this->fieldValues = $fieldValues;
        return $this;
    }
}
