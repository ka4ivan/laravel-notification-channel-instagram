<?php

namespace NotificationChannels\Instagram;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use NotificationChannels\Instagram\Exceptions\CouldNotSendNotification;

class Instagram
{
    const API_HOST = 'https://graph.instagram.com';

    /**
     * @var HttpClient HTTP Client
     */
    protected $http;

    /**
     * Instagram API version (e.g., '22.0')
     *
     * @var string
     */
    protected $apiVersion;

    /**
     * Access token for authenticating with the Instagram API.
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Username used to authenticate with the Instagram API.
     *
     * @var string
     */
    protected $username;

    /**
     * Instagram profile ID associated with the authenticated user.
     *
     * curl -X GET "https://graph.instagram.com/me?fields=id,username&access_token=ACCESS_TOKEN"
     *
     * @var string
     */
    protected $profileId;

    public function __construct(array $config = [], HttpClient $httpClient = null)
    {
        $this->http = $httpClient;
        $this->apiVersion = Arr::get($config, 'apiVersion');
        $this->accessToken = Arr::get($config, 'accessToken');
        $this->username = Arr::get($config, 'username');
        $this->profileId = Arr::get($config, 'profileId');
    }

    /**
     * Get HttpClient.
     */
    protected function httpClient(): HttpClient
    {
        return $this->http ?? new HttpClient();
    }

    /**
     * Set Default Graph API Version.
     *
     * @param string|null $apiVersion
     * @return Instagram
     */
    public function setApiVersion(string $apiVersion = null): self
    {
        $this->apiVersion = $apiVersion;

        return $this;
    }

    /**
     * Set the access token used for authenticating API requests.
     *
     * @param string|null $accessToken Instagram access token.
     * @return $this
     */
    public function setAccessToken(string $accessToken = null): self
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Set the Instagram username associated with the account.
     *
     * @param string|null $username Instagram username.
     * @return $this
     */
    public function setUsername(string $username = null): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Set the Instagram profile ID for API requests.
     *
     * @param string|null $profileId Instagram profile ID.
     * @return $this
     */
    public function setProfileId(string $profileId = null): self
    {
        $this->profileId = $profileId;

        return $this;
    }

    /**
     * Send text message.
     *
     * @throws GuzzleException
     * @throws CouldNotSendNotification
     */
    public function send(array $params): ResponseInterface
    {
        return $this->post("{$this->profileId}/messages", $params);
    }

    /**
     * @throws GuzzleException
     * @throws CouldNotSendNotification
     */
    public function get(string $endpoint, array $params = []): ResponseInterface
    {
        return $this->api($endpoint, ['query' => $params]);
    }

    /**
     * @throws GuzzleException
     * @throws CouldNotSendNotification
     */
    public function post(string $endpoint, array $params = []): ResponseInterface
    {
        return $this->api($endpoint, ['json' => $params], 'POST');
    }

    /**
     * Send an API request and return response.
     *
     * @param string $endpoint
     * @param array $options
     * @param string $method
     *
     * @return mixed|ResponseInterface
     * @throws CouldNotSendNotification
     */
    protected function api(string $endpoint, array $options, string $method = 'GET')
    {
        if (
            empty($this->token) ||
            empty($this->apiVersion) ||
            empty($this->accessToken) ||
            empty($this->username) ||
            empty($this->profileId)
        ) {
            throw CouldNotSendNotification::instagramPageTokenNotProvided('You must provide your Instagram tokens to make any API requests.');
        }

        $url = self::API_HOST . "/v{$this->apiVersion}/{$endpoint}";

        $options['headers']['Authorization'] = "Bearer {$this->accessToken}";
        $options['headers']['Accept'] = 'application/json';

        try {
            return $this->httpClient()->request($method, $url, $options);
        } catch (ClientException $exception) {
            throw CouldNotSendNotification::instagramRespondedWithAnError($exception);
        } catch (\Exception $exception) {
            throw CouldNotSendNotification::couldNotCommunicateWithInstagram($exception);
        }
    }
}
