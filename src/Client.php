<?php
/**
 * Created by PhpStorm.
 * User: yd-yf-2018091401-001
 * Date: 2020/1/2
 * Time: 11:07 AM
 */

namespace Hyperf\TencentAiSdk;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Guzzle\ClientFactory;
use Hyperf\TencentAiSdk\Exception\ClientException;
use Hyperf\TencentAiSdk\Exception\ServerException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use GuzzleHttp\Client as GuzzleClient;

abstract class Client
{
    const API_URL_PATH = 'https://api.ai.qq.com/fcgi-bin';

    /**
     * @var float
     */
    private $connectTimeout = 5;

    /**
     * @var float
     */
    private $recvTimeout = 5;

    /**
     * @var \Hyperf\Guzzle\ClientFactory
     */
    private $clientFactory;

    /**
     * @var StdoutLoggerInterface
     */
    private $logger;

    public function __construct(ClientFactory $clientFactory, LoggerInterface $logger = null)
    {
        $this->clientFactory = $clientFactory;
        $this->logger = $logger ?: new NullLogger();
    }

    public function getClient(): GuzzleClient
    {
        return $this->clientFactory->create([
            'debug' => false,
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'timeout' => ($this->connectTimeout + $this->recvTimeout),
        ]);
    }

    public function request(string $method, string $url, array $options = []): array
    {
        $this->logger->debug(sprintf('Consul Request [%s] %s', strtoupper($method), $url));
        try {
            // Create a HTTP Client by $clientFactory closure.
            $client = $this->getClient();
            if (!$client instanceof ClientInterface) {
                throw new ClientException(sprintf('The client factory should create a %s instance.', ClientInterface::class));
            }
            $response = retry(2, function () use ($client, $method, $options, $url) {
                return $response = $client->request($method, self::API_URL_PATH . $url, ['form_params' => $options]);
            }, 1000);
        } catch (TransferException $e) {
            $message = sprintf('Something went wrong when calling remote-server (%s).', $e->getMessage());
            $this->logger->error($message);
            throw new ServerException($e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() >= 400) {
            $message = sprintf('Something went wrong when calling remote-server (%s - %s).', $response->getStatusCode(), $response->getReasonPhrase());
            $this->logger->error($message);
            $message .= PHP_EOL . (string)$response->getBody();
            if ($response->getStatusCode() >= 500) {
                throw new ServerException($message, $response->getStatusCode());
            }
            throw new ClientException($message, $response->getStatusCode());
        }

        $data = json_decode((string)$response->getBody(), true);
        return $data;
    }
}