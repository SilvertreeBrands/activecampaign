<?php
declare(strict_types=1);

namespace ActiveCampaign\Gateway;

class Client
{
    public const API_VERSION = '/api/3/';
    public const HTTP_VERSION = '1.1';
    public const CONTENT_TYPE = 'application/json';

    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var string
     */
    private $apiKey = '';

    /**
     * @var string
     */
    private $apiUrl = '';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

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
     * @param array $expectedResponseStatuses
     *
     * @return void
     */
    public function request(
        string $action,
        string $method,
        array $payload = [],
        array $expectedResponseStatuses = []
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
                    'Content-Type'  => self::CONTENT_TYPE,
                    'Api-Token'     => $this->apiKey
                ]
            ];

            $this->debug('Request', [
                'METHOD'    => $method,
                'URL'       => $url,
                'OPTIONS'   => $options
            ]);

            //$result = $this->client->request($method, $url, $options);

        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
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
