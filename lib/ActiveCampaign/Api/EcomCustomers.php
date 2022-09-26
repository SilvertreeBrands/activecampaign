<?php
declare(strict_types=1);

namespace ActiveCampaign\Api;

class EcomCustomers extends \ActiveCampaign\Gateway\Client
{
    /**
     * Create
     *
     * @see https://developers.activecampaign.com/reference/create-customer
     *
     * @param \ActiveCampaign\Api\Models\EcomCustomer $ecomCustomer
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function create(
        \ActiveCampaign\Api\Models\EcomCustomer $ecomCustomer
    ): \ActiveCampaign\Gateway\Response {
        $payload = [
            'ecomCustomer' => $ecomCustomer->extractPayload()
        ];

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
     * @param \ActiveCampaign\Api\Models\EcomCustomer $ecomCustomer
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function update(
        int $ecomCustomerId,
        \ActiveCampaign\Api\Models\EcomCustomer $ecomCustomer
    ): \ActiveCampaign\Gateway\Response {
        $payload = [
            'ecomCustomer' => $ecomCustomer->extractPayload()
        ];

        return $this->request(
            'ecomCustomers/' . $ecomCustomerId,
            self::METHOD_PUT,
            $payload,
            [200, 201],
            [404]
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
