<?php
declare(strict_types=1);

namespace ActiveCampaign\Api\Ecommerce;

class Customers extends \ActiveCampaign\Gateway\Client
{
    /**
     * Create
     *
     * @see https://developers.activecampaign.com/reference/create-customer
     *
     * @param string $connectionId
     * @param string $externalId
     * @param string $email
     * @param string $acceptsMarketing
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function create(
        string $connectionId,
        string $externalId,
        string $email,
        string $acceptsMarketing = ''
    ): \ActiveCampaign\Gateway\Response {
        $payload = [
            'ecomCustomer'   => [
                'connectionid'  => $connectionId,
                'externalid'    => $externalId,
                'email'         => $email
            ]
        ];

        if ($acceptsMarketing) {
            $payload['ecomCustomer']['acceptsMarketing'] = $acceptsMarketing;
        }

        return $this->request(
            'ecomCustomers',
            self::METHOD_POST,
            $payload,
            [200, 201]
        );
    }

    /**
     * Get
     *
     * @see https://developers.activecampaign.com/reference/get-customer
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
            'ecomCustomers/' . $ecomCustomerId,
            self::METHOD_GET,
            [],
            [200]
        );
    }

    /**
     * Update
     *
     * @see https://developers.activecampaign.com/reference/update-customer
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
        $payload = ['ecomCustomer' => []];

        if ($externalId) {
            $payload['ecomCustomer']['externalid'] = $externalId;
        }

        if ($connectionId) {
            $payload['ecomCustomer']['connectionid'] = $connectionId;
        }

        if ($email) {
            $payload['ecomCustomer']['email'] = $email;
        }

        if ($acceptsMarketing) {
            $payload['ecomCustomer']['acceptsMarketing'] = $acceptsMarketing;
        }

        return $this->request(
            'ecomCustomers/' . $ecomCustomerId,
            self::METHOD_PUT,
            $payload,
            [200, 201]
        );
    }

    /**
     * Delete
     *
     * @see https://developers.activecampaign.com/reference/delete-customer
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
            'ecomCustomers/' . $ecomCustomerId,
            self::METHOD_DELETE,
            [],
            [200]
        );
    }

    /**
     * List
     *
     * @see https://developers.activecampaign.com/reference/list-all-customers
     *
     * @param array $filters
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function list(
        array $filters = []
    ): \ActiveCampaign\Gateway\Response {
        $action = 'ecomCustomers';

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
