<?php
declare(strict_types=1);

namespace ActiveCampaign\Gateway;

class Response
{
    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    private $response;

    /**
     * @var array
     */
    private $successCodes;

    /**
     * @var array
     */
    private $errorCodes;

    /**
     * @var int|null
     */
    public $status;

    /**
     * @var array
     */
    public $result = [];

    /**
     * @var string
     */
    public $rawResult;

    /**
     * Construct
     *
     * @param \Psr\Http\Message\ResponseInterface|\GuzzleHttp\Exception\ClientException|\Exception $response
     * @param array $successCodes
     * @param array $errorCodes
     *
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    public function __construct(
        \Psr\Http\Message\ResponseInterface|\GuzzleHttp\Exception\ClientException|\Exception $response,
        array $successCodes = [],
        array $errorCodes = []
    ) {
        $this->response = $response;
        $this->successCodes = $successCodes;
        $this->errorCodes = $errorCodes;
        $this->parseResponse();
    }

    /**
     * Parse response
     *
     * @return void
     * @throws \ActiveCampaign\Gateway\ResultException
     */
    private function parseResponse()
    {
        $success = false;

        try {
            if ($this->response instanceof \Psr\Http\Message\ResponseInterface) {
                $this->status = $this->response->getStatusCode();
                $this->rawResult = $this->response->getBody()->getContents();
                $this->result = $this->unserialize($this->rawResult);
            } elseif ($this->response instanceof \GuzzleHttp\Exception\ClientException) {
                $this->status = $this->response->getCode();
                $this->rawResult = $this->response->getMessage();
                $this->result = [$this->rawResult];
            }

            if (in_array($this->status, $this->successCodes)
                || in_array($this->status, $this->errorCodes)
            ) {
                $success = true;
            }
        } catch (\Exception $e) {
            throw new \ActiveCampaign\Gateway\ResultException($e->getMessage());
        }

        if (!$success) {
            throw new \ActiveCampaign\Gateway\ResultException($this->getMessage());
        }
    }

    /**
     * Unserialize the given string
     *
     * @param string|null $string
     *
     * @return array
     */
    private function unserialize(?string $string)
    {
        if ($string === null) {
            throw new \InvalidArgumentException(
                'Unable to unserialize value. Error: Parameter must be a string type, null given.'
            );
        }

        $result = json_decode($string, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Unable to unserialize value. Error: ' . json_last_error_msg());
        }

        if (!is_array($result)) {
            $result = [$result];
        }

        return $result;
    }

    /**
     * Get message
     *
     * @return string
     */
    private function getMessage(): string
    {
        if ($this->response instanceof \Exception) {
            return $this->response->getMessage();
        }

        if (is_array($this->result)) {
            if (isset($this->result['message'])) {
                return $this->result['message'];
            } elseif (isset($this->result['error']['title'])) {
                return $this->result['error']['title'];
            } elseif (isset($this->result['errors']['0']['title'])) {
                return $this->result['errors']['0']['title'];
            }
        }

        $additional = '';

        if ($this->status) {
            $additional = sprintf(' (Status code: %s)', $this->status);
        }

        return sprintf('An unknown error occurred.%s', $additional);
    }
}
