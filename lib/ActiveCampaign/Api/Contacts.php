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
     * @param \ActiveCampaign\Api\Models\Contact $contact
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function sync(
        \ActiveCampaign\Api\Models\Contact $contact
    ): \ActiveCampaign\Gateway\Response {
        $payload = [
            'contact' => $contact->extractPayload()
        ];

        return $this->request(
            'contact/sync',
            self::METHOD_POST,
            $payload,
            [200, 201]
        );
    }
}
