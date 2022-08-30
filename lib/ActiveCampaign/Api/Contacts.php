<?php
declare(strict_types=1);

namespace ActiveCampaign\Api;

class Contacts extends \ActiveCampaign\Gateway\Client
{
    /**
     * Create
     *
     * @see https://developers.activecampaign.com/reference/create-a-new-contact
     *
     * @param \ActiveCampaign\Api\Models\Contact $contact
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function create(
        \ActiveCampaign\Api\Models\Contact $contact
    ): \ActiveCampaign\Gateway\Response {
        $payload = [
            'contact' => $contact->extractPayload()
        ];

        return $this->request(
            'contacts',
            self::METHOD_POST,
            $payload,
            [200, 201]
        );
    }

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

    /**
     * Get
     *
     * @see https://developers.activecampaign.com/reference/get-contact
     *
     * @param int $contactId
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function get(
        int $contactId
    ): \ActiveCampaign\Gateway\Response {
        return $this->request(
            'contacts/' . $contactId,
            self::METHOD_GET,
            [],
            [200]
        );
    }

    /**
     * Update
     *
     * @see https://developers.activecampaign.com/reference/update-a-contact-new
     *
     * @param int $contactId
     * @param \ActiveCampaign\Api\Models\Contact $contact
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function update(
        int $contactId,
        \ActiveCampaign\Api\Models\Contact $contact
    ): \ActiveCampaign\Gateway\Response {
        $payload = [
            'contact' => $contact->extractPayload()
        ];

        return $this->request(
            'contacts/' . $contactId,
            self::METHOD_PUT,
            $payload,
            [200, 201]
        );
    }

    /**
     * Delete
     *
     * @see https://developers.activecampaign.com/reference/delete-contact
     *
     * @param int $contactId
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function delete(
        int $contactId
    ): \ActiveCampaign\Gateway\Response {
        return $this->request(
            'contacts/' . $contactId,
            self::METHOD_DELETE,
            [],
            [200]
        );
    }
}
