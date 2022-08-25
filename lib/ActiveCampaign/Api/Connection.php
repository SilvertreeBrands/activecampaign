<?php
declare(strict_types=1);

namespace ActiveCampaign\Api;

class Connection extends \ActiveCampaign\Gateway\Client
{
    /**
     * Create
     *
     * @see https://developers.activecampaign.com/reference/create-connection
     *
     * @param \ActiveCampaign\Api\Models\Connection $connection
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function create(
        \ActiveCampaign\Api\Models\Connection $connection
    ): \ActiveCampaign\Gateway\Response {
        $payload = [
            'connection' => $connection->extractPayload()
        ];

        return $this->request(
            'connections',
            self::METHOD_POST,
            $payload,
            [200, 201]
        );
    }

    /**
     * Get
     *
     * @see https://developers.activecampaign.com/reference/get-connection
     *
     * @param int $connectionId
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function get(
        int $connectionId
    ): \ActiveCampaign\Gateway\Response {
        return $this->request(
            'connections/' . $connectionId,
            self::METHOD_GET,
            [],
            [200]
        );
    }

    /**
     * Update
     *
     * @see https://developers.activecampaign.com/reference/update-connection
     *
     * @param int $connectionId
     * @param \ActiveCampaign\Api\Models\Connection $connection
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function update(
        int $connectionId,
        \ActiveCampaign\Api\Models\Connection $connection
    ): \ActiveCampaign\Gateway\Response {
        $payload = [
            'connection' => $connection->extractPayload()
        ];

        return $this->request(
            'connections/' . $connectionId,
            self::METHOD_PUT,
            $payload,
            [200, 201]
        );
    }

    /**
     * Delete
     *
     * @see https://developers.activecampaign.com/reference/delete-connection
     *
     * @param int $connectionId
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function delete(
        int $connectionId
    ): \ActiveCampaign\Gateway\Response {
        return $this->request(
            'connections/' . $connectionId,
            self::METHOD_DELETE,
            [],
            [200]
        );
    }

    /**
     * List
     *
     * @see https://developers.activecampaign.com/reference/list-all-connections
     *
     * @param array $filters
     *
     * @return \ActiveCampaign\Gateway\Response
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function list(
        array $filters = []
    ): \ActiveCampaign\Gateway\Response {
        $action = 'connections';

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
