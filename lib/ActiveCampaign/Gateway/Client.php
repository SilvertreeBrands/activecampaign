<?php
declare(strict_types=1);

namespace ActiveCampaign\Gateway;

class Client
{
    public const API_VERSION = '/api/3/';
    public const HEADER_CONTENT_TYPE = 'application/json';
    public const HEADER_ACCEPT = 'application/json; charset=UTF-8';

    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_GET = 'GET';
    public const METHOD_DELETE = 'DELETE';

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * @var \ActiveCampaign\Gateway\Response
     */
    private $response;

    /**
     * Construct
     *
     * @param string $apiKey
     * @param string $apiUrl
     * @param \Psr\Log\LoggerInterface $logger
     * @param bool $debug
     * @param \GuzzleHttp\ClientInterface|null $client
     */
    public function __construct(
        string $apiKey,
        string $apiUrl,
        \Psr\Log\LoggerInterface $logger,
        bool $debug = false,
        \GuzzleHttp\ClientInterface $client = null
    ) {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->logger = $logger;
        $this->debug = $debug;
        $this->client = $client ?: new \GuzzleHttp\Client();
    }

    /**
     * Request
     *
     * @param string $action
     * @param string $method
     * @param array $payload
     * @param array $successCodes
     *
     * @return \ActiveCampaign\Gateway\Response
     */
    protected function request(
        string $action,
        string $method,
        array $payload = [],
        array $successCodes = []
    ) {
        try {
            if (!$this->apiKey) {
                throw new \Exception('The API key is not configured or is empty.');
            }

            if (!$this->apiUrl) {
                throw new \Exception('The API URL is not configured or is empty.');
            }

            $url = $this->buildUrl($action);

            $options = [
                \GuzzleHttp\RequestOptions::HEADERS => [
                    'Content-Type'  => self::HEADER_CONTENT_TYPE,
                    'Accept'        => self::HEADER_ACCEPT,
                    'Api-Token'     => $this->apiKey
                ]
            ];

            if (!empty($payload)) {
                $options[\GuzzleHttp\RequestOptions::JSON] = $payload;
            }

            $this->debug('Prepare request', [
                'METHOD'    => $method,
                'URL'       => $url,
                'HEADERS'   => $options[\GuzzleHttp\RequestOptions::HEADERS],
                'PAYLOAD'   => $options[\GuzzleHttp\RequestOptions::JSON] ?? []
            ]);

            // Perform request
            $response = $this->client->request($method, $url, $options);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $response = $e;
        }

        return new \ActiveCampaign\Gateway\Response($response, $successCodes);
    }

    /**
     * Debug
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    private function debug(string $message, array $context)
    {
        if ($this->logger && $this->debug) {
            $this->logger->debug($message, $context);
        }
    }

    /**
     * Build URL
     *
     * @param string $action
     *
     * @return string
     */
    private function buildUrl(string $action)
    {
        return rtrim($this->apiUrl, '/ ') . self::API_VERSION . $action;
    }
}
