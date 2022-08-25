<?php
declare(strict_types=1);

namespace ActiveCampaign\Api;

class Contacts extends \ActiveCampaign\Gateway\Client
{
    /**
     * Sync
     *
     * @see https://developers.activecampaign.com/reference/sync-a-contacts-data
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @param array $fieldValues
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function sync(
        string $email,
        string $firstName = '',
        string $lastName = '',
        string $phone = '',
        array $fieldValues = []
    ): \ActiveCampaign\Gateway\Response {
        $payload = [
            'contact'   => [
                'email'         => $email,
                'firstName'     => $firstName,
                'lastName'      => $lastName,
                'phone'         => $phone,
                'fieldValues'   => $fieldValues
            ]
        ];

        return $this->request(
            'contact/sync',
            self::METHOD_POST,
            $payload,
            [200, 201]
        );
    }
}
