<?php
declare(strict_types=1);

namespace ActiveCampaign\Api;

class EcomOrders extends \ActiveCampaign\Gateway\Client
{
    /**
     * Create
     *
     * @see https://developers.activecampaign.com/reference/create-order
     *
     * @param \ActiveCampaign\Api\Models\EcomOrder $ecomOrder
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function create(
        \ActiveCampaign\Api\Models\EcomOrder $ecomOrder
    ): \ActiveCampaign\Gateway\Response {
        $payload = [
            'ecomOrder' => $ecomOrder->extractPayload()
        ];

        return $this->request(
            'ecomOrders',
            self::METHOD_POST,
            $payload,
            [200, 201]
        );
    }

    /**
     * Get
     *
     * @see https://developers.activecampaign.com/reference/get-order
     *
     * @param int $ecomCustomerId
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function get(
        int $ecomCustomerId
    ): \ActiveCampaign\Gateway\Response {
        return $this->request(
            'ecomOrders/' . $ecomCustomerId,
            self::METHOD_GET,
            [],
            [200]
        );
    }

    /**
     * Update
     *
     * @see https://developers.activecampaign.com/reference/update-order
     *
     * @param int $ecomCustomerId
     * @param string $externalId
     * @param string $connectionId
     * @param string $email
     * @param string $acceptsMarketing
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function update(
        int $ecomCustomerId,
        string $externalId = '',
        string $connectionId = '',
        string $email = '',
        string $acceptsMarketing = ''
    ): \ActiveCampaign\Gateway\Response {
        $payload = ['ecomOrder' => []];

        if ($externalId) {
            $payload['ecomOrder']['externalid'] = $externalId;
        }

        if ($connectionId) {
            $payload['ecomOrder']['connectionid'] = $connectionId;
        }

        if ($email) {
            $payload['ecomOrder']['email'] = $email;
        }

        if ($acceptsMarketing) {
            $payload['ecomOrder']['acceptsMarketing'] = $acceptsMarketing;
        }

        return $this->request(
            'ecomOrders/' . $ecomCustomerId,
            self::METHOD_PUT,
            $payload,
            [200, 201]
        );
    }

    /**
     * Delete
     *
     * @see https://developers.activecampaign.com/reference/delete-order
     *
     * @param int $ecomCustomerId
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function delete(
        int $ecomCustomerId
    ): \ActiveCampaign\Gateway\Response {
        return $this->request(
            'ecomOrders/' . $ecomCustomerId,
            self::METHOD_DELETE,
            [],
            [200]
        );
    }

    /**
     * List
     *
     * @see https://developers.activecampaign.com/reference/list-all-orders
     *
     * @param array $filters
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function list(
        array $filters = []
    ): \ActiveCampaign\Gateway\Response {
        $action = 'ecomOrders';

        if (!empty($filters)) {
            $action .= '?' . str_replace(' ', '+', urldecode(http_build_query($filters)));
        }

        return $this->request(
            $action,
            self::METHOD_GET,
            [],
            [200]
        );
    }
}
