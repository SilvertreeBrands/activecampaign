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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var bool
     */
    private $debug;

    /**
     * Construct
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \GuzzleHttp\ClientInterface|null $client
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \GuzzleHttp\ClientInterface $client = null
    ) {
        $this->logger = $logger;
        $this->client = $client ?: new \GuzzleHttp\Client();
    }

    /**
     * Set config
     *
     * @param string $apiKey
     * @param string $apiUrl
     * @param bool $debug
     *
     * @return Client
     */
    public function setConfig(
        string $apiKey,
        string $apiUrl,
        bool $debug = false
    ) {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->debug = $debug;

        return $this;
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
     * @throws ResultException
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
            $rawResponse = $this->client->request($method, $url, $options);
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $rawResponse = $e;
        } catch (\GuzzleHttp\Exception\GuzzleException $ge) {
            $this->logger->critical($ge);
            $rawResponse = $ge;
        }

        $response = new \ActiveCampaign\Gateway\Response($rawResponse, $successCodes);

        $this->debug('Response result', $response->result);

        return $response;
    }

    /**
     * Debug
     *
     * @param string $message
     * @param ?array $context
     *
     * @return void
     */
    private function debug(string $message, array $context = null)
    {
        if ($this->logger && $this->debug) {
            if (is_null($context)) {
                $context = [];
            }

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
